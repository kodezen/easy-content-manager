<?php

namespace EasyContentManager\Features;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class RegisterTaxonomies {




	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'register_custom_taxonomies' ], 9 );
		// Runs after RegisterPostTypes has added ECM's own top-level/submenu
		// pages (admin_menu priority 10) and its own-CPT taxonomy submenus
		// (priority 20), so a parent page like Academy's "academy-courses"
		// already exists by the time this tries to attach to it.
		add_action( 'admin_menu', [ $self, 'register_admin_submenus' ], 20 );

		add_action('init', function () {
			if ( get_option( 'ecm_flush_taxonomy_rules', false ) ) {
				flush_rewrite_rules();
				delete_option( 'ecm_flush_taxonomy_rules' );
			}
		});
	}

	/**
	 * Places a taxonomy's admin screen under an explicit "Admin Menu Parent".
	 *
	 * WP core only auto-adds a taxonomy submenu when the taxonomy's post type
	 * itself gets an auto-generated top-level menu (see wp-admin/menu.php);
	 * unlike post types, a string `show_in_menu` on a taxonomy is not resolved
	 * into a submenu by core. `RegisterPostTypes::register_taxonomy_submenus()`
	 * covers taxonomies attached to ECM's own post types, but taxonomies
	 * attached to a foreign plugin's post type (e.g. Academy's
	 * `academy_courses`) are never in its `admin_menu_lists`, so their menu
	 * placement was silently dropped. This handles that case directly from
	 * the taxonomy's own registered `show_in_menu` string.
	 *
	 * The saved parent (e.g. `academy-courses`) is registered exactly as
	 * configured, even though it's itself a submenu page rather than a real
	 * WP top-level — core's own sidebar can't render a submenu-of-a-submenu
	 * (`wp-admin/menu-header.php` only draws `$submenu[$item[2]]` for
	 * `$item`s that are themselves top-level), so nothing here ever shows up
	 * via core's native rendering regardless. Consuming plugins that want
	 * their own menu to reflect this (e.g. Academy reading
	 * `$submenu['academy-courses']` to nest it into its "Courses" flyout,
	 * see `Academy\Classes\ScriptsBase::get_native_submenu_items()`) can read
	 * it from `$submenu[$parent_slug]` directly — that's the whole point of
	 * keeping the literal, unresolved slug here instead of flattening it.
	 */
	public function register_admin_submenus() {
		foreach ( get_taxonomies( [ 'show_ui' => true ], 'objects' ) as $taxonomy ) {
			if ( ! is_string( $taxonomy->show_in_menu ) || '' === $taxonomy->show_in_menu ) {
				continue;
			}

			$object_types = (array) $taxonomy->object_type;
			$post_type    = reset( $object_types );
			$parent_slug  = $this->get_admin_query_param( $taxonomy->show_in_menu );

			$menu_name = ! empty( $taxonomy->labels->menu_name ) ? $taxonomy->labels->menu_name : $taxonomy->labels->name;

			add_submenu_page(
				$parent_slug,
				$taxonomy->labels->name,
				$menu_name,
				$taxonomy->cap->manage_terms,
				'edit-tags.php?taxonomy=' . $taxonomy->name . ( $post_type ? '&post_type=' . $post_type : '' )
			);
		}
	}

	private function get_admin_query_param( $url ) {
		$parts = wp_parse_url( $url );

		if ( empty( $parts['query'] ) ) {
			return $url;
		}

		parse_str( $parts['query'], $query );

		return $query['page'] ?? $url;
	}

	public function register_custom_taxonomies() {
		$taxonomies = Helper::get_all_custom_taxonomies();

		foreach ( $taxonomies as $taxonomy_post ) {
			$data = maybe_unserialize( $taxonomy_post->post_content );

			if ( ! isset( $data['taxonomy_slug'] ) || empty( $data['object_type'] ) ) {
				continue;
			}

			$slug = sanitize_key( $data['taxonomy_slug'] );
			$object_types = array_map( function ( $item ) {
				return $item['post_type'];
			}, (array) $data['object_type'] );

			$args = $this->prepare_taxonomy_args( $data );

			register_taxonomy( $slug, array_merge( [ 'academy_courses', 'storeengine_product' ], $object_types ), $args );
		}

		$this->register_local_taxonomies();
	}

	public function register_local_taxonomies() {
		$taxonomies = ecm_get_json_from_files( ecm_get_plugins_taxonomy_path() );
		$theme_taxonomies = [];

		if ( Helper::has_theme_support() ) {
			$theme_taxonomies = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/taxonomies' ] );
		}

		$taxonomies = array_merge( $taxonomies, $theme_taxonomies );

		foreach ( $taxonomies as $taxonomy ) {
			$data = $taxonomy['post_content'];

			if ( ! isset( $data['taxonomy_slug'] ) || empty( $data['object_type'] ) ) {
				continue;
			}

			$slug = $data['taxonomy_slug'];
			$object_types = array_map(function ( $item ) {
				return $item['slug'] ?? $item['title'] ?? $item;
			}, (array) $data['object_type']);

			$args = $this->prepare_taxonomy_args( $data );

			register_taxonomy( $slug, $object_types, $args );
		}
	}

	protected function prepare_taxonomy_args( $data ) {
		$args = [];

		$args['labels'] = $data['labels'] ?? [];
		$args['description'] = $data['description'] ?? '';
		$args['public'] = (bool) ( $data['public'] ?? true );
		$args['publicly_queryable'] = (bool) ( $data['publicly_queryable'] ?? $args['public'] );
		$args['show_ui'] = (bool) ( $data['show_ui'] ?? $args['public'] );

		$show_in_menu = (bool) ( $data['show_in_menu'] ?? $args['show_ui'] );
		$args['show_in_menu'] = $show_in_menu;

		// A taxonomy-level "Admin Menu Parent" (e.g. attaching to a foreign
		// plugin's CPT menu, like Academy's courses menu) is picked up by
		// register_admin_submenus() below — WP core never auto-places a
		// taxonomy submenu the way it does for post types.
		if ( $show_in_menu && ! empty( $data['admin_menu_parent'] ) ) {
			$args['show_in_menu'] = (string) $data['admin_menu_parent'];
		}
		$args['show_in_nav_menus'] = $data['show_in_nav_menus'] ?? $args['public'];
		$args['show_tagcloud'] = $data['show_tagcloud'] ?? $args['show_ui'];
		$args['show_in_quick_edit'] = $data['show_in_quick_edit'] ?? $args['show_ui'];
		$args['show_admin_column'] = $data['show_admin_column'] ?? false;
		$args['meta_box_cb'] = $data['meta_box_cb'] ?? null;
		$args['meta_box_sanitize_cb'] = $data['meta_box_sanitize_cb'] ?? null;
		$args['hierarchical'] = (bool) ( $data['hierarchical'] ?? false );
		$args['update_count_callback'] = $data['update_count_callback'] ?? null;
		$args['sort'] = $data['sort'] ?? false;
		$args['args'] = $data['args'] ?? [];

		if ( ! empty( $data['capabilities_enabled'] ) && ! empty( $data['capabilities'] ) ) {
			// Only the four keys register_taxonomy() understands, each a sanitized
			// capability slug. The raw array arrives from stored settings and was
			// previously passed straight through.
			$capabilities = [];
			foreach ( [ 'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms' ] as $key ) {
				if ( ! empty( $data['capabilities'][ $key ] ) && is_string( $data['capabilities'][ $key ] ) ) {
					$capabilities[ $key ] = sanitize_key( $data['capabilities'][ $key ] );
				}
			}

			if ( ! empty( $capabilities ) ) {
				$args['capabilities'] = $capabilities;
			}
		}

		if ( ! empty( $data['show_in_rest'] ) ) {
			$args['show_in_rest'] = true;
			$args['rest_base'] = $data['rest_base'] ?? $data['taxonomy_slug'];
			$args['rest_namespace'] = $data['rest_namespace'] ?? 'wp/v2';
			$args['rest_controller_class'] = $data['rest_controller_class'] ?? 'WP_REST_Terms_Controller';
		}

		$args['query_var'] = $data['query_var'] ?? $data['taxonomy_slug'];
		if ( 'none' === $args['query_var'] ) {
			$args['query_var'] = false;
		} elseif ( 'custom' === $args['query_var'] && ! empty( $data['query_var_name'] ) ) {
			$args['query_var'] = $data['query_var_name'];
		}

		$args['rewrite'] = $data['rewrite'] ?? true;
		if ( is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = array_merge([
				'slug' => $data['taxonomy_slug'],
				'with_front' => true,
				'hierarchical' => false,
			], $args['rewrite']);
		}

		return apply_filters( 'easy_content_manager/taxonomy/registration_args', $args, $data );
	}
}
