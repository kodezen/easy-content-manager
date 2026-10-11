<?php
namespace ECMReactions;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMReactions\Ajax\ReactionHandler;
use ECMReactions\Shortcodes\ReactionsShortcode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Reactions implements AddonInterface {
    private $addon_name = 'reactions';

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
        define( 'ECM_REACTIONS_VERSION', '1.0.0' );
        define( 'ECM_REACTIONS_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_REACTIONS_TEMPLATES_PATH', ECM_REACTIONS_PATH . '../../templates/addons/reactions/' );
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

        // suitable name for adding hooks and functions
        new ReactionsShortcode();
        new ReactionHandler();
        new Ajax();
        new Hooks();
    }
    public function addon_activation_hook() {
        // Create database tables
    }
}