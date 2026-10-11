<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addons extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_SETTINGS;

	public function __construct() {
		$this->actions = [
			// Which add-ons are on drives what the whole app renders, so anyone who
			// can open the app needs to read it. Toggling them stays privileged.
			'get_all_addons' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_all_addons' ],
				'fields' => [],
			],
			'saved_addon_status' => [
				'callback' => [ $this, 'saved_addon_status' ],
				'fields' => [
					'addons' => 'array|string',
				],
			],
		];
	}

	public function get_all_addons() {
		// Authorization is handled by the dispatcher against this action's
		// declared capability; a second, stricter inline check here would lock
		// out the very users the capability is meant to admit.
		$ecm_addons = json_decode( get_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME, '{}' ) );
		wp_send_json_success( $ecm_addons );
	}

	public function saved_addon_status( $payload ) {
		$addons = $payload['addons'] ?? [];

		foreach ( $addons as $addon ) {
			$addon_name = $addon['addon_name'] ?? '';
			$addon_slug = $addon['addon_slug'] ?? '';
			$status = isset( $addon['status'] ) ? (bool) $addon['status'] : false;

			if ( empty( $addon_slug ) ) {
				wp_send_json_error( __( 'Addon Name missing', 'easy-content-manager' ) );
			}

			if ( $status ) {
				$required_plugin = $addon['required_plugin'] ?? '';
				do_action( 'easy_content_manager/before_active_addon', $addon_slug, $required_plugin );
				if ( $required_plugin && is_array( $required_plugin ) ) {
					foreach ( $required_plugin as $plugin ) {
						if ( ! Helper::is_plugin_active( sanitize_text_field( $plugin['plugin_dir_path'] ) ) ) {
							$error_message = sprintf( '%s Plugin is required to activate %s addon.', sanitize_text_field( $plugin['plugin_name'] ), $addon_name );
							wp_send_json_error( $error_message );
						}
					}
				}
			}

			$saved_addons = (array) json_decode( get_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME ), true );
			$saved_addons[ $addon_slug ] = $status;
			update_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME, wp_json_encode( $saved_addons ) );
			// Fire Addon Action
			if ( $status ) {
				do_action( "easy-content-manager/addons/activated_{$addon_slug}", $status );
			} else {
				do_action( "easy-content-manager/addons/deactivated_{$addon_slug}", $status );
			}
		}//end foreach

		// response
		wp_send_json_success( $saved_addons );
	}

}
