<?php

namespace EasyContentManager\Classes;

use EasyContentManager\Admin\Menu;
use EasyContentManager\Admin\SeLicense;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ScriptBase {


	/**
	 * @param bool $include_structural Whether to run the expensive, site-wide
	 *                                 lookups (field groups, post types, taxonomies,
	 *                                 settings, menu). Callers that only need these
	 *                                 for privileged users can pass false and skip
	 *                                 the queries entirely rather than discarding
	 *                                 the result afterwards.
	 */
	protected function get_scripts_data( bool $include_structural = true ): array {
		global $ecm_settings;
		$menu                    = new Menu();
		$current_user            = wp_get_current_user();
		$allowed_react_routes    = $include_structural ? Helper::get_allowed_react_admin_routes() : [];
		$first_allowed_route     = reset( $allowed_react_routes );
		$content_manager_globals = array(
			'nonce'                  => wp_create_nonce( 'wp_rest' ),
			'ecm_nonce'              => wp_create_nonce( 'easy_content_manager_nonce' ),
			'ecm_app_slug'           => Helper::get_ecm_app_slug(),
			'rest_url'               => esc_url_raw( rest_url() ),
			'namespace'              => EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/v1/',
			'plugin_root_url'        => EASY_CONTENT_MANAGER_PLUGIN_ROOT_URI,
			'plugin_root_path'       => EASY_CONTENT_MANAGER_ROOT_DIR_PATH,
			'ajaxurl'                => esc_url( admin_url( 'admin-ajax.php' ) ),
			'admin_url'              => admin_url(),
			'site_url'               => site_url(),
			'route_path'             => wp_parse_url( admin_url(), PHP_URL_PATH ),
			'allowed_react_routes'   => $allowed_react_routes,
			'first_allowed_ecm_url'  => $first_allowed_route
				? admin_url( 'admin.php?page=' . rawurlencode( $first_allowed_route ) )
				: admin_url(),
			'is_plain_permalink'     => $this->is_plain_permalink(),
			'menu'                   => wp_json_encode( $include_structural ? $this->modify_menu_post_type() : [] ),
			'current_user_id'        => get_current_user_id(),
			'current_user_roles'     => (array) $current_user->roles,
			'is_rtl'                 => is_rtl(),
			'is_admin'               => is_admin(),
			'current_user_can'       => array_merge(
				[
					'manage_options'    => current_user_can( 'manage_options' ),
					'manage_categories' => current_user_can( 'manage_categories' ),
				],
				Capabilities::current_user_caps()
			),
			'can_manage_permissions' => current_user_can( 'manage_options' ) && current_user_can( 'promote_users' ),
			'toplevel_menu_icon_url' => $menu->get_toplevel_menu_icon_url(),
			'toplevel_menu_title'    => $menu->get_toplevel_menu_title(),
			'is_premium'             => SeLicense::is_pro(),
			'logo_url'               => $menu->get_logo_url(),
			'ecm_settings'           => $include_structural ? Helper::get_ecm_settings() : null,
			'field_groups'           => $include_structural ? Helper::get_all_custom_field_groups() : [],
			'post_types'             => $include_structural ? Helper::get_all_custom_post_types_with_modified_data() : [],
			'taxonomies'             => $include_structural ? Helper::get_all_custom_taxonomies_with_modified_data() : [],
			'ecm_settings_slugs'     => $include_structural ? Helper::get_ecm_settings_formatted_global_data() : [],
			'color_theme_mode'       => $ecm_settings->color_theme_mode,
		);

		return $content_manager_globals;
	}

	/**
	 * Post types and field groups the front-end bundle is allowed to see.
	 *
	 * Only the front-end submission form consumes these, and only for the post
	 * types that form is configured to accept. Shipping the whole registry
	 * published every custom post type's configuration and every field group's
	 * complete definition — keys, labels, conditional logic, location rules,
	 * drafts included — to anonymous visitors on any page carrying an ECM
	 * shortcode, and ran two unbounded queries to do it.
	 *
	 * @return array{post_types:array,field_groups:array}
	 */
	private function get_frontend_structure_data(): array {
		$empty = [
			'post_types'   => [],
			'field_groups' => [],
		];

		$settings = Helper::get_frontend_submission_shortcode_settings();

		if ( ! Helper::is_addon_enabled( 'frontend-submission' ) ) {
			return $empty;
		}

		$allowed = array_values( array_filter( array_map( 'strval', (array) ( $settings['specific_post_types'] ?? [] ) ) ) );

		/**
		 * Filters the post types whose structure is exposed to the front-end bundle.
		 *
		 * @param string[] $allowed Post type slugs.
		 */
		$allowed = (array) apply_filters( 'easy_content_manager/frontend/exposed_post_types', $allowed );

		if ( empty( $allowed ) ) {
			return $empty;
		}

		return [
			'post_types'   => array_intersect_key(
				Helper::get_all_custom_post_types_with_modified_data(),
				array_flip( $allowed )
			),
			'field_groups' => Helper::get_published_field_groups_for_post_types( $allowed ),
		];
	}

	/**
	 * Payload for the WordPress user screens.
	 *
	 * `profile.php` is reachable by every logged-in role, Subscriber included, so
	 * it must not ship the admin app's structural payload — all field groups, all
	 * post types, all taxonomies, `ecm_settings` and the admin menu map. Users who
	 * can actually open the Content Manager keep the full data; everyone else gets
	 * only the user-scoped field groups the profile form renders, plus the nonces
	 * and URLs the bundle needs to function.
	 *
	 * @return array
	 */
	protected function get_user_screen_scripts_data(): array {
		if ( Capabilities::user_can( Capabilities::VIEW_APP ) ) {
			return $this->get_backend_scripts_data();
		}

		// Skip the expensive site-wide lookups outright rather than computing and
		// then discarding them below — this branch is reached by every logged-in
		// role, Subscriber included.
		$data = $this->get_backend_scripts_data( false );

		$structural = [
			'post_types',
			'taxonomies',
			'ecm_settings',
			'ecm_settings_slugs',
			'menu',
			'allowed_react_routes',
			'first_allowed_ecm_url',
		];

		foreach ( $structural as $key ) {
			unset( $data[ $key ] );
		}

		$data['field_groups'] = Helper::get_user_scoped_field_groups();

		return $data;
	}

	protected function get_frontend_scripts_data(): array {
		$structure = $this->get_frontend_structure_data();

		$content_manager_globals = array(
			'nonce'                        => wp_create_nonce( 'wp_rest' ),
			'ecm_nonce'                    => wp_create_nonce( 'easy_content_manager_nonce' ),
			'rest_url'                     => esc_url_raw( rest_url() ),
			'namespace'                    => EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/v1/',
			'ajaxurl'                      => esc_url( admin_url( 'admin-ajax.php' ) ),
			'site_url'                     => site_url(),
			'current_user_id'              => get_current_user_id(),
			'current_post_id'              => get_the_ID(),
			'addons'                       => json_decode( get_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME, '{}' ) ),
			'upvote_settings'              => Helper::get_settings( 'upvote_addon', [] ),
			'frontend_submission_settings' => Helper::get_settings( 'frontend_submission_addon', [] ),
			'svg_icons'                    => Helper::get_localize_able_svg_icon(),
			'users_can_register'           => get_option( 'users_can_register' ),
			'is_admin'                     => current_user_can( 'manage_options' ),
			'post_types'                   => $structure['post_types'],
			'field_groups'                 => $structure['field_groups'],
			'current_user_roles'           => wp_get_current_user()->roles,
		);

		return apply_filters(
			'easy_content_manager/assets/frontend_scripts_data',
			$content_manager_globals
		);
	}

	public function is_plain_permalink(): bool {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			return true;
		}
		return false;
	}

	private function modify_menu_post_type() {
		$menus = Helper::get_allowed_admin_menu_list();
		foreach ( $menus as $key => $menu ) {
			$menus[ $key ]['slug'] = Helper::create_slugs( $menu['title'] );
		}
		return $menus;
	}

	/**
	 * Result of has_ecm_shortcode() for this request.
	 *
	 * Detection scans every post in the main query against every front-end shortcode
	 * tag, then walks the sidebars. It is called from both the enqueue and the footer
	 * fallback, so the answer is computed once.
	 *
	 * @var bool|null
	 */
	private static $has_ecm_shortcode = null;

	public function has_ecm_shortcode() {
		if ( null !== self::$has_ecm_shortcode ) {
			return self::$has_ecm_shortcode;
		}

		self::$has_ecm_shortcode = $this->detect_ecm_shortcode();

		return self::$has_ecm_shortcode;
	}

	private function detect_ecm_shortcode() {
		global $wp_query;

		if ( ! $wp_query ) {
			return false;
		}

		$shortcodes = $this->get_frontend_shortcodes();

		if ( empty( $shortcodes ) ) {
			return false;
		}

		if ( ! empty( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post_obj ) {
				if ( isset( $post_obj->post_content ) && $this->check_if_shortcode_exists( $post_obj->post_content ) ) {
					return true;
				}
			}
		}

		if ( $this->check_widgets_for_shortcode() ) {
			return true;
		}

		return false;
	}

	private function check_widgets_for_shortcode() {
		global $wp_registered_sidebars;

		if ( empty( $wp_registered_sidebars ) ) {
			return false;
		}

		// Read the widget map and each widget-type option once, not per sidebar.
		$sidebars_widgets = wp_get_sidebars_widgets();
		$widget_options   = [];

		foreach ( array_keys( $wp_registered_sidebars ) as $sidebar_id ) {
			if ( empty( $sidebars_widgets[ $sidebar_id ] ) || ! is_active_sidebar( $sidebar_id ) ) {
				continue;
			}

			foreach ( $sidebars_widgets[ $sidebar_id ] as $widget_id ) {
				// Check text widgets, custom HTML, etc.
				if ( strpos( $widget_id, 'text-' ) !== 0 && strpos( $widget_id, 'custom_html-' ) !== 0 ) {
					continue;
				}

				$separator = strrpos( $widget_id, '-' );
				if ( false === $separator ) {
					continue;
				}

				$widget_base   = substr( $widget_id, 0, $separator );
				$widget_number = substr( $widget_id, $separator + 1 );

				if ( ! isset( $widget_options[ $widget_base ] ) ) {
					$widget_options[ $widget_base ] = get_option( 'widget_' . $widget_base, [] );
				}

				$content = $widget_options[ $widget_base ][ $widget_number ]['content'] ?? '';

				if ( $this->check_if_shortcode_exists( $content ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Canonical list of front-end ECM shortcodes that require the frontend bundle.
	 * Kept complete (every registered interactive shortcode) and filterable so
	 * detection never silently misses a tag. `ecm_field` is intentionally excluded:
	 * it renders server-side and needs no JS.
	 *
	 * @return string[]
	 */
	public function get_frontend_shortcodes(): array {
		return apply_filters(
			'easy_content_manager/frontend_shortcodes',
			[
				'ecm_bookmark_button', 'ecm_bookmark_badge', 'ecm_bookmark_list', 'ecm_bookmark_count',
				'ecm_claim_button', 'ecm_claim_badge', 'ecm_claim_list', 'ecm_claim_count',
				'ecm_reaction', 'ecm_reaction_count', 'ecm_reaction_list',
				'ecm_review_form', 'ecm_review_list', 'ecm_user_reviews',
				'ecm_upvote', 'ecm_upvote_count', 'ecm_upvote_list',
				'ecm_view_counter', 'ecm_view_count',
				'ecm_frontend_submission',
			]
		);
	}

	public function check_if_shortcode_exists( $content ) {
		if ( ! $content || ! is_string( $content ) ) {
			return false;
		}

		// Cheap prefilter: every front-end tag starts with `[ecm_`, so one strpos
		// skips the ~20 regex passes has_shortcode() would otherwise run per post.
		if ( false === strpos( $content, '[ecm_' ) ) {
			return false;
		}

		foreach ( $this->get_frontend_shortcodes() as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) ) {
				return true;
			}
		}

		return false;
	}
}
