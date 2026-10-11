<?php

namespace ECMFrontendSubmission;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class FrontendSubmission implements AddonInterface {
    private $addon_name = 'frontend-submission';

    private function __construct()
    {
        $this->define_constants();
        $this->init_addon();
    }

    public static function init()
    {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    public function define_constants()
    {
        define( 'ECM_FRONTEND_SUBMISSION_ADDON_PATH', plugin_dir_path( __FILE__ ) );
        define( 'ECM_FRONTEND_SUBMISSION_ADDON_URL', plugin_dir_url( __FILE__ ) );
    }

    public function init_addon()
    {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
        // if disable then stop running addon
        if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
            return;
        }
        new Shortcode();
    }

    public function addon_activation_hook()
    {
        // TODO: Implement addon_activation_hook() method.
    }
}