<?php

namespace EasyContentManager\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EasyContentManager\Admin\Settings\Base as BaseSettings;
use EasyContentManager\Classes\Capabilities;
use WP_REST_Controller;
use WP_REST_Server;

class Settings extends WP_REST_Controller {


	public static function init() {
		$self            = new self();
		$self->namespace = EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/v1';
		$self->rest_base = 'settings';
		add_action( 'rest_api_init', array( $self, 'register_routes' ) );
		add_action( 'easy_content_manager/admin/after_save_settings', array( $self, 'after_save_settings' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$endpoint = '/settings/';

		register_rest_route(
			$this->namespace,
			$endpoint,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

	public function get_items( $request ) {
		$settings = BaseSettings::get_saved_data();

		$response = apply_filters( 'easy_content_manager/api/settings/get_settings', $settings );

		return rest_ensure_response( $response );
	}

	public function get_items_permissions_check( $request ) {
		return Capabilities::user_can( Capabilities::VIEW_APP );
	}

	public function after_save_settings( $is_update ) {
		// Intentionally empty. This previously wrote
		// `easy_content_manager_flash_role_management`, an option nothing ever
		// read — a leftover of an abandoned role feature, now superseded by
		// EasyContentManager\Classes\Capabilities.
	}
}
