<?php
namespace ECMReviewAndRatings;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMReviewAndRatings\Admin\Api\ReviewAdminApi;
use ECMReviewAndRatings\Admin\Menu;
use ECMReviewAndRatings\Admin\ReviewDisplay;
use ECMReviewAndRatings\Post\ReviewSubmission;
use ECMReviewAndRatings\Shortcodes\EcmShortcode;
use ECMReviewAndRatings\Shortcodes\ReviewShortcode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ReviewAndRatings implements AddonInterface {
    private $addon_name = 'review-and-ratings';

    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
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
        define( 'ECM_REVIEWS_VERSION', '1.0.0' );
        define( 'ECM_REVIEWS_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_REVIEWS_TEMPLATES_PATH', ECM_REVIEWS_PATH . '../../templates/addons/review-and-ratings/' );
        define( 'ECM_REVIEWS_TYPE', 'ecm_review' );
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}
        new Menu();
        new ReviewShortcode();
        new ReviewSubmission();
        new ReviewDisplay();
        new ReviewAdminApi();
    }

    public function load_dependencies() {
        require_once ECM_REVIEWS_PATH . 'hooks.php';
    }

    public function addon_activation_hook() {
        // Create database tables
    }
}