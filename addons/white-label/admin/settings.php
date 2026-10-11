<?php
namespace ECMWhiteLabel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Settings {
	public static function get_settings_saved_data() {
		$settings = get_option( ECM_WHITE_LABEL_SETTINGS_NAME );
		if ( $settings ) {
			return json_decode( $settings, true );
		}
		return [];
	}
	public static function get_settings_default_data() {
		return apply_filters('ecm_white_label/admin/settings_default_data', [
			'title' => 'Easy Content Manager',
			'active_menu_icon' => 0,
			'inactive_menu_icon' => 0,
			'logo' => 0,
			'is_hide_settings' => false,
		]);
	}

	public static function save_settings( $form_data = false ) {
		$default_data = self::get_settings_default_data();
		$saved_data = self::get_settings_saved_data();
		$settings_data = wp_parse_args( $saved_data, $default_data );
		if ( $form_data ) {
			$settings_data = wp_parse_args( $form_data, $settings_data );
		}
		// if settings already saved, then update it
		if ( count( $saved_data ) ) {
			return update_option( ECM_WHITE_LABEL_SETTINGS_NAME, wp_json_encode( $settings_data ), false );
		}
		return add_option( ECM_WHITE_LABEL_SETTINGS_NAME, wp_json_encode( $settings_data ), '', false );
	}
}
