<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_SETTINGS;


	public function __construct() {
		$this->actions = [
			'create_settings' => [
				'callback' => [ $this, 'create_settings' ],
				'fields' => [
					'label' => 'string',
					'slug' => 'string',
					'icon' => 'string',
					'admin_menu_parent' => 'string',
					'option_name' => 'string',
					'menu_position' => 'integer',
					'routes' => 'array|string',
				],
			],
			'update_settings' => [
				'callback' => [ $this, 'update_settings' ],
				'fields' => [
					'id' => 'integer',
					'label' => 'string',
					'slug' => 'string',
					'icon' => 'string',
					'admin_menu_parent' => 'string',
					'option_name' => 'string',
					'menu_position' => 'integer',
					'routes' => 'array|string',
				],
			],
			'delete_settings' => [
				'callback' => [ $this, 'delete_settings' ],
				'fields' => [
					'id' => 'integer',
					'force' => 'boolean',
				],
			],
			'get_settings' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_settings' ],
			],
			'get_settings_by_id' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_settings_by_id' ],
				'fields' => [
					'id' => 'integer',
				],
			],
			'save_settings_data' => [
				'callback' => [ $this, 'save_settings_data' ],
				'fields' => [
					'id' => 'integer',
					'option_name' => 'string',
					'route' => 'string',
					'meta' => 'array|string',
				],
			],
			'get_saved_settings' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_saved_settings' ],
				'fields' => [
					'option_name' => 'string',
					'route' => 'string',
				],
			],
		];
	}

	public function create_settings( $payload ) {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;

		if ( empty( $payload['slug'] ) ) {
			wp_send_json_error([
				'message' => __( 'The "slug" field is required.', 'easy-content-manager' ),
				'field'   => 'slug',
			], 400);
		}

		$is_locked = apply_filters( 'ecm_settings_validation_before_create', false, $payload );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Settings creation was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$settingsData = wp_parse_args( $payload, $this->default_data() );

		$title = '' !== $payload['label'] ? $payload['label'] : 'Settings';

		$insert_data = [
			'post_type'    => $postType,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => sanitize_title( $payload['slug'] ),
			'post_content' => maybe_serialize( $settingsData ),
		];

		$postId = wp_insert_post( $insert_data, true );

		if ( is_wp_error( $postId ) ) {
			wp_send_json_error( [ 'message' => $postId->get_error_message() ], 500 );
		}

		wp_send_json_success([
			'id' => $postId,
			'post_content' => $settingsData
		]);
	}

	public function update_settings( $payload ) {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;
		$postId   = $payload['id'];

		// Without this the ID is unbounded: any post — a page, a product, another
		// plugin's CPT — would have its title, slug and content overwritten with
		// the serialized settings blob. `create` and `delete` guard the same way.
		if ( get_post_type( $postId ) !== $postType ) {
			wp_send_json_error( [ 'message' => __( 'Invalid settings ID.', 'easy-content-manager' ) ], 400 );
		}

		$is_locked = apply_filters( 'ecm_settings_validation_before_update', false, $payload, get_post( $postId ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Settings update was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$updatedData = wp_parse_args( $payload, $this->default_data() );

		$title = '' !== $payload['label'] ? $payload['label'] : get_the_title( $postId );

		$updated_data = [
			'ID'           => $postId,
			'post_title'   => $title,
			'post_name'    => sanitize_title( $payload['slug'] ),
			'post_content' => maybe_serialize( $updatedData ),
		];

		$result = wp_update_post( $updated_data, true );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ], 500 );
		}

		$updated_data['post_content'] = $updatedData;

		wp_send_json_success([
			'id' => $postId,
			'post_content' => $updatedData
		]);
	}

	public function delete_settings( $payload ) {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;
		$postId   = $payload['id'];

		$is_locked = apply_filters( 'ecm_settings_validation_before_delete', false, $payload, get_post( $postId ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Settings deletion was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		if ( get_post_type( $postId ) !== $postType ) {
			wp_send_json_error( [ 'message' => __( 'Invalid settings ID.', 'easy-content-manager' ) ], 400 );
		}

		$deleted = ! empty( $payload['force'] )
			? wp_delete_post( $postId, true )
			: wp_trash_post( $postId );

		if ( ! $deleted ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete the settings item.', 'easy-content-manager' ) ], 500 );
		}

		wp_send_json_success([
			'message' => ! empty( $payload['force'] )
				? __( 'Settings permanently deleted.', 'easy-content-manager' )
				: __( 'Settings moved to trash.', 'easy-content-manager' ),
		]);
	}

	public function get_settings() {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;

		$posts = get_posts([
			'post_type'      => $postType,
			'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
			'posts_per_page' => -1,
			'orderby'        => [
				'menu_order' => 'ASC',
				'title' => 'ASC'
			],
			'order'          => 'ASC'
		]);

		$items = array_map(function ( $post ) {
			return [
				'id'              => $post->ID,
				'post_content'    => maybe_unserialize( $post->post_content ) ?? [],
			];
		}, $posts);

		wp_send_json_success([
			'items' => $items,
			'count' => count( $items ),
		]);
	}

	public function get_settings_by_id( $payload ) {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;
		$postId   = $payload['id'];

		if ( get_post_type( $postId ) !== $postType ) {
			wp_send_json_error( [ 'message' => __( 'Invalid settings ID.', 'easy-content-manager' ) ], 400 );
		}

		$post = get_post( $postId );
		$post->post_content = maybe_unserialize( $post->post_content ) ?? [];

		wp_send_json_success([
			'id'              => $post->ID,
			'post_content'    => $post->post_content,
		]);
	}

	public function save_settings_data( $payload ) {
		$postType = Helper::ECM_SETTINGS_POST_TYPE_NAME;
		$postId   = $payload['id'];

		if ( get_post_type( $postId ) !== $postType ) {
			wp_send_json_error( [ 'message' => __( 'Invalid settings ID.', 'easy-content-manager' ) ], 400 );
		}

		$option_name = $payload['option_name'];

		if ( empty( $option_name ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid option name.', 'easy-content-manager' ) ], 400 );
		}

		$route = $payload['route'];

		if ( empty( $route ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid route.', 'easy-content-manager' ) ], 400 );
		}

		$existing_data = maybe_unserialize( get_option( $option_name, [] ) ) ?? [];
		$existing_data[ $route ] = $payload['meta'];

		$saved = update_option( $option_name, maybe_serialize( $existing_data ) );

		if ( $saved ) {
			wp_send_json_success( $payload['meta'] );
		}

		wp_send_json_error( [ 'message' => __( 'Failed to save settings data.', 'easy-content-manager' ) ], 500 );
	}

	public function get_saved_settings( $payload ) {
		$option_name = $payload['option_name'];

		if ( empty( $option_name ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid option name.', 'easy-content-manager' ) ], 400 );
		}

		$route = $payload['route'];

		if ( empty( $route ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid route.', 'easy-content-manager' ) ], 400 );
		}

		$saved_data = get_option( $option_name, [] );

		$saved_data = maybe_unserialize( $saved_data );

		wp_send_json_success( $saved_data[ $route ] ?? (object) [] );
	}

	private function default_data() {
		return [
			'label'            => '',
			'slug'             => '',
			'icon'             => '',
			'admin_menu_parent' => '',
			'menu_position'    => 0,
			'option_name'      => '',
			'routes'           => [],
		];
	}
}
