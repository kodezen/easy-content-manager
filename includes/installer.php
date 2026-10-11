<?php

namespace EasyContentManager;

use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;
use EasyContentManager\Admin\Settings\base as Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Installer {
	public $ecm_version;

	public static function init() {
		$self = new self();
		$self->ecm_version = get_option( 'easy_content_manager_version' );
		$self->save_main_settings();
		$self->save_options();
		Capabilities::install();
	}

	public function save_options() {
		if ( ! $this->ecm_version ) {
			add_option( 'easy_content_manager_version', EASY_CONTENT_MANAGER_VERSION );
		}
		if ( ! get_option( 'easy_content_manager_db_version' ) ) {
			add_option( 'easy_content_manager_db_version', EASY_CONTENT_MANAGER_DB_VERSION );
		}
		if ( ! get_option( 'easy_content_manager_first_install_time' ) ) {
			add_option( 'easy_content_manager_first_install_time', Helper::get_time() );
		}
		update_option( 'easy_content_manager_required_rewrite_flush', Helper::get_time() );
	}

	public function save_main_settings() {
		Settings::save_settings();
	}
}
