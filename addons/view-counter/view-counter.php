<?php
namespace ECMViewCounter;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMViewCounter\Handlers\ViewHandler;
use ECMViewCounter\Shortcodes\ViewShortcode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ViewCounter implements AddonInterface {
    private $addon_name = 'view-counter';

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
        define( 'ECM_VIEW_COUNTER_VERSION', '1.0.0' );
        define( 'ECM_VIEW_COUNTER_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_VIEW_COUNTER_TEMPLATES_PATH', ECM_VIEW_COUNTER_PATH . '../../templates/addons/view-counter/' );
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}
        
        new ViewShortcode();
    }

    public function addon_activation_hook() {
        // Create database tables
    }
}