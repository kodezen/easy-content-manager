<?php
namespace ECMBookmark;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMBookmark\Ajax\ToggleBookmark;
use ECMBookmark\Shortcodes\BookmarkShortcode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Bookmark implements AddonInterface {
    private $addon_name = 'bookmark';

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
        define( 'ECM_BOOKMARK_VERSION', '1.0.0' );
        define( 'ECM_BOOKMARK_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_BOOKMARK_TEMPLATES_PATH', EASY_CONTENT_MANAGER_ROOT_DIR_PATH . 'templates/addons/bookmark/' );
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
        // if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

        new Ajax();
        new BookmarkShortcode();
    }

    public function addon_activation_hook() {
        
    }
}
