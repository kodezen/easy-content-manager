<?php
namespace ECMClaim;

use EasyContentManager\Interfaces\AddonInterface;
use EasyContentManager\Utils\Helper;
use ECMClaim\Admin\Menu;
use ECMClaim\Ajax\ClaimAjax;
use ECMClaim\Shortcodes\ClaimShortcode;
use ECMClaim\Api\ClaimApi;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Claim implements AddonInterface {
    private $addon_name = 'claim';

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
        define( 'ECM_CLAIM_VERSION', '1.0.0' );
        define( 'ECM_CLAIM_PATH', dirname( __FILE__ ) . '/' );
        define( 'ECM_CLAIM_TEMPLATES_PATH', ECM_CLAIM_PATH . '../../templates/addons/claim/' );
        define( 'ECM_CLAIM_TABLE', 'ecm_claims' ); 
    }

    public function init_addon() {
        add_action( "easy-content-manager/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
        
		// if disable then stop running addon
		if ( ! Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

        new Menu();
        new ClaimShortcode();
        new ClaimAjax();
        new ClaimApi();
    }

    public function addon_activation_hook() {
        Installer::init();
        flush_rewrite_rules();
    }
}
