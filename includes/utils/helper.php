<?php

namespace EasyContentManager\Utils;

use EasyContentManager\Admin\SeLicense;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Traits\Addons;
use EasyContentManager\Utils\Traits\FieldGroups;
use EasyContentManager\Utils\Traits\Fields;
use EasyContentManager\Utils\Traits\PostTypes;
use EasyContentManager\Utils\Traits\Presets;
use EasyContentManager\Utils\Traits\Settings;
use EasyContentManager\Utils\Traits\SVGS;
use EasyContentManager\Utils\Traits\Taxonomy;
use EasyContentManager\Utils\Traits\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {


	use FieldGroups;
	use Fields;
	use PostTypes;
	use Presets;
	use Settings;
	use Taxonomy;
	use Addons;
	use SVGS;
    use Theme;

	public const ECM_POST_TYPE_NAME = 'ecm_post_types';
	public const ECM_FIELD_GROUP_POST_TYPE_NAME = 'ecm_field_group';
	public const ECM_TAXONOMY_POST_TYPE_NAME = 'ecm_taxonomy';
	public const ECM_SETTINGS_POST_TYPE_NAME = 'ecm_settings';

	public static function get_time() {
		return time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	/**
	 * The requesting client's IP, sanitized, or '' when unavailable.
	 *
	 * @return string
	 */
	public static function get_client_ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	/**
	 * Per-IP request counter shared by every unauthenticated/public write endpoint
	 * that needs throttling (claim submissions, front-end submission, …), so a
	 * fix to the underlying mechanism (e.g. IP resolution behind a proxy) only
	 * has to be made once.
	 *
	 * Each call counts as one hit. Returns whether the caller is already at or
	 * over the limit — callers should stop processing when this is true.
	 *
	 * @param string $key_prefix Unique prefix per endpoint, e.g. 'ecm_claim_rl_'.
	 * @param int    $limit      Max hits allowed within the window.
	 * @param int    $window     Window length in seconds.
	 * @return bool
	 */
	public static function rate_limit_exceeded( string $key_prefix, int $limit, int $window ): bool {
		if ( $limit <= 0 ) {
			return false;
		}

		$ip = self::get_client_ip();

		if ( '' === $ip ) {
			return false;
		}

		$key  = $key_prefix . md5( $ip );
		$hits = (int) get_transient( $key );

		if ( $hits >= $limit ) {
			return true;
		}

		set_transient( $key, $hits + 1, $window );

		return false;
	}

	public static function is_plugin_active( $basename ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $basename );
	}

	public static function get_addon_active_status( $addon_name, $is_pro = false ) {
		global $ecm_addons;

		if ( isset( $ecm_addons->{$addon_name} ) ) {
			return (bool) $ecm_addons->{$addon_name};
		}

		return false;
	}

	/**
	 * The capability that guards a generated post type's own screens.
	 *
	 * Content access defers to the post type, never to a plugin-owned
	 * capability: whoever WordPress says may edit these entries may reach the
	 * screen that edits them. Falls back to `edit_posts` while the post type is
	 * not registered yet (the menu is built on `admin_menu`, after `init`, so in
	 * practice it always is).
	 */
	public static function get_post_type_menu_capability( string $post_type ): string {
		$object     = get_post_type_object( $post_type );
		$capability = ( $object && ! empty( $object->cap->edit_posts ) ) ? $object->cap->edit_posts : 'edit_posts';

		/**
		 * Filters the capability required to reach a post type's ECM screens.
		 *
		 * @param string $capability Capability slug.
		 * @param string $post_type  Post type key.
		 */
		return (string) apply_filters( 'easy_content_manager/post_type/menu_capability', $capability, $post_type );
	}

	public static function get_admin_menu_list(): array {
		$post_types = self::get_all_custom_post_types();
		$custom_post_types = self::get_settings( 'inherited_post_type' );
		$menu = [];
		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Dashboard', 'easy-content-manager' ),
			'capability' => Capabilities::VIEW_APP,
		];

		foreach ( $post_types as $post_type ) {
			$post_content = maybe_unserialize( $post_type->post_content );

			$sub_items = [];

			if ( ! empty( $post_content['taxonomies'] ) ) {
				$sub_items[] = [
					'slug'  => '',
					'title' => $post_content['post_type'],
				];

				foreach ( $post_content['taxonomies'] as $key => $value ) {
					$sub_items[] = [
						'slug'  => $value,
						'title' => $value,
					];
				}
			}

			$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-' . $post_content['post_slug'] ] = [
				'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
				'capability'  => self::get_post_type_menu_capability( $post_content['post_slug'] ),
				'title'       => $post_content['post_type'],
				'ID'       => $post_type->ID,
			];

			if ( ! empty( $sub_items ) ) {
				$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-' . $post_content['post_slug'] ]['sub_items'] = $sub_items;
			}
		}//end foreach
		foreach ( $custom_post_types as $key => $value ) {
			if ( $value ) {
				$title = ucwords( str_replace( '-', ' ', $key ) );
				$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-' . $key ] = [
					'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
					'title' => $title,
					'capability' => self::get_post_type_menu_capability( $key ),
					'is_excluded' => true,
				];

				$sub_items = [];

				$taxonomies = get_object_taxonomies( $key );

				if ( ! empty( $taxonomies ) ) {
					$sub_items[] = [
						'slug'  => '',
						'title' => $key,
					];

					foreach ( $taxonomies as $taxonomy ) {
						if ( 'post_format' === $taxonomy ) {
							continue;
						}
						$sub_items[] = [
							'slug'  => $taxonomy,
							'title' => ucfirst( $taxonomy ),
						];
					}
				}

				if ( ! empty( $sub_items ) ) {
					$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-' . $key ]['sub_items'] = $sub_items;
				}
			}//end if
		}//end foreach

		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-post-types' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Post Types', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_POST_TYPES,
		];
		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-taxonomy' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Taxonomy', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_TAXONOMIES,
		];
		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-field-groups' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Field Groups', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_FIELD_GROUPS,
		];

		// For addons
		if ( SeLicense::is_pro() ) {
			$menu = apply_filters( 'easy_content_manager/add_addons_menu', $menu );
		}

		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-addons' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Add-ons', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_SETTINGS,
		];

		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-tools' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Tools', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_TOOLS,
		];

		$menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-settings' ] = [
			'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
			'title' => __( 'Settings', 'easy-content-manager' ),
			'capability' => Capabilities::MANAGE_SETTINGS,
		];

		return apply_filters( 'easy_content_manager/admin_menu_list', $menu );
	}

	/**
	 * React admin routes and the capabilities WordPress uses to register them.
	 *
	 * Keeping this map in PHP gives the menu, direct requests and the React
	 * router one authorization source. Query-string children such as `action`,
	 * `path` and `id` intentionally inherit their parent `page` capability.
	 *
	 * @return array<string,string> Route slug => capability slug.
	 */
	public static function get_react_admin_route_capabilities(): array {
		$routes = [];

		foreach ( self::get_admin_menu_list() as $route => $item ) {
			if ( ! empty( $item['capability'] ) ) {
				$routes[ $route ] = (string) $item['capability'];
			}
		}

		$app_prefix = self::get_ecm_app_slug() . '-';
		foreach ( array_keys( self::get_all_custom_post_types_with_modified_data() ) as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( $post_type_object && ! empty( $post_type_object->cap->edit_posts ) ) {
				$routes[ $app_prefix . $post_type ] = self::get_post_type_menu_capability( $post_type );
			}
		}

		foreach ( self::get_ecm_settings_formatted_global_data() as $settings_route ) {
			if ( ! empty( $settings_route['slug'] ) ) {
				$route = (string) $settings_route['slug'];
				// This mirrors RegisterPostTypes::menu_capability(): a colliding
				// post-type route is registered with that post type's capability.
				$post_type = 0 === strpos( $route, $app_prefix ) ? substr( $route, strlen( $app_prefix ) ) : '';
				$routes[ $route ] = post_type_exists( $post_type )
					? self::get_post_type_menu_capability( $post_type )
					: Capabilities::MANAGE_SETTINGS;
			}
		}

		/**
		 * Filters the capability map used to authorize React admin routes.
		 *
		 * @param array<string,string> $routes Route slug => capability slug.
		 */
		return apply_filters( 'easy_content_manager/react_route_capabilities', $routes );
	}

	/**
	 * React routes the current user may visit, in menu order.
	 *
	 * @return string[]
	 */
	public static function get_allowed_react_admin_routes(): array {
		$allowed = [];

		foreach ( self::get_react_admin_route_capabilities() as $route => $capability ) {
			if ( current_user_can( $capability ) ) {
				$allowed[] = $route;
			}
		}

		return $allowed;
	}

	/**
	 * Admin menu entries the current user may see in the React menu portal.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_allowed_admin_menu_list(): array {
		return array_filter(
			self::get_admin_menu_list(),
			function ( $item ) {
				return ! empty( $item['capability'] ) && current_user_can( $item['capability'] );
			}
		);
	}

	public static function plugin_page_hook_suffix( $hook ): bool {
		if ( strpos( $hook, '_page_' . EASY_CONTENT_MANAGER_PLUGIN_SLUG ) !== false ) {
			return true;
		}

		return false;
	}

	public static function is_dev_mode_enable() {
		$environment = wp_get_environment_type();
		if ( 'local' === $environment || 'development' === $environment ) {
			return true;
		}
	}

	public static function create_slugs( $str, $seperator = '_' ) {
		$str = strtolower( $str );
		$str = str_replace( ' ', $seperator, $str );

		return $str;
	}

	public static function deep_merge_args( $defaults, $args ) {
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
				$defaults[ $key ] = self::deep_merge_args( $defaults[ $key ], $value );
			} else {
				$defaults[ $key ] = $value;
			}
		}

		return $defaults;
	}

	public static function get_sample_permalink_args( $id, $new_title = null, $new_slug = null ) {
		$response = array();
		$post = get_post( $id );
		if ( ! $post ) {
			return '';
		}
		list($permalink, $post_name) = get_sample_permalink( $post->ID, $new_title, $new_slug );
		$view_link = false;
		// `edit_post` is a meta capability, so the post ID is meaningful here —
		// `manage_options` silently discarded it and made this an admin-only check.
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			if ( 'draft' === $post->post_status || empty( $post->post_name ) ) {
				$view_link = get_preview_post_link( $post );
			} else {
				if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
					$view_link = get_permalink( $post );
				} else {
					$view_link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );
				}
			}
		}
		if ( false !== $view_link ) {
			$response['view_link'] = esc_url( $view_link );
		} else {
			$response['permalink'] = $permalink;
		}

		$display_link = rtrim( str_replace( '%pagename%', $post_name, $permalink ) );
		$response['editable_postname'] = $post_name;
		$response['display_link'] = $display_link;
		$response['post_name'] = $post_name;

		return (array) $response;
	}

	public static function is_valid_site_url( string $url ): bool {
		return str_starts_with( $url, get_option( 'siteurl' ) );
	}

	public static function has_theme_support() {
		$dir = get_template_directory();
		if ( is_dir( $dir . '/ecm' ) ) {
			return $dir . '/ecm';
		}

		return false;
	}

	public static function get_ecm_app_slug() {
		return apply_filters( 'ecm_app_slug', 'ecm-app' );
	}
	public static function get_ecm_slug_integrations() {
		return apply_filters( 'ecm_slug_integrations', [] );
	}

	public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		$template = false;

		if ( ! $template ) {
			$template = self::locate_template( $template_name, $template_path, $default_path );
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$filter_template = apply_filters( 'ecm/get_template', $template, $template_name, $args, $template_path, $default_path );

		if ( $filter_template !== $template ) {
			if ( ! file_exists( $filter_template ) ) {
				/* translators: %s template */
				wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'easy-content-manager' ), '<code>' . $filter_template . '</code>' ), '1.0.0' );
				return;
			}
			$template = $filter_template;
		}

		$action_args = array(
			'template_name' => $template_name,
			'template_path' => $template_path,
			'located'       => $template,
			'args'          => $args,
		);

		if ( ! empty( $args ) && is_array( $args ) ) {
			if ( isset( $args['action_args'] ) ) {
				wc_doing_it_wrong(
					__FUNCTION__,
					__( 'action_args should not be overwritten when calling ecm/get_template.', 'easy-content-manager' ),
					'1.0.0'
				);
				unset( $args['action_args'] );
			}
            extract($args); // @codingStandardsIgnoreLine
		}

		do_action( 'ecm/before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
		include $action_args['located'];

		do_action( 'ecm/after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
	}

	public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = self::template_path();
		}

		if ( ! $default_path ) {
			$default_path = self::plugin_path() . 'templates/';
		}

		$cs_template = str_replace( '_', '-', $template_name );
		$template    = locate_template(
			array(
				trailingslashit( $template_path ) . $cs_template,
				$cs_template,
			)
		);

		if ( empty( $template ) ) {
			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name,
				)
			);
		}

		// Get default template/.
		if ( ! $template || EASY_CONTENT_MANAGER_TEMPLATE_DEBUG_MODE ) {
			if ( empty( $cs_template ) ) {
				$template = $default_path . $template_name;
			} else {
				$template = $default_path . $cs_template;
			}
		}

		// Return what we found.
		return apply_filters( 'ecm/locate_template', $template, $template_name, $template_path );
	}

	public static function template_path() {
		return apply_filters( 'ecm/template_path', 'ecm/' );
	}

	public static function plugin_path() {
		return apply_filters( 'ecm/plugin_path', EASY_CONTENT_MANAGER_ROOT_DIR_PATH );
	}
}
