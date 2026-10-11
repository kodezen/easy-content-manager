<?php

namespace EasyContentManager;

use EasyContentManager\Classes\ScriptBase;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Assets extends ScriptBase {

	/**
	 * WordPress user screens. `profile.php` is reachable by every logged-in role,
	 * so these get the reduced payload from get_user_screen_scripts_data().
	 */
	private const USER_SCREEN_HOOKS = [ 'user-new.php', 'user-edit.php', 'profile.php' ];

	private static $frontend_assets_enqueued = false;

	/**
	 * Safe filemtime for cache-busting: never emits a warning if the built asset
	 * is missing (partial deploy / failed build) — falls back to the plugin version.
	 *
	 * @param string $relative_path Path relative to the assets dir.
	 * @return string|int
	 */
	private function asset_version( $relative_path ) {
		$full = EASY_CONTENT_MANAGER_ASSETS_DIR_PATH . $relative_path;
		return file_exists( $full ) ? filemtime( $full ) : EASY_CONTENT_MANAGER_VERSION;
	}

	/**
	 * Safely load a webpack asset manifest. A missing manifest must not fatal the
	 * request — return an empty-dependency fallback instead.
	 *
	 * @param string $relative_path Manifest path relative to the assets dir.
	 * @return array{dependencies: array, version: string}
	 */
	private function asset_deps( $relative_path ) {
		$full = EASY_CONTENT_MANAGER_ASSETS_DIR_PATH . $relative_path;
		if ( file_exists( $full ) ) {
			return include $full;
		}
		return [ 'dependencies' => [], 'version' => EASY_CONTENT_MANAGER_VERSION ];
	}

	public static function init() {
		$self = new self();
		add_action( 'admin_enqueue_scripts', [ $self, 'backend_scripts' ], defined( 'EASY_CONTENT_MANAGER_BACKEND_SCRIPTS_PRIORITY' ) ? EASY_CONTENT_MANAGER_BACKEND_SCRIPTS_PRIORITY : 10 );
		add_action( 'wp_enqueue_scripts', [ $self, 'frontend_scripts' ], 1 );
		// Detect ECM shortcodes in the main query / widgets early so their CSS+JS load
		// in the header (correct timing, no FOUC). Runs after registration (priority 1).
		add_action( 'wp_enqueue_scripts', [ $self, 'maybe_enqueue_frontend' ], 20 );
		// Render-time catch-all: any ECM shortcode rendered anywhere (page builders,
		// template parts, do_shortcode) enqueues the bundle when it actually runs.
		add_action( 'ecm_shortcode_loaded', [ $self, 'shortcode_scripts' ], 999 );
		add_action( 'ecm_app_loaded', [ $self, 'app_scripts' ], 10 );
		add_action( 'enqueue_block_editor_assets', [ $self, 'block_editor_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $self, 'classic_editor_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $self, 'react_editor_script' ] );
		add_action( 'admin_enqueue_scripts', [ $self, 'wp_user_scripts' ] );
		// allow academy lms assets loading
		add_filter( 'academy/allowed_third_party_plugins_assets', [ $self, 'allowed_ecm_plugins_assets' ] );

		// Footer fallback for template shortcodes
		add_action( 'wp_footer', [ $self, 'maybe_enqueue_in_footer' ], 1 );
	}

	public function backend_scripts( $hook ) {
		if ( Helper::plugin_page_hook_suffix( $hook ) ) {
			// Remove Notices
			remove_all_actions( 'admin_notices' );
			// dequeue third party plugin assets
			add_action(
				'wp_print_scripts',
				function () {
					$isSkip = apply_filters( 'easy-content-manager/skip_no_conflict_backend_scripts', Helper::is_dev_mode_enable() );

					if ( $isSkip ) {
						return;
					}

					global $wp_scripts;
					if ( ! $wp_scripts ) {
						return;
					}

					$pluginUrl = plugins_url();
					foreach ( $wp_scripts->queue as $script ) {
						$src = $wp_scripts->registered[ $script ]->src;
						if ( strpos( $src, $pluginUrl ) !== false && ! strpos( $src, EASY_CONTENT_MANAGER_PLUGIN_SLUG ) !== false ) {
							wp_dequeue_script( $wp_scripts->registered[ $script ]->handle );
						}
					}
				},
				1
			);

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'easy-content-manager-admin-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/backend.css', array( 'wp-components' ), $this->asset_version( 'build/backend.css' ), 'all' );
			wp_enqueue_style( 'easy-content-manager-icon', EASY_CONTENT_MANAGER_ASSETS_URI . 'lib/css/easy-content-manager-icon/style.css', array(), $this->asset_version( 'lib/css/easy-content-manager-icon/style.css' ), 'all' );
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}

			$dependencies = $this->asset_deps( sprintf( 'build/backend.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
			wp_enqueue_script(
				'easy-content-manager-admin-scripts',
				EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/backend.%s.js', EASY_CONTENT_MANAGER_VERSION ),
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_localize_script( 'easy-content-manager-admin-scripts', 'EasyContentManagerGlobal', $this->get_backend_scripts_data() );
			wp_set_script_translations( 'easy-content-manager-admin-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
		}//end if
	}

	public function get_backend_scripts_data( bool $include_structural = true ) {
		$args = array(
			'users_can_register'    => get_option( 'users_can_register' ),
		);
		return apply_filters(
			'easy_content_manager/assets/backend_scripts_data',
			array_merge(
				$this->get_scripts_data( $include_structural ),
				$args
			)
		);
	}

	public function block_editor_scripts() {
		// ECM fields are post meta, so this is only needed in the post editor —
		// not the Site Editor or the Widgets screen.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'post' !== $screen->base ) {
			return;
		}

		wp_enqueue_style( 'easy-content-manager-block-editor-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/block-editor.css', array( 'wp-components' ), $this->asset_version( 'build/block-editor.css' ), 'all' );
		$dependencies = $this->asset_deps( sprintf( 'build/block-editor.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
		wp_enqueue_script(
			'easy-content-manager-block-editor-scripts',
			EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/block-editor.%s.js', EASY_CONTENT_MANAGER_VERSION ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_localize_script( 'easy-content-manager-block-editor-scripts', 'EasyContentManagerGlobal', $this->get_backend_scripts_data() );
		wp_set_script_translations( 'easy-content-manager-block-editor-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
	}

	public function react_editor_script( $hook ) {
		// This bundle only registers field/taxonomy render hooks consumed by the
		// plugin's own screens, the post editors and the user screens. Don't load it
		// (and run its localize queries) on every unrelated wp-admin page.
		$allowed_hooks = apply_filters(
			'easy_content_manager/react_editor_allowed_hooks',
			[ 'post.php', 'post-new.php', 'user-new.php', 'user-edit.php', 'profile.php' ]
		);
		if ( ! Helper::plugin_page_hook_suffix( $hook ) && ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_style( 'easy-content-manager-icon', EASY_CONTENT_MANAGER_ASSETS_URI . 'lib/css/easy-content-manager-icon/style.css', array(), $this->asset_version( 'lib/css/easy-content-manager-icon/style.css' ), 'all' );
		wp_enqueue_style( 'easy-content-manager-react-editor-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/ecm-react-editor.css', array( 'wp-components' ), $this->asset_version( 'build/ecm-react-editor.css' ), 'all' );
		$dependencies = $this->asset_deps( sprintf( 'build/ecm-react-editor.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
		wp_enqueue_script(
			'easy-content-manager-react-editor-scripts',
			EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/ecm-react-editor.%s.js', EASY_CONTENT_MANAGER_VERSION ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		$localize_data = in_array( $hook, self::USER_SCREEN_HOOKS, true )
			? $this->get_user_screen_scripts_data()
			: $this->get_backend_scripts_data();

		wp_localize_script( 'easy-content-manager-react-editor-scripts', 'EasyContentManagerGlobal', $localize_data );
		wp_set_script_translations( 'easy-content-manager-react-editor-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
	}

	public function classic_editor_scripts( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook && 'user-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'easy-content-manager-block-editor-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/classic-editor.css', array( 'wp-components' ), $this->asset_version( 'build/classic-editor.css' ), 'all' );
		$dependencies = $this->asset_deps( sprintf( 'build/classic-editor.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
		wp_enqueue_script(
			'easy-content-manager-classic-editor-scripts',
			EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/classic-editor.%s.js', EASY_CONTENT_MANAGER_VERSION ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		$localize_data = in_array( $hook, self::USER_SCREEN_HOOKS, true )
			? $this->get_user_screen_scripts_data()
			: $this->get_backend_scripts_data();

		wp_localize_script( 'easy-content-manager-classic-editor-scripts', 'EasyContentManagerGlobal', $localize_data );
		wp_set_script_translations( 'easy-content-manager-classic-editor-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
	}

	public function wp_user_scripts( $hook ) {
		if ( ! in_array( $hook, self::USER_SCREEN_HOOKS, true ) ) {
			return;
		}

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		wp_enqueue_style( 'easy-content-manager-wp-user-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/wp-user.css', array( 'wp-components' ), $this->asset_version( 'build/wp-user.css' ), 'all' );
		$dependencies = $this->asset_deps( sprintf( 'build/wp-user.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
		wp_enqueue_script(
			'easy-content-manager-wp-user-scripts',
			EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/wp-user.%s.js', EASY_CONTENT_MANAGER_VERSION ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_localize_script( 'easy-content-manager-wp-user-scripts', 'EasyContentManagerGlobal', $this->get_user_screen_scripts_data() );
		wp_set_script_translations( 'easy-content-manager-wp-user-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
	}

	public function app_scripts( $hook_suffix ) {
		add_action('admin_enqueue_scripts', function ( $current_hook ) use ( $hook_suffix ) {
			$disable_ecm_app_loading = apply_filters( 'ecm_disable_app_scripts_loading', $current_hook !== $hook_suffix );
			if ( $disable_ecm_app_loading ) {
				return;
			}

			wp_enqueue_style( 'easy-content-manager-' . $hook_suffix . '-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/app.css', array( 'wp-components' ), $this->asset_version( 'build/app.css' ), 'all' );
			wp_enqueue_style( 'easy-content-manager-icon', EASY_CONTENT_MANAGER_ASSETS_URI . 'lib/css/easy-content-manager-icon/style.css', array(), $this->asset_version( 'lib/css/easy-content-manager-icon/style.css' ), 'all' );
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}

			$dependencies = $this->asset_deps( sprintf( 'build/app.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );
			wp_enqueue_script(
				'easy-content-manager-' . $hook_suffix . '-scripts',
				EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/app.%s.js', EASY_CONTENT_MANAGER_VERSION ),
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_localize_script( 'easy-content-manager-' . $hook_suffix . '-scripts', 'EasyContentManagerGlobal', $this->get_backend_scripts_data() );
			wp_set_script_translations( 'easy-content-manager-' . $hook_suffix . '-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
		});
	}

	public function frontend_scripts() {
		$dependencies = $this->asset_deps( sprintf( 'build/frontend.%s.asset.php', EASY_CONTENT_MANAGER_VERSION ) );

		wp_register_style( 'easy-content-manager-style', EASY_CONTENT_MANAGER_ASSETS_URI . 'build/frontend.css', array( 'wp-components' ), $this->asset_version( 'build/frontend.css' ), 'all' );
		wp_register_style( 'easy-content-manager-icon', EASY_CONTENT_MANAGER_ASSETS_URI . 'lib/css/easy-content-manager-icon/style.css', array(), $this->asset_version( 'lib/css/easy-content-manager-icon/style.css' ), 'all' );
		
		wp_register_script(
			'easy-content-manager-frontend-scripts',
			EASY_CONTENT_MANAGER_ASSETS_URI . sprintf( 'build/frontend.%s.js', EASY_CONTENT_MANAGER_VERSION ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_set_script_translations( 'easy-content-manager-frontend-scripts', 'easy-content-manager', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'languages' );
		// NOTE: localization (which runs several unbounded queries) is deferred to
		// shortcode_scripts() so it only runs on pages that actually enqueue the bundle.
	}

	public function shortcode_scripts() {
		if ( self::$frontend_assets_enqueued ) {
			return;
		}
		self::$frontend_assets_enqueued = true;

		// Localize now — only when the bundle is genuinely being enqueued.
		wp_localize_script(
			'easy-content-manager-frontend-scripts',
			'EasyContentManagerGlobal',
			$this->get_frontend_scripts_data()
		);

		wp_enqueue_style( 'easy-content-manager-style' );
		wp_enqueue_style( 'easy-content-manager-icon' );

		wp_enqueue_script( 'easy-content-manager-frontend-scripts' );
	}

	/**
	 * Early detection on wp_enqueue_scripts: if the current page's main query or
	 * widgets contain an ECM shortcode, enqueue the bundle now (header timing).
	 */
	public function maybe_enqueue_frontend() {
		if ( ! self::$frontend_assets_enqueued && $this->has_ecm_shortcode() ) {
			$this->shortcode_scripts();
		}
	}

	public function maybe_enqueue_in_footer() {
		if (
			! self::$frontend_assets_enqueued
			&& wp_script_is( 'easy-content-manager-frontend-scripts', 'registered' )
			&& $this->has_ecm_shortcode()
		) {
			$this->shortcode_scripts();
		}
	}

	public function allowed_ecm_plugins_assets( $plugins ) {
		$plugins[] = EASY_CONTENT_MANAGER_PLUGIN_SLUG;
		return $plugins;
	}
}
