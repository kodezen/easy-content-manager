<?php
namespace ECMUpvote;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMUpvote\Ajax\UpvoteHandler;
use ECMUpvote\Shortcodes\UpvoteShortcode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Upvote implements AddonInterface {
    private $addon_name = 'upvote';

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
        define( 'ECM_UPVOTE_VERSION', '1.0.0' );
        define( 'ECM_UPVOTE_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_UPVOTE_TEMPLATES_PATH', ECM_UPVOTE_PATH . '../../templates/addons/upvote/' );
        define( 'ECM_UPVOTE_COOKIE_NAME', 'ecm_upvoted_posts' );
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

        // suitable name for adding hooks and functions
        new UpvoteShortcode();
        new UpvoteHandler();
        new Ajax();
        new Hooks();
    }
    public function addon_activation_hook() {
        // Create database tables
    }
}