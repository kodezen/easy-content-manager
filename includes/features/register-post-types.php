<?php

namespace EasyContentManager\Features;

use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class RegisterPostTypes {




	public $admin_menu_lists = [];

	
	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'register_custom_post_type' ] );
		add_action( 'init', [ $self, 'prepare_settings_menus' ] );
		add_action('init', function () use ( $self ) {
			if ( get_option( 'ecm_flush_rewrite_rules', false ) ) {
				flush_rewrite_rules();
				delete_option( 'ecm_flush_rewrite_rules' );
			}
		});
		add_action( 'admin_menu', array( $self, 'register_admin_menu' ) );
		add_action( 'admin_menu', array( $self, 'register_taxonomy_submenus' ), 20 );
		add_action( 'admin_head', array( $self, 'remove_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', [ $self, 'add_admin_menu_classes' ] );
	}

	public function register_taxonomy_submenus() {
		if ( empty( $this->admin_menu_lists ) ) {
			return;
		}

		foreach ( $this->admin_menu_lists as $menu_slug => $menu_item ) {
			if ( ! post_type_exists( $menu_slug ) ) {
				continue;
			}

			$integrations_slug = Helper::get_ecm_slug_integrations();
			
			if ( ! empty( $menu_item['admin_menu_parent'] ) ) {
				$parent_slug = $this->get_admin_query_param( $menu_item['admin_menu_parent'] );
				
				$is_integration = in_array( $parent_slug, $integrations_slug, true );
				
				if ( $is_integration ) {
					$parent_slug = $parent_slug . '-' . $menu_slug;
				}
			} else {
				$parent_slug = 'ecm-app-' . $menu_slug;
			}

			$post_type_object = get_post_type_object( $menu_slug );
			$ui_type = $menu_item['ui_type'] ?? 'classic';
			
			if ( $post_type_object && 'modern' !== $ui_type ) {
				add_submenu_page(
					$parent_slug,
					$post_type_object->labels->all_items ?? $post_type_object->labels->name,
					$post_type_object->labels->all_items ?? $post_type_object->labels->name,
					$post_type_object->cap->edit_posts,
					'edit.php?post_type=' . $menu_slug
				);
			}

			$taxonomies = get_object_taxonomies( $menu_slug, 'objects' );

			foreach ( $taxonomies as $taxonomy ) {
				if ( ! $taxonomy->show_ui || ! $taxonomy->show_in_menu ) {
					continue;
				}

				// A taxonomy with its own explicit admin menu parent is
				// registered by RegisterTaxonomies::register_admin_submenus()
				// instead, which respects that override rather than this
				// post type's own menu.
				if ( is_string( $taxonomy->show_in_menu ) ) {
					continue;
				}

				add_submenu_page(
					$parent_slug,
					$taxonomy->labels->name,
					$taxonomy->labels->menu_name ?? $taxonomy->labels->name,
					$taxonomy->cap->manage_terms,
					'edit-tags.php?taxonomy=' . $taxonomy->name . '&post_type=' . $menu_slug
				);
			}
		}
	}

	public function prepare_settings_menus() {
		$settings_posts = get_posts([
			'post_type'      => Helper::ECM_SETTINGS_POST_TYPE_NAME,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		]);

		$settings_posts = array_map(function ( $post ) {
			return maybe_unserialize( $post->post_content );
		}, $settings_posts);

		if ( empty( $settings_posts ) ) {
			return;
		}

		$local_settings = ecm_get_json_from_files( ecm_get_plugins_settings_path() );
		if ( Helper::has_theme_support() ) {
			$theme_settings = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/settings' ] );
		}
		$settings_posts = array_merge( $settings_posts, $local_settings, $theme_settings ?? [] );

		foreach ( $settings_posts as $post ) {
			if ( empty( $parent ) ) {
				$parent = admin_url( 'admin.php?page=ecm-app' );
			}

			$this->admin_menu_lists[ $post['slug'] ] = [
				'labels'            => [ 'menu_name' => esc_html( $post['label'] ) ],
				'menu_icon'         => $post['icon'],
				'menu_position'     => $post['menu_position'],
				'admin_menu_parent' => $post['admin_menu_parent'] ?? null,
			];
		}
	}

	public function register_custom_post_type() {
		$cached_post_types = get_transient( 'ecm_cached_prepared_post_types' );

		if ( false === $cached_post_types || empty( $cached_post_types ) ) {
			$raw_post_types     = Helper::get_all_custom_post_types();

			$cached_post_types  = [];

			if ( ! empty( $raw_post_types ) ) {
				$cached_post_types = self::prepare_post_type_for_register( $raw_post_types );
			}

			set_transient( 'ecm_cached_prepared_post_types', $cached_post_types );
		}//end if

		$local_post_types = ecm_get_json_from_files( ecm_get_plugins_post_type_path() );

		if ( Helper::has_theme_support() ) {
			$theme_post_types = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/post-types' ] );
		}

		$all_post_types = array_merge( $cached_post_types, self::prepare_post_type_for_register( array_merge( $local_post_types, $theme_post_types ?? [] ), true ) );

		// Before the early return below: deleting the last renamed post type is
		// precisely the case where the mirrored capabilities must be cleaned off
		// the roles, and it is also the case where there is nothing left to
		// register.
		$this->sync_renamed_capabilities( $all_post_types );

		if ( empty( $all_post_types ) ) {
			return;
		}

		foreach ( $all_post_types as $post_type ) {
			$post_type_key  = $post_type['post_type_key'];
			$post_type_args = $post_type['post_type_args'];

			if ( isset( $post_type['ui_type'] ) && 'modern' === $post_type['ui_type'] ) {
				$post_type_args['show_in_menu']      = false;
				$post_type_args['show_in_admin_bar'] = false;

				if ( ! isset( $this->admin_menu_lists[ $post_type_key ] ) ) {
					$this->admin_menu_lists[ $post_type_key ] = array_merge(
						$post_type_args,
						[ 
							'admin_menu_parent' => $post_type['admin_menu_parent'],
							'ui_type' => $post_type['ui_type']
						]
					);
				}
			}

			if ( ! post_type_exists( $post_type_key ) ) {
				register_post_type( $post_type_key, $post_type_args );
			}
		}//end foreach
	}

	/**
	 * Mirror the capabilities of every renamed post type onto the site's roles.
	 *
	 * Derived from the prepared registration args rather than collected while
	 * building them, so a warm `ecm_cached_prepared_post_types` transient yields
	 * the same set as a cold one. {@see Capabilities::sync_post_type_caps()}
	 * hashes the result and no-ops unless it actually changed.
	 *
	 * @param array<int,array{post_type_args:array}> $all_post_types Prepared post types.
	 */
	private function sync_renamed_capabilities( array $all_post_types ): void {
		$bases = [];

		foreach ( $all_post_types as $post_type ) {
			$capability_type = $post_type['post_type_args']['capability_type'] ?? null;

			if ( empty( $capability_type ) ) {
				continue; // Defaults to `post` — core capabilities, nothing to mirror.
			}

			$bases[] = [
				'singular' => is_array( $capability_type ) ? ( $capability_type[0] ?? '' ) : (string) $capability_type,
				'plural'   => is_array( $capability_type ) ? ( $capability_type[1] ?? '' ) : '',
			];
		}

		Capabilities::sync_post_type_caps( $bases );
	}

	public static function prepare_post_type_for_register( $post_types, bool $is_local = false ) {
		$self = new self();
		$modifed_post_type = [];
		foreach ( $post_types as $post_type ) {
			$post_type_key  = $is_local ? $post_type['post_content']['post_slug'] : $post_type->post_name;
			$post_type_data = $is_local ? $post_type['post_content'] : maybe_unserialize( $post_type->post_content );

			if ( isset( $post_type_data['custom_fields'] ) ) {
				unset( $post_type_data['custom_fields'] );
			}

			$post_type_args = $self->get_post_type_args( $post_type_data );

			$modifed_post_type[] = [
				'post_type_key'      => $post_type_key,
				'post_type_args'     => $post_type_args,
				'ui_type'            => $post_type_data['ui_type'] ?? null,
				'admin_menu_parent'  => $post_type_data['admin_menu_parent'] ?? null,
			];
		}//end foreach

		return $modifed_post_type;
	}

	public function get_post_type_args( $post, $escape_labels = true ) {
		$args = [];

		$labels = $post['labels'] ?? [];
		$labels = array_map( 'strval', $labels );
		if ( $escape_labels ) {
			$labels = array_map( 'esc_html', $labels );
		}
		if ( ! empty( $labels ) ) {
			$args['labels'] = $labels;
		}

		if ( ! empty( $post['description'] ) ) {
			$args['description'] = (string) $post['description'];
		}

		$args['public'] = ! empty( $post['public'] );

		if ( ! empty( $post['hierarchical'] ) ) {
			$args['hierarchical'] = true;
		}

		$exclude_from_search = (bool) $post['exclude_from_search'];
		if ( $exclude_from_search === $args['public'] ) {
			$args['exclude_from_search'] = $exclude_from_search;
		}

		$publicly_queryable = (bool) $post['publicly_queryable'];
		if ( $publicly_queryable !== $args['public'] ) {
			$args['publicly_queryable'] = $publicly_queryable;
		}

		$show_ui = (bool) $post['show_ui'];
		if ( $show_ui !== $args['public'] ) {
			$args['show_ui'] = $show_ui;
		}

		$show_in_menu = (bool) $post['show_in_menu'];
		if ( $show_in_menu !== $show_ui ) {
			$args['show_in_menu'] = $show_in_menu;
		}

		if ( $show_in_menu && ! empty( $post['admin_menu_parent'] ) ) {
			$args['show_in_menu'] = (string) $post['admin_menu_parent'];
		}

		$show_in_nav_menus = (bool) $post['show_in_nav_menus'];
		if ( $show_in_nav_menus !== $args['public'] ) {
			$args['show_in_nav_menus'] = $show_in_nav_menus;
		}

		$show_in_admin_bar = (bool) $post['show_in_admin_bar'];
		if ( $show_in_admin_bar !== $show_in_menu ) {
			$args['show_in_admin_bar'] = $show_in_admin_bar;
		}

		$show_in_rest = (bool) $post['show_in_rest'];
		$args['show_in_rest'] = $show_in_rest;

		$rest_base = (string) $post['rest_base'];
		if ( ! empty( $rest_base ) && $rest_base !== $post['post_type'] ) {
			$args['rest_base'] = $rest_base;
		}

		$rest_namespace = (string) $post['rest_namespace'];
		if ( ! empty( $rest_namespace ) && 'wp/v2' !== $rest_namespace ) {
			$args['rest_namespace'] = $post['rest_namespace'];
		}

		$rest_controller_class = (string) $post['rest_controller_class'];
		if ( ! empty( $rest_controller_class ) && 'WP_REST_Posts_Controller' !== $rest_controller_class ) {
			$args['rest_controller_class'] = $rest_controller_class;
		}

		$menu_position = (int) $post['menu_position'];
		if ( $menu_position ) {
			$args['menu_position'] = $menu_position;
		}

		$args['menu_icon'] = 'dashicons-admin-post';

		if ( ! empty( $post['menu_icon'] ) ) {
			if ( is_string( $post['menu_icon'] ) ) {
				$args['menu_icon'] = $post['menu_icon'];
			}
			if ( is_array( $post['menu_icon'] ) ) {
				if ( 'media_library' === $post['menu_icon']['type'] ) {
					$args['menu_icon'] = wp_get_attachment_image_url( $post['menu_icon']['value'] );
				} else {
					$args['menu_icon'] = $post['menu_icon']['value'];
				}
			}
		}

		$rename_capabilities = (bool) $post['rename_capabilities'];
		if ( $rename_capabilities ) {
			$singular_capability_name = (string) $post['singular_capability_name'];
			$plural_capability_name = (string) $post['plural_capability_name'];
			$capability_type = 'post';

			if ( ! empty( $singular_capability_name ) && ! empty( $plural_capability_name ) ) {
				$capability_type = [ $singular_capability_name, $plural_capability_name ];
			} elseif ( ! empty( $singular_capability_name ) ) {
				$capability_type = $singular_capability_name;
			}

			if ( 'post' !== $capability_type && [ 'post', 'posts' ] !== $capability_type ) {
				$args['capability_type'] = $capability_type;
				$args['map_meta_cap'] = true;
			}
		}

		$supports = is_array( $post['supports'] ) ? $post['supports'] : [];
		$supports = array_unique( array_filter( array_map( 'strval', $supports ) ) );

		if ( empty( $supports ) ) {
			$args['supports'] = false;
		} else {
			$args['supports'] = $supports;
		}

		$taxonomies = $post['taxonomies'];
		if ( ! is_array( $taxonomies ) ) {
			$taxonomies = (array) $taxonomies;
		}

		$taxonomies = array_filter( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$args['taxonomies'] = $taxonomies;
		}

		$has_archive = (bool) $post['has_archive'];
		if ( $has_archive ) {
			$has_archive_slug = (string) $post['has_archive_slug'];

			if ( ! empty( $has_archive_slug ) ) {
				$args['has_archive'] = $has_archive_slug;
			} else {
				$args['has_archive'] = true;
			}
		}

		$rewrite = (array) $post['rewrite'];
		$rewrite_enabled = true;
		$rewrite_args = [];

		$rewrite['permalink_rewrite'] = isset( $rewrite['permalink_rewrite'] ) ? (string) $rewrite['permalink_rewrite'] : 'post_type_key';

		if ( 'no_permalink' === $rewrite['permalink_rewrite'] ) {
			$rewrite_enabled = false;
		}

		if ( 'custom_permalink' === $rewrite['permalink_rewrite'] && ! empty( $rewrite['slug'] ) && $rewrite['slug'] !== $post['post_type'] ) {
			$rewrite_args['slug'] = (string) $rewrite['slug'];
		}

		if ( isset( $rewrite['with_front'] ) && ! $rewrite['with_front'] && $rewrite_enabled ) {
			$rewrite_args['with_front'] = false;
		}

		if ( isset( $rewrite['feeds'] ) && (bool) $rewrite['feeds'] !== $has_archive && $rewrite_enabled ) {
			$rewrite_args['feeds'] = (bool) $rewrite['feeds'];
		}

		if ( isset( $rewrite['pages'] ) && ! $rewrite['pages'] && $rewrite_enabled ) {
			$rewrite_args['pages'] = false;
		}

		if ( ! empty( $rewrite_args ) ) {
			$args['rewrite'] = $rewrite_args;
		} elseif ( ! $rewrite_enabled ) {
			$args['rewrite'] = false;
		}

		$query_var = (string) $post['query_var'];
		if ( 'custom_query_var' === $query_var ) {
			$query_var_name = (string) $post['query_var_name'];

			if ( ! empty( $query_var_name ) && $query_var_name !== $post['post_type'] ) {
				$args['query_var'] = $query_var_name;
			}
		} elseif ( 'none' === $query_var ) {
			$args['query_var'] = false;
		}

		$can_export = (bool) $post['can_export'];
		if ( ! $can_export ) {
			$args['can_export'] = false;
		}

		$args['delete_with_user'] = (bool) $post['delete_with_user'];

		return apply_filters( 'easy_content_manager/post_type/registration_args', $args, $post );
	}

	/**
	 * The capability guarding an `ecm-app-{slug}` screen.
	 *
	 * These screens replace the post type's native menu (a "modern" UI post type
	 * is registered with `show_in_menu => false`), so they must require what the
	 * native menu would have required — the post type's own `edit_posts`
	 * capability — not `manage_options`. Hardcoding `manage_options` here left
	 * editors and authors with no route to their own content at all.
	 *
	 * Entries in `admin_menu_lists` that are not post types (settings screens
	 * from {@see prepare_settings_menus}) fall back to the plugin's settings
	 * capability.
	 */
	private function menu_capability( string $menu_slug ): string {
		if ( post_type_exists( $menu_slug ) ) {
			return Helper::get_post_type_menu_capability( $menu_slug );
		}

		return Capabilities::MANAGE_SETTINGS;
	}

	public function register_admin_menu() {
		if ( empty( $this->admin_menu_lists ) ) {
			return;
		}

		// Step 1: Register all main menus first
		foreach ( $this->admin_menu_lists as $menu_slug => $menu_item ) {
			if ( ! empty( $menu_item['admin_menu_parent'] ) ) {
				continue; // Skip submenus for now
			}

			$full_slug   = 'ecm-app-' . $menu_slug;
			$menu_title  = $menu_item['labels']['menu_name'];
			$capability  = $this->menu_capability( $menu_slug );
			$callback    = [ $this, 'load_main_template' ];
			$icon        = $menu_item['menu_icon'] ?? 'dashicons-admin-generic';
			$position    = $menu_item['menu_position'] ?? 30;

			$hook_suffix = add_menu_page(
				$menu_title,
				$menu_title,
				$capability,
				$full_slug,
				$callback,
				$icon,
				$position
			);

			do_action( 'ecm_app_loaded', $hook_suffix, $menu_slug, $menu_item );
		}//end foreach

		// Step 2: Register submenus
		foreach ( $this->admin_menu_lists as $menu_slug => $menu_item ) {
			if ( empty( $menu_item['admin_menu_parent'] ) ) {
				continue; // Already handled main menus
			}
			$parent_slug = $this->get_admin_query_param( $menu_item['admin_menu_parent'] );

			$integrations_slug = Helper::get_ecm_slug_integrations();
			$sub_menu_slug   = in_array( $parent_slug, $integrations_slug, true ) ? $parent_slug . '-' . $menu_slug : 'ecm-app-' . $menu_slug;

			$menu_title  = $menu_item['labels']['menu_name'];
			$capability  = $this->menu_capability( $menu_slug );
			$callback    = [ $this, 'load_main_template' ];
			$hook_suffix = add_submenu_page(
				$parent_slug,
				$menu_title,
				$menu_title,
				$capability,
				$sub_menu_slug,
				$callback
			);
			do_action( 'ecm_app_loaded', $hook_suffix, $menu_slug, $menu_item );
		}//end foreach
	}

	public function add_admin_menu_classes() {
		if ( empty( $this->admin_menu_lists ) ) {
			return;
		}

		$js = '';
		foreach ( $this->admin_menu_lists as $slug => $menu_item ) {
			if ( empty( $menu_item['class_name'] ) ) {
				continue;
			}

			$class_name = esc_js( $menu_item['class_name'] );
			$menu_slug  = esc_js( 'ecm-app-' . $slug );

			$js .= "
				var el = document.querySelector('#adminmenu a[href=\"admin.php?page={$menu_slug}\"]');
				if (el && el.parentElement) {
					el.parentElement.classList.add('{$class_name}');
				}
			";
		}

		if ( '' !== $js ) {
			wp_add_inline_script( 'common', "document.addEventListener('DOMContentLoaded', function() {{$js}});" );
		}
	}

	public function load_main_template() {
		$slug = Helper::get_ecm_app_slug();
		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div id="' . $slug . '-wrap" class="' . $slug . '-wrap">Loading...</div>';
	}

	public function remove_admin_notices() {
		$screen = get_current_screen();
		if ( count( $this->admin_menu_lists ) ) {
			foreach ( $this->admin_menu_lists as $menu_slug => $menu_item ) {
				if ( $screen && ( 'toplevel_page_ecm-app-' . $menu_slug === $screen->id ) ) {
					remove_all_actions( 'admin_notices' );
					remove_all_actions( 'all_admin_notices' );
				}
			}
		}
	}

	public function get_admin_query_param( $url ) {
		$parts = wp_parse_url( $url );

		if ( empty( $parts['query'] ) ) {
			return $url;
		}

		parse_str( $parts['query'], $query );

		if ( isset( $query['page'] ) ) {
			return $query['page'];
		}

		if ( isset( $query['post_type'] ) ) {
			return $url;
		}

		return $url;
	}
}
