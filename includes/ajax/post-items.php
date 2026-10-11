<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Classes\Sanitizer;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PostItems extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::VIEW_APP;





	public function __construct() {
		$this->actions = [
			'duplicate_post_item' => [
				'callback' => [ $this, 'duplicate_post_item' ],
				'fields' => [
					'post_item_id' => 'integer',
					'meta' => 'array|string',
				]
			],
			'get_post_item_slug' => array(
				'callback' => array( $this, 'get_post_item_slug' ),
				'fields' => [
					'ID' => 'integer',
					'new_title' => 'string',
					'new_slug' => 'string',
				]
			),
			'get_post_item_count' => [
				'callback' => [ $this, 'get_post_item_count' ],
				'fields' => [
					'post_slug' => 'string',
				]
			],
		];
	}

	public function duplicate_post_item( $payload ) {
		$post_item_id = isset( $payload['post_item_id'] ) ? intval( $payload['post_item_id'] ) : 0;

		if ( ! $post_item_id ) {
			wp_send_json_error( __( 'Post Item ID is required.', 'easy-content-manager' ) );
		}

		$original_post = get_post( $post_item_id );

		if ( ! $original_post ) {
			wp_send_json_error( __( 'Original post not found.', 'easy-content-manager' ) );
		}

		// Content access belongs to the post type, not to an ECM capability: the
		// user must be able to read the original and to create entries of that
		// type. `VIEW_APP` alone only gets them as far as this handler.
		$post_type_object = get_post_type_object( $original_post->post_type );

		if ( ! $post_type_object
			|| ! current_user_can( 'read_post', $post_item_id )
			|| ! current_user_can( $post_type_object->cap->create_posts ) ) {
			wp_send_json_error( __( 'You do not have permission to duplicate this item.', 'easy-content-manager' ) );
		}

		$duplicate_post = [
			'post_author'           => get_current_user_id(),
			'post_status'           => 'draft',
			'post_title'            => $original_post->post_title . ' (copy)',
			'post_name'             => wp_unique_post_slug( sanitize_title( $original_post->post_name . '-copy' ), 0, 'draft', $original_post->post_type, 0 ),
			'post_content'          => $original_post->post_content,
			'excerpt'               => $original_post->post_excerpt,
			'post_type'             => $original_post->post_type,
			'comment_status'        => $original_post->comment_status,
			'ping_status'           => $original_post->ping_status,
			'post_password'         => $original_post->post_password,
			'post_content_filtered' => $original_post->post_content_filtered,
			'menu_order'            => $original_post->menu_order,
			'post_mime_type'        => $original_post->post_mime_type,
			'to_ping'               => $original_post->to_ping,
			'pinged'                => $original_post->pinged,
		];

		$duplicate_id = wp_insert_post( $duplicate_post );

		if ( ! $duplicate_id ) {
			wp_send_json_error( __( 'Failed to duplicate post.', 'easy-content-manager' ) );
		}

		$post_meta = get_post_meta( $post_item_id );
		if ( ! empty( $post_meta ) ) {
			$exclude_meta_keys = apply_filters( 'easy-content-manager/duplicate_post/exclude_meta_keys', [
				'_wp_old_slug',
				'_wp_old_date',
				'_edit_lock',
				'_edit_last',
			] );

			foreach ( $post_meta as $meta_key => $meta_values ) {
				if ( in_array( $meta_key, $exclude_meta_keys, true ) ) {
					continue;
				}

				foreach ( $meta_values as $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
					add_post_meta( $duplicate_id, $meta_key, $meta_value );
				}
			}
		}

		$taxonomies = get_object_taxonomies( $original_post->post_type, 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_item_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				wp_set_post_terms( $duplicate_id, $terms, $taxonomy );
			}
		}

		$duplicated_post_data = get_post( $duplicate_id );
		$duplicated_post_data->meta = get_post_meta( $duplicate_id );
		$duplicated_post_data->taxonomies = [];
		foreach ( $taxonomies as $taxonomy ) {
			$duplicated_post_data->taxonomies[ $taxonomy ] = wp_get_post_terms( $duplicate_id, $taxonomy, [ 'fields' => 'names' ] );
		}
		$duplicated_post_data->featured_media = get_post_thumbnail_id( $duplicate_id );

		wp_send_json_success( $duplicated_post_data );
	}

	public function get_post_item_slug( $payload ) {
		wp_send_json_success( Helper::get_sample_permalink_args( $payload['ID'], $payload['new_title'], $payload['new_slug'] ?? '' ) );
	}

	public function get_post_item_count( $payload ) {
		$post_type = $payload['post_slug'];
		if ( empty( $post_type ) ) {
			wp_send_json_error( 'post_slug is required' );
		}
		$post_counts = wp_count_posts( $post_type );

		$data = [
			'total' => array_sum( (array) $post_counts ),
			'publish' => (int) ( $post_counts->publish ?? 0 ),
			'draft' => (int) ( $post_counts->draft ?? 0 ),
			'pending' => (int) ( $post_counts->pending ?? 0 ),
			'trash' => (int) ( $post_counts->trash ?? 0 ),
		];
		wp_send_json_success( $data );
	}
}
