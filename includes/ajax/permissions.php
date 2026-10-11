<?php
/**
 * Role → capability management for Settings → Permissions.
 *
 * Grants are written straight to WP_Role objects via
 * {@see Capabilities::set_role_cap()}, so they stay visible to third-party role
 * editors and survive deactivation.
 *
 * @package EasyContentManager
 */

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Permissions extends AbstractAjaxHandler {

	/**
	 * Changing who can do what is itself a privileged act, so it needs more than
	 * `MANAGE_SETTINGS` — otherwise anyone granted settings access could grant
	 * themselves the rest. `promote_users` is the core capability for changing
	 * what other people may do, and it is checked again per request below.
	 *
	 * @var string
	 */
	protected string $default_capability = 'promote_users';

	public function __construct() {
		$this->actions = [
			'get_role_permissions' => [
				'callback' => [ $this, 'get_role_permissions' ],
			],
			'update_role_permission' => [
				'callback' => [ $this, 'update_role_permission' ],
				'fields'   => [
					'role'       => 'string',
					'capability' => 'string',
					'granted'    => 'boolean',
				],
			],
		];
	}

	/**
	 * Only a full administrator may read or rewrite the permission matrix.
	 *
	 * `promote_users` alone is not enough: an editor granted that capability by
	 * another plugin could otherwise hand themselves the whole builder.
	 */
	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'promote_users' ) ) {
			wp_send_json_error(
				[ 'message' => __( 'You do not have permission to manage permissions.', 'easy-content-manager' ) ],
				rest_authorization_required_code()
			);
		}
	}

	public function get_role_permissions() {
		$this->guard();

		wp_send_json_success(
			[
				'capabilities' => array_map(
					static function ( $slug, $meta ) {
						return [
							'key'         => $slug,
							'label'       => $meta['label'],
							'description' => $meta['description'],
						];
					},
					array_keys( Capabilities::all() ),
					Capabilities::all()
				),
				'roles'        => Capabilities::roles_matrix(),
			]
		);
	}

	public function update_role_permission( $payload ) {
		$this->guard();

		$role       = isset( $payload['role'] ) ? sanitize_key( $payload['role'] ) : '';
		$capability = isset( $payload['capability'] ) ? sanitize_key( $payload['capability'] ) : '';
		$granted    = ! empty( $payload['granted'] );

		$result = Capabilities::set_role_cap( $role, $capability, $granted );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
		}

		wp_send_json_success( [ 'roles' => Capabilities::roles_matrix() ] );
	}
}
