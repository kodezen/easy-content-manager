<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Classes\Factory\FieldFactory;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PostTypeFields extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::VIEW_APP;

	public function __construct() {
		$this->actions = [
			'get_all_default_custom_field' => [
				'callback' => [ $this, 'get_all_default_custom_field' ],
				'fields' => [
					'field_type' => 'array|string',
					'post_type' => 'string',
					'object_type' => 'string',
				]
			],
			'get_post_fields_by_singular_name' => [
				'callback' => [ $this, 'get_post_fields_by_singular_name' ],
				'fields' => [
					'singular_name' => 'string',
				]
			],
			'get_post_fields_preset' => [
				'callback' => [ $this, 'get_post_fields_preset' ],
				'fields' => [
					'preset_name' => 'string',
					'post_custom_fields' => 'array|string',
				]
			],
		];
	}

	public function get_all_default_custom_field( $payload ) {
		try {
			$fields = [];
			foreach ( $payload['field_type'] as $field ) {
				$fieldInstance = FieldFactory::make( $field, $payload['post_type'] ?? '', '', $payload['object_type'] ?? 'post' );
				if ( is_object( $fieldInstance ) ) {
					$fields[ $field ] = $fieldInstance->get();
				}
			}
			wp_send_json_success( $fields );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function get_post_fields_by_singular_name( $payload ) {
		if ( empty( $payload['singular_name'] ) ) {
			wp_send_json_error( [ 'message' => __( 'singular_name is required to get the post.', 'easy-content-manager' ) ] );
		}

		$args = [
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'title' => $payload['singular_name'],
		];

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => __( 'No post found with the given singular_name.', 'easy-content-manager' ) ] );

			return;
		}

		$post = $posts[0];
		$post_content = maybe_unserialize( $post->post_content );

		wp_send_json_success([
			'custom_fields' => $post_content['custom_fields'],
		]);
	}

	public function get_post_fields_preset( $payload ) {
		$post_custom_fields = $payload['post_custom_fields'];

		$fields_classes = [];
		foreach ( $post_custom_fields as $field ) {
			$preset_args = Helper::get_preset_args( $payload['preset_name'], $field );
			$field_base = preg_replace( '/-\d+$/', '', $field );
			$fieldInstance = FieldFactory::make( $field_base, $payload['preset_name'] );

			// make() returns false for a premium field type on a free site. Calling
			// ->get() on that is a fatal, so a preset containing a gallery or
			// repeater field took the whole request down.
			if ( ! is_object( $fieldInstance ) ) {
				continue;
			}

			$fields_classes[] = $fieldInstance->get( false, $preset_args );
		}

		if ( empty( $fields_classes ) ) {
			wp_send_json_error( __( 'No field found', 'easy-content-manager' ) );
		}

		wp_send_json_success( $fields_classes );
	}
}
