<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use WP_Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::VIEW_APP;



	public function __construct() {
		$this->actions = [
			'get_updated_menu' => [
				'callback' => [ $this, 'get_updated_menu' ],
			],
			'get_user_roles' => [
				'callback' => [ $this, 'get_user_roles' ],
			],
			'get_user_by_roles' => [
				'callback' => [ $this, 'get_user_by_roles' ],
				'fields' => [
					'roles' => 'array|string'
				]
			],
		];
	}

	public function get_updated_menu( $payload ) {
		$menu = \EasyContentManager\Utils\Helper::get_allowed_admin_menu_list();
		wp_send_json_success( $menu );
	}

	public function get_user_roles( $payload ) {
		global $wp_roles;

		if ( empty( $wp_roles ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_roles = new WP_Roles();
		}

		$roles = [];
		foreach ( $wp_roles->roles as $key => $role ) {
			$roles[] = [
				'key'   => $key,
				'name'  => $role['name'],
				'caps'  => $role['capabilities']
			];
		}

		wp_send_json_success( $roles );
	}

	public function get_user_by_roles( $payload ) {
		$roles = $payload['roles'] ?? [];
		$users = [];

		if ( ! empty( $roles ) ) {
			$args = [
				'role__in' => $roles,
				'fields'   => [ 'ID', 'display_name', 'user_email' ]
			];
			$user_query = new \WP_User_Query( $args );
			$users = $user_query->get_results();
		}

		wp_send_json_success( $users );
	}
}
