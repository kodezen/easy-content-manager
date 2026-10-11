<?php
/*
 * Plugin Name:		Easy Content Manager (ECM)
 * Plugin URI:		http://kodezen.com/easy-content-manager
 * Description:		Create custom post types, taxonomies without writing any PHP code.
 * Version:			1.2.8
 * Author:			Kodezen
 * Author URI:		http://kodezen.com
 * License:			GPL-3.0+
 * License URI:		http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:		easy-content-manager
 * Domain Path:		/languages
 *
 * Requires at least: 6.4
 * Tested up to: 7.0
 * Requires PHP: 7.4
 *
 * SE Premium Only: /addons, /templates/addons, /includes/addons.php, /includes/ajax/settings.php, /includes/features/fields/repeater-field.php, /includes/features/fields/gallery-field.php
 * @fs_premium_only /addons, /templates/addons, /includes/addons.php, /includes/ajax/settings.php, /includes/features/fields/repeater-field.php, /includes/features/fields/gallery-field.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EasyContentManager' ) ) {
	final class EasyContentManager {


		private function __construct() {
			$this->define_constants();
			$this->set_global_settings();
			$this->load_dependency();
			$this->load_sdk();
			register_activation_hook( __FILE__, [ $this, 'activate' ] );
			register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
			add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
			add_action( 'easy_content_manager_loaded', [ $this, 'init_plugin' ] );
		}

		public static function init() {
			static $instance = false;

			if ( ! $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		public function load_dependency(): void {
			if ( file_exists( EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'vendor/autoload.php' ) ) {
				require_once EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'vendor/autoload.php';
			}

			// Load the licence SDK directly rather than leaving it to Composer's
			// `files` autoload.
			//
			// The SDK's "newest version wins" election only counts copies whose
			// init.php actually executes, and Composer de-duplicates autoload-files
			// by a package-stable hash — package name + relative path, not the
			// absolute vendor location. So when several active plugins each ship
			// storeengine/wordpress-sdk, only the first plugin's vendor/autoload.php
			// runs init.php; every later copy is silently skipped and never
			// registers its version. If that first plugin bundles an older SDK, our
			// newer copy loses an election it never entered.
			//
			// init.php guards itself against re-declaration, so requiring it
			// directly is safe even when other plugins do the same — which is what
			// StoreEngine core and Academy already do.
			$license_sdk = EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'vendor/storeengine/wordpress-sdk/init.php';
			if ( is_readable( $license_sdk ) ) {
				require_once $license_sdk;
			}

			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'autoload.php';
			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'functions.php';
			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'hooks.php';
			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'features/fields-api/frontend-fields.php';
			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'features/fields-api/settings-api.php';
			require_once EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH . 'features/fields-api/user-api.php';
		}

		public function set_global_settings(): void {
			$GLOBALS['ecm_settings'] = json_decode( get_option( EASY_CONTENT_MANAGER_SETTINGS_NAME, '{}' ) );
			$GLOBALS['ecm_addons'] = json_decode(get_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME, '{}' ));
		}

		public function load_sdk(): void {
			EasyContentManager\Admin\SeLicense::get_instance();
		}

		public function activate(): void {
			EasyContentManager\Installer::init();
		}

		public function deactivate() {}

		/**
		 * When WP has loaded all plugins, trigger the `easy_content_manager_loaded` hook.
		 *
		 * This ensures `easy_content_manager_loaded` is called only after all other plugins
		 * are loaded, to avoid issues caused by plugin directory naming changing
		 *
		 * @since 1.0.0
		 */
		public function on_plugins_loaded(): void {
			do_action( 'easy_content_manager_loaded' );
		}

		/**
		 * Initialize the plugin
		 */
		public function init_plugin(): void {
			// Init action.
			do_action( 'easy_content_manager_before_init' );
			$this->load_global_css();
			$this->dispatch_hooks();
			$this->load_addons();
			// Init action.
			do_action( 'easy_content_manager_init' );
		}

		public function dispatch_hooks(): void {
			EasyContentManager\PostType::init();
			EasyContentManager\Features::init();
			EasyContentManager\Shortcode::init();
			EasyContentManager\Assets::init();
			EasyContentManager\Migration::init();
			EasyContentManager\API::init();
			EasyContentManager\Ajax::init();
			if ( is_admin() ) {
				EasyContentManager\Admin::init();
			}
		}

		public function define_constants(): void {
			define( 'EASY_CONTENT_MANAGER', 'easy_content_manager' );
			define( 'EASY_CONTENT_MANAGER_VERSION', '1.2.8' );
			define( 'EASY_CONTENT_MANAGER_DB_VERSION', '1.0' );
			define( 'EASY_CONTENT_MANAGER_SETTINGS_NAME', 'easy_content_manager_settings' );
			define( 'EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME', 'easy_content_manager_addons_settings' );
			define( 'EASY_CONTENT_MANAGER_PLUGIN_FILE', __FILE__ );
			define( 'EASY_CONTENT_MANAGER_ADDONS_DIR_PATH', plugin_dir_path( __FILE__ ) . 'addons/' );
			define( 'EASY_CONTENT_MANAGER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'EASY_CONTENT_MANAGER_PLUGIN_SLUG', 'easy-content-manager' );
			define( 'EASY_CONTENT_MANAGER_PLUGIN_ROOT_URI', plugins_url( '/', __FILE__ ) );
			define( 'EASY_CONTENT_MANAGER_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
			define( 'EASY_CONTENT_MANAGER_INCLUDES_DIR_PATH', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'includes/' );
			define( 'EASY_CONTENT_MANAGER_ASSETS_DIR_PATH', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'assets/' );
			define( 'EASY_CONTENT_MANAGER_ASSETS_URI', EASY_CONTENT_MANAGER_PLUGIN_ROOT_URI . 'assets/' );
			define( 'EASY_CONTENT_MANAGER_TEMPLATE_DEBUG_MODE', false );
			define( 'EASY_CONTENT_MANAGER_BACKEND_SCRIPTS_PRIORITY', 10 );
		}

		public function load_global_css() {
			EasyContentManager\Classes\GlobalCss::init();
		}

		public function load_addons() {
			if(class_exists('EasyContentManager\Addons')){
				EasyContentManager\Addons::init();
			}
		}
	}

	/**
	 * Initializes the main plugin
	 */
	function easy_content_manager_start() {
		return EasyContentManager::init();
	}

	// Plugin Start
	easy_content_manager_start();
}//end if
