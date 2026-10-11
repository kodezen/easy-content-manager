<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class FieldGroup extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_FIELD_GROUPS;




	public function __construct() {
		$this->actions = [
			'get_field_groups' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_field_groups' ],
				'fields' => [
					'group_id' => 'integer',
					'group_name' => 'string',
				]
			],
			'get_all_field_groups' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_all_field_groups' ],
				'fields' => [
					'per_page' => 'integer',
					'page' => 'integer',
					'search' => 'string',
					'status' => 'string'
				]
			],
			'get_all_field_groups_count' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_all_field_groups_count' ],
			],
			'create_field_group' => [
				'callback' => [ $this, 'create_field_group' ],
				'fields' => [
					'group_name' => 'string',
					'location' => 'array|string',
					'custom_fields' => 'array|string',
					'slug' => 'string',
				]
			],
			'duplicate_field_group'  => [
				'callback' => [ $this, 'duplicate_field_group' ],
				'fields' => [
					'group_id' => 'integer',
				],
			],

			'update_field_group'  => [
				'callback' => [ $this, 'update_field_group' ],
				'fields' => [
					'group_id' => 'integer',
					'group_name' => 'string',
					'location' => 'array|string',
					'custom_fields' => 'array|string',
					'slug' => 'string',
					'status' => 'string',
				]
			],
			'delete_field_group'  => [
				'callback' => [ $this, 'delete_field_group' ],
				'fields' => [
					'group_id' => 'integer',
					'force' => 'boolean',
				],
			]
		];
	}

	public function duplicate_field_group( $payload ) {
		$original_post = get_post( $payload['group_id'] );

		if ( ! $original_post ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Group ID.', 'easy-content-manager' ) ] );
		}

		$original_content = maybe_unserialize( $original_post->post_content );

		$new_title = $original_post->post_title . ' (Copy)';
		$new_slug  = $original_content['post_slug'] . '-copy';

		$counter = 1;
		while ( get_page_by_title( $new_title, OBJECT, Helper::ECM_FIELD_GROUP_POST_TYPE_NAME ) ) {
			$counter++;
			$new_title = $original_post->post_title . " (Copy {$counter})";
		}

		$new_content = [
			'location' => $original_content['location'] ?? [],
			'custom_fields' => $original_content['custom_fields'] ?? [],
			'post_slug' => sanitize_text_field( $new_slug ) ?? '',
		];

		$new_post_id = wp_insert_post([
			'post_title'   => sanitize_text_field( $new_title ),
			'post_content' => maybe_serialize( $new_content ),
			'post_status'  => 'draft',
			'post_type'    => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
		]);

		if ( is_wp_error( $new_post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to duplicate field group.', 'easy-content-manager' ),
				'error'   => $new_post_id->get_error_message()
			]);
		}

		$post_types_from_location = Helper::get_post_types_from_location_array( $new_content['location'] );
		update_post_meta( $new_post_id, 'ecm_field_group_location', maybe_serialize( $post_types_from_location ) );

		$new_post = get_post( $new_post_id );

		wp_send_json_success([
			'ID' => $new_post->ID,
			'post_title' => $new_post->post_title,
			'post_content' => maybe_unserialize( $new_post->post_content ),
			'post_status' => $new_post->post_status,
		]);
	}

	public function get_all_field_groups( $payload ) {
		if ( empty( $payload['per_page'] ) || empty( $payload['page'] ) ) {
			wp_send_json_error( [ 'message' => __( 'page and per_page fields are required', 'easy-content-manager' ) ] );
		}
		$args = [
			'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'posts_per_page' => isset( $payload['per_page'] ) ? (int) $payload['per_page'] : 10,
			'paged'          => isset( $payload['page'] ) ? (int) $payload['page'] : 1,
			's'              => isset( $payload['search'] ) ? sanitize_text_field( $payload['search'] ) : '',
		];

		if ( ! empty( $payload['status'] ) ) {
			$args['post_status'] = $payload['status'] ? [ $payload['status'] ] : [ 'publish', 'draft' ];
		}

		$query = new \WP_Query( $args );

		$total_items = $query->found_posts;
		$posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$content = maybe_unserialize( get_the_content() );
				$posts[] = [
					'id'    => get_the_ID(),
					'title' => get_the_title(),
					'link'  => get_permalink(),
					'date'  => get_the_date(),
					'total_fields' => count( $content['custom_fields'] ),
					'custom_fields' => $content['custom_fields'],
					'location' => $content['location'],
					'post_slug' => $content['post_slug'] ?? '',
					'status' => get_post_status()
				];
			}
			wp_reset_postdata();
		}

		header( 'X-WP-Total: ' . $total_items );
		return $posts;
	}

	public function get_all_field_groups_count( $payload ) {
		$args = [
			'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
		];

		$query = new \WP_Query( $args );

		$total_items = $query->found_posts;
		$posts = [];

		return $total_items;
	}

	public function create_field_group( $payload ) {
		if ( empty( $payload['group_name'] ) || empty( $payload['location'] ) || empty( $payload['custom_fields'] ) || empty( $payload['slug'] ) ) {
			status_header( 400 );
			wp_send_json_error( [ 'message' => __( 'All fields are required.', 'easy-content-manager' ) ] );
		}

		$is_locked = apply_filters( 'ecm_field_group_validation_before_create', false, $payload );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Field group creation was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$existing_post = get_page_by_title( sanitize_text_field( $payload['group_name'] ), OBJECT, Helper::ECM_FIELD_GROUP_POST_TYPE_NAME );
		$args = [
			'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'title'          => sanitize_text_field( $payload['group_name'] ),
			'post_status'    => 'any',
			'posts_per_page' => 1,
		];

		$posts = get_posts( $args );

		$existing_post = ! empty( $posts ) ? $posts[0] : null;

		if ( $existing_post ) {
			status_header( 409 );
			wp_send_json_error([
				'message' => __( 'Group name already exists. Please choose a different name.', 'easy-content-manager' ),
				'status' => 409
			]);
		}

		$post_content = [
			'location' => $payload['location'],
			'custom_fields' => $payload['custom_fields'],
			'post_slug' => $payload['slug'],
		];

		$args = [
			'post_title' => sanitize_text_field( $payload['group_name'] ),
			'post_content' => maybe_serialize( $post_content ),
			'post_status' => 'publish',
			'post_type' => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
		];

		$post_id = wp_insert_post( $args );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to create post.', 'easy-content-manager' ),
				'error' => $post_id->get_error_message()
			]);

			return;
		}

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );

		$post_data = get_post( $post_id );

		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		$field_group_location = Helper::get_post_types_from_location_array( $post_content['location'] );

		update_post_meta( $post_data->ID, 'ecm_field_group_location', maybe_serialize( $field_group_location ) );

		wp_send_json_success([
			'ID' => $post_data->ID,
			'post_title' => $post_data->post_title,
			'post_content' => $post_data->post_content,
			'post_status' => $post_data->post_status,
		]);
	}

	public function get_field_groups( $payload ) {
		if ( ! empty( $payload['group_name'] ) ) {
			$args = [
				'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
				'title'          => $payload['group_name'],
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			];

			$query = new \WP_Query( $args );
			$post = $query->have_posts() ? $query->next_post() : null;
		} else {
			$post = get_post( $payload['group_id'] );
		}

		if ( ! $post ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Group ID.', 'easy-content-manager' ) ] );
		}

		$content = maybe_unserialize( $post->post_content );

		$response = [
			'id'            => $post->ID,
			'title'         => $post->post_title,
			'custom_fields' => isset( $content['custom_fields'] ) ? $content['custom_fields'] : [],
			'location'      => isset( $content['location'] ) ? $content['location'] : '',
			'slug'      => isset( $content['post_slug'] ) ? $content['post_slug'] : '',
		];

		wp_send_json_success( $response );
	}

	public function update_field_group( $payload ) {
		$post = get_post( $payload['group_id'] );

		if ( ! $post || Helper::ECM_FIELD_GROUP_POST_TYPE_NAME !== $post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Group ID.', 'easy-content-manager' ) ] );
		}

		$is_locked = apply_filters( 'ecm_field_group_validation_before_update', false, $payload, $post );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Field group update was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$post_data = [
			'ID' => $post->ID,
			'post_title' => sanitize_text_field( $payload['group_name'] ),
			'post_content' => maybe_serialize([
				'location' => $payload['location'],
				'custom_fields' => $payload['custom_fields'],
				'post_slug' => $payload['slug'],
			]),
		];

		if ( isset( $payload['status'] ) && in_array( $payload['status'], [ 'publish', 'draft', 'trash' ], true ) ) {
			$post_data['post_status'] = $payload['status'];
		}

		$post_id = wp_update_post( $post_data );
		$post_types_from_location = Helper::get_post_types_from_location_array( $payload['location'] );

		update_post_meta( $post_id, 'ecm_field_group_location', maybe_serialize( $post_types_from_location ) );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to update post.', 'easy-content-manager' ),
				'error' => $post_id->get_error_message()
			]);

			return;
		}

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );

		$post_data = get_post( $post_id );
		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success( $post_data );
	}

	public function delete_field_group( $payload ) {
		$post = get_post( $payload['group_id'] );

		$is_locked = apply_filters( 'ecm_field_group_validation_before_delete', false, $payload, $post );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Field group deletion was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		if ( ! $post || Helper::ECM_FIELD_GROUP_POST_TYPE_NAME !== $post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Group ID.', 'easy-content-manager' ) ] );
		}

		$force_delete = isset( $payload['force'] ) ? filter_var( $payload['force'], FILTER_VALIDATE_BOOLEAN ) : false;

		$deleted = wp_delete_post( $payload['group_id'], $force_delete );

		if ( ! $deleted ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete post.', 'easy-content-manager' ) ] );
		}

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );

		wp_send_json_success([
			'message' => __( 'Post deleted successfully.', 'easy-content-manager' ),
			'id' => $payload['group_id']
		]);
	}
}
