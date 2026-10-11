<?php

namespace EasyContentManager\Admin;

use RuntimeException;
use SE_License_SDK_Client;
use SE_License_SDK_License;
use stdClass;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SeLicense {
	private static ?SeLicense $instance = null;

	private static ?SE_License_SDK_Client $sdk_client = null;

	public static function get_instance(): SeLicense {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		// The StoreEngine licence SDK is direct-required in load_dependency() so
		// this copy always registers its version in the SDK's election. This is a
		// belt-and-braces fallback for the case where SeLicense is reached without
		// the plugin bootstrap having run (e.g. a unit test loading the class
		// directly).
		if ( ! function_exists( 'se_license_init' ) ) {
			$license_sdk = EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'vendor/storeengine/wordpress-sdk/init.php';

			if ( is_readable( $license_sdk ) ) {
				require_once $license_sdk;
			}
		}

		if ( ! did_action( 'plugins_loaded' ) ) {
			add_action( 'plugins_loaded', [ $this, 'get_sdk' ] );
		} else {
			$this->get_sdk();
		}
	}

	/**
	 * Whether the licence SDK is loadable at all.
	 *
	 * `vendor/` is gitignored, so a build or deploy that ships without it leaves
	 * `se_license_init()` undefined. Calling it regardless turned every is_pro()
	 * call site — the AJAX dispatcher, the admin menu, the localize payload —
	 * into a fatal.
	 */
	public static function is_available(): bool {
		return function_exists( 'se_license_init' );
	}

	public function get_sdk(): ?SE_License_SDK_Client {
		if ( ! self::is_available() ) {
			return null;
		}

		if ( null === self::$sdk_client ) {
			self::$sdk_client = se_license_init( [
				'package_file'        => EASY_CONTENT_MANAGER_PLUGIN_FILE,
				'package_name'        => 'Easy Content Manager (ECM) Pro', // translation remove due to load too early
				'product_id'          => 202,
				'is_free'             => false,
				'use_update'          => true,
				'slug'                => 'easy-content-manager-pro',
				'basename'            => EASY_CONTENT_MANAGER_PLUGIN_BASENAME,
				'package_type'        => 'plugin',
				'package_version'     => EASY_CONTENT_MANAGER_VERSION,
				// Bootstrap files this plugin hard-requires on boot. The SDK checks
				// them in the extracted update package before WordPress swaps the
				// live folder, so a truncated or malformed download is rejected
				// rather than leaving the plugin half-installed and fataling on
				// every request. Declaring them explicitly also pins the behaviour
				// instead of relying on the SDK's inferred default.
				'critical_paths'      => [
					'vendor/autoload.php',
					'vendor/storeengine/wordpress-sdk/init.php',
					'includes/autoload.php',
				],
				'allow_local'         => true,
				'menu'                => admin_url( 'admin.php?page=easy-content-manager-settings&path=manage_pro' ),
				'init_restapi'        => true,
				'script_handler'      => 'easy-content-manager-admin-scripts',
				'license_server'      => 'https://store.kodezen.com/',
				'purchase_url'        => 'https://easycontentmanager.pro/',
				'product_logo'        => defined( 'EASY_CONTENT_MANAGER_ASSETS_URI' ) ? EASY_CONTENT_MANAGER_ASSETS_URI . 'images/logo.svg' : '',
				'store_dashboard_url' => 'https://store.kodezen.com/dashboard/license-keys/',
				'terms_url'           => 'https://kodezen.com/terms-and-conditions/',
				'privacy_policy_url'  => 'https://store.kodezen.com/privacy-policy/',
				'ticket_recipient'    => 'support@kodezen.com',
				'primary_color'       => '#006BFF',
				'first_install_time'  => get_option( 'easy_content_manager_first_install_time' ),
				'optin_notice_delay'  => 3 * DAY_IN_SECONDS,
			] );
		}

		return self::$sdk_client;
	}

	public static function get_license(): SE_License_SDK_License {
		if ( ! self::$sdk_client ) {
			throw new RuntimeException( 'Trying to get license data before SDK initialization.' );
		}

		return self::$sdk_client->license();
	}

	/**
	 * Whether a valid Pro licence is active.
	 *
	 * Never throws: this gates menu registration, AJAX dispatch and the localize
	 * payload, so an uninitialised or missing SDK must degrade to "not pro"
	 * rather than take the site down.
	 */
	public static function is_pro(): bool {
		if ( ! self::$sdk_client ) {
			return false;
		}

		try {
			return (bool) self::get_license()->is_valid();
		} catch ( \Throwable $e ) {
			return false;
		}
	}
}
