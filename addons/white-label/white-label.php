<?php
namespace ECMWhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use EasyContentManager\Interfaces\AddonInterface;

final class WhiteLabel implements AddonInterface {
	private $addon_name = 'white-label';
	private function __construct() {
		$this->define_constants();
		$this->init_addon();
	}
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
	public function define_constants() {
		/**
		 * Defines CONSTANTS for Whole Addon.
		 */
		define( 'ECM_WHITE_LABEL_VERSION', '1.0' );
		define( 'ECM_WHITE_LABEL_SETTINGS_NAME', 'ecm_white_label_settings' );
	}
	public function init_addon() {
		// // fire addon activation hook
		// add_action( "easy_content_manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// // if disable then stop running addon
		// if ( ! \EasyContentManager\Utils\Helper::get_addon_active_status( $this->addon_name ) ) {
		// 	return;
		// }

		// Run Addon functionality
		new Ajax();
		new Hooks();
	}

	public function addon_activation_hook() {
		Admin\Settings::save_settings();
	}
}
