<?php

use EasyContentManager\Features\RegisterFields;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hooks {

	private function __construct() {
		add_action( 'easy_content_manager_loaded', [ $this, 'on_ecm_loaded' ], 20 );
		add_action( 'init', [ $this, 'register_post_meta' ] );
		add_action( 'init', [ $this, 'register_term_meta' ] );
		add_action( 'user_new_form', [ $this, 'load_user_app_template' ] );
		add_action( 'edit_user_profile', [ $this, 'load_user_app_template' ] );
		add_action( 'show_user_profile', [ $this, 'load_user_app_template' ] );
		add_action( 'wp_footer', [ $this, 'ecm_add_react_wrap' ] );
		add_action( 'user_register', [ $this, 'save_ecm_user_meta_fields' ] );
		add_action( 'personal_options_update', [ $this, 'save_ecm_user_meta_fields' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_ecm_user_meta_fields' ] );

		// The post type / field group localise payloads are memoised per request. Tie
		// the memo reset to the transient invalidation that already happens on every
		// write, so a create-then-read inside one request can never return stale data
		// — and so future call sites (including pro addons) get it for free.
		foreach ( [ 'ecm_cached_prepared_post_types', 'ecm_cached_prepared_field_groups', 'ecm_cached_prepared_custom_fields' ] as $transient ) {
			add_action( 'delete_transient_' . $transient, [ $this, 'flush_runtime_caches' ] );
			add_action( 'set_transient_' . $transient, [ $this, 'flush_runtime_caches' ] );
		}
	}

	/**
	 * Drop the per-request memo caches held by the Helper traits.
	 *
	 * @return void
	 */
	public function flush_runtime_caches() {
		Helper::flush_post_type_runtime_cache();
		Helper::flush_field_group_runtime_cache();
	}

	public static function get_instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	public function on_ecm_loaded() {
		$cached_fields = array_merge(
			get_transient( 'ecm_cached_prepared_custom_fields' ) ? get_transient( 'ecm_cached_prepared_custom_fields' ) : [],
			get_transient( 'ecm_cached_prepared_field_groups' ) ? get_transient( 'ecm_cached_prepared_field_groups' ) : []
		);

		$local_post_type = ecm_get_json_from_files( ecm_get_plugins_post_type_path() );

		$theme_post_type = [];
		if ( Helper::has_theme_support() ) {
			$theme_post_type = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/post-types' ] );
		}

		$all_fields = array_merge( $cached_fields, RegisterFields::prepare_field_for_register( array_merge( $local_post_type, $theme_post_type ), true ) );

		if ( ! empty( $all_fields ) ) {
			Helper::build_field_registry_cache( $all_fields );
		}
	}

	public function register_post_meta() {
		// This runs on every request (init). Cache the list of post-type slugs so we
		// don't run an unbounded query + per-post unserialize on each page load.
		$slugs = get_transient( 'ecm_priority_meta_slugs' );

		if ( false === $slugs ) {
			$items = get_posts( [
				'post_type'      => Helper::ECM_POST_TYPE_NAME,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			] );

			$slugs = [];
			foreach ( $items as $post_id ) {
				$content = maybe_unserialize( get_post_field( 'post_content', $post_id ) );
				if ( ! empty( $content['post_slug'] ) ) {
					$slugs[] = $content['post_slug'];
				}
			}

			set_transient( 'ecm_priority_meta_slugs', $slugs, DAY_IN_SECONDS );
		}

		if ( empty( $slugs ) ) {
			return;
		}

		foreach ( $slugs as $post_slug ) {
			register_post_meta(
				$post_slug,
				'ecm_priority',
				[
					'type'              => 'integer',
					'single'            => true,
					'default'           => 0,
					'sanitize_callback' => 'absint',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
					'show_in_rest'      => true,
				]
			);
		}
	}

	public function register_term_meta() {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'names' );

		foreach ( $taxonomies as $taxonomy ) {
			register_term_meta(
				$taxonomy,
				'term_priority',
				[
					'type'              => 'integer',
					'description'       => __( 'Term display priority/order', 'easy-content-manager' ),
					'single'            => true,
					'sanitize_callback' => 'absint',
					'show_in_rest'      => true,
					'default'           => 0,
				]
			);
		}
	}

	public function load_user_app_template() {
		Helper::get_template( 'admin/users/app.php' );
	}

	public function ecm_add_react_wrap() {
		echo '<div id="ecm-frontend-react-wrap" class="ecm-frontend-react-wrap"></div>';
	}

	public function save_ecm_user_meta_fields( $user_id ) {
		// `edit_user` is a meta capability, so this asks the real question: may
		// the current user edit THIS profile? It matches the auth callbacks the
		// same fields register in RegisterFields, which already allowed a user to
		// edit their own fields and anyone with `edit_users` to edit others'.
		// Requiring `manage_options` here meant an editor could save a profile
		// and silently lose every ECM field on it.
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		global $wpdb;

		// Primary guard: only meta keys belonging to a user-scoped ECM field group may
		// be written. This is the same allowlist the read side uses (Ajax\User), so the
		// profile UI can never reach unrelated WordPress usermeta.
		$allowed_keys = Helper::get_user_scoped_custom_field_meta_keys();

		if ( empty( $allowed_keys ) ) {
			return;
		}

		// Defence in depth: even if a custom field were ever named after a core record,
		// never let it overwrite protected meta or role/capability rows.
		$denied_keys = [
			'wp_capabilities',
			'wp_user_level',
			'session_tokens',
			$wpdb->prefix . 'capabilities',
			$wpdb->prefix . 'user_level',
		];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core verifies the profile/new-user nonce before firing these hooks; capability checked above.
		foreach ( array_keys( $_POST ) as $key ) {
			if ( 0 !== strpos( $key, 'meta_' ) ) {
				continue;
			}

			$meta_key = substr( $key, strlen( 'meta_' ) );

			if (
				! in_array( $meta_key, $allowed_keys, true )
				|| is_protected_meta( $meta_key, 'user' )
				|| in_array( $meta_key, $denied_keys, true )
				|| preg_match( '/capabilities$|user_level$/', $meta_key )
			) {
				continue;
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized recursively by sanitize_meta_value().
			$meta_value = $this->sanitize_meta_value( wp_unslash( $_POST[ $key ] ) );

			update_user_meta( $user_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Recursively sanitize meta value based on its type
	 *
	 * @param mixed $value The value to sanitize
	 * @return mixed Sanitized value
	 */
	private function sanitize_meta_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_meta_value' ], $value );
		}

		if ( is_string( $value ) && in_array( substr( trim( $value ), 0, 1 ), [ '[', '{' ], true ) ) {
			$decoded = json_decode( $value, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				return $this->sanitize_meta_value( $decoded );
			}
		}

		if ( is_bool( $value ) ) {
			return (bool) $value;
		}
		
		if ( is_numeric( $value ) ) {
			if ( strpos( $value, '.' ) !== false ) {
				return (float) $value;
			}
			return (int) $value;
		}
		
		if ( is_null( $value ) ) {
			return null;
		}

		if ( is_object( $value ) ) {
			return $this->sanitize_meta_value( (array) $value );
		}
		
		return sanitize_text_field( $value );
	}

}

Hooks::get_instance();

