<?php
namespace ECMWhiteLabel;

use EasyContentManager\Admin\Settings\Base as Settings;
use EasyContentManager\Classes\AbstractAjaxHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax extends AbstractAjaxHandler{

	public function __construct() {
		$this->actions = [
			'save_settings' => [
				'callback' => [ $this, 'save_settings' ],
				'capability' => 'manage_options',
				'fields' => [
					'title' => 'string',
					'active_menu_icon' => 'integer',
					'inactive_menu_icon' => 'integer',
					'logo' => 'integer',
					'is_hide_settings' => 'boolean',
				],
			],
		];
	}

	public function save_settings( $payload ) {
		$title = $payload['title'] ?? '';
		$active_menu_icon = (int) ($payload['active_menu_icon'] ?? 0 );
		$inactive_menu_icon = (int) ($payload['inactive_menu_icon'] ?? 0 );
		$logo = (int) ($payload['logo'] ?? 0 );
		$is_hide_settings = isset( $payload['is_hide_settings'] ) ? (bool) $payload['is_hide_settings'] : false;

		$is_saved = Settings::save_settings(array(
			'title' => $title,
			'active_menu_icon' => $active_menu_icon,
			'inactive_menu_icon' => $inactive_menu_icon,
			'logo' => $logo,
			'is_hide_settings' => $is_hide_settings,
		));

		if ( $is_saved ) {
			$saved_data = Settings::get_saved_data();
			wp_send_json_success( $saved_data );
		}
		wp_send_json_error( __( 'Something went wrong!!', 'easy-content-manager' ) );
	}
}
