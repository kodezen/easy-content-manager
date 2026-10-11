<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if access directly
}

class PostTypes extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_POST_TYPES;

	public function __construct() {
		$this->actions = [
			'create_custom_post_type' => [
				'callback' => [ $this, 'create_custom_post_type' ],
				'fields' => [
					'singular_name'          => 'string',
					'plural_name'            => 'string',
					'post_slug'              => 'string',
					'is_public'              => 'boolean',
					'hierarchical'           => 'boolean',
					'custom_fields'          => 'array|string',
					'advanced_configuration' => 'boolean',
					'advanced_config_data'   => 'array|string',
				]
			],
			'update_custom_post_type' => [
				'callback' => [ $this, 'update_custom_post_type' ],
				'fields' => [
					'post_id'                => 'integer',
					'singular_name'          => 'string',
					'plural_name'            => 'string',
					'post_slug'              => 'string',
					'is_public'              => 'boolean',
					'hierarchical'           => 'boolean',
					'custom_fields'          => 'array|string',
					'advanced_config_data'   => 'array|string',
				]
			],
			'delete_custom_post_type' => [
				'callback' => [ $this, 'delete_custom_post_type' ],
				'fields' => [
					'post_id' => 'integer',
				]
			],
			'get_single_custom_post_type' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_single_custom_post_type' ],
				'fields' => [
					'post_id' => 'integer',
					'post_title' => 'string',
				]
			],
			'get_all_custom_post_types' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_all_custom_post_types' ],
				'fields' => [
					'search' => 'string',
					'status' => 'string',
				]
			],
			'duplicate_custom_post_type' => [
				'callback' => [ $this, 'duplicate_custom_post_type' ],
				'fields' => [
					'post_id' => 'integer',
				]
			],
			'get_existing_post_type' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_existing_post_type' ],
				'fields' => [
					'built_in' => 'boolean',
				]
			],
			'get_excluded_post_type_details' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_excluded_post_type_details' ],
				'fields' => [
					'post_type_slug' => 'string',
				]
			],
			'get_registered_post_metas' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_registered_post_metas' ],
				'fields' => [
					'object_type' => 'string',
					'object_subtype' => 'string',
				]
			],
			'update_custom_post_type_status' => [
				'callback' => [ $this, 'update_custom_post_type_status' ],
				'fields' => [
					'post_id'     => 'integer',
					'post_status' => 'string',
				]
			],
			'get_post_types_taxonomy' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_post_types_taxonomy' ],
			],
			'save_default_post_types' => [
				'callback' => [ $this, 'save_default_post_types' ],
				'fields' => [
					'post_types' => 'array|object',
					'built_in' => 'boolean'
				]
			],
            'custom_template_url' => [
                'callback' => [ $this, 'custom_template_url' ],
                'fields' => [
                    'post_type' => 'string',
                    'type' => 'string',
                ]
            ]
		];
	}

	public function get_excluded_post_type_details( $payload ) {
		$post_type_slug = $payload['post_type_slug'];

		if ( empty( $post_type_slug ) ) {
			wp_send_json_error( [ 'message' => __( 'Post type slug is required.', 'easy-content-manager' ) ] );
		}

		$post_type = get_post_type_object( $post_type_slug );

		if ( ! $post_type ) {
			wp_send_json_error( [ 'message' => __( 'Post type not found.', 'easy-content-manager' ) ] );
		}

		wp_send_json_success( $post_type );
	}
	public function get_existing_post_type( $payload ): array {
		$post_types = get_post_types( [] );

		return $post_types;
	}

	public function create_custom_post_type( $payload ) {
		if ( empty( $payload['singular_name'] ) || empty( $payload['post_slug'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Post title and slug are required.', 'easy-content-manager' ) ] );
		}

		$is_locked = apply_filters( 'ecm_post_type_validation_before_create', false, $payload );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'This post type is locked and cannot be created.', 'easy-content-manager' ) ] );
		}

		$advanced_configuration = true;
		$advanced_configs = [];
		if ( $advanced_configuration ) {
			$advanced_configs = $payload['advanced_config_data'];
		}

		$advanced_configs = array_merge( $advanced_configs, [ 'advanced_configuration' => $payload['advanced_configuration'] ] );

		$prepared_data = $this->prepare_item_for_database( $payload, $advanced_configs );

		$post_data = [
			'post_title' => sanitize_text_field( $payload['singular_name'] ),
			'post_content' => maybe_serialize( $prepared_data ),
			'post_status' => 'publish',
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'post_name' => sanitize_title( $payload['post_slug'] ),
		];

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to create post.', 'easy-content-manager' ),
				'error' => $post_id->get_error_message()
			]);

			return;
		}

		update_option( 'ecm_flush_rewrite_rules', true );

		$this->clear_cache();

		$post_data = get_post( $post_id );

		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success([
			'ID' => $post_data->ID,
			'post_title' => $post_data->post_title,
			'post_content' => $post_data->post_content,
			'post_status' => $post_data->post_status,
			'post_slug' => $post_data->post_name,
		]);
	}

	public function update_custom_post_type( $payload ) {
		$post_id = $payload['post_id'];
		if ( ! get_post( $post_id ) || Helper::ECM_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Custom Post not found.', 'easy-content-manager' ) ], 404 );
		}

		$is_locked = apply_filters( 'ecm_post_type_validation_before_update', false, $payload, get_post( $post_id ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'This post type is locked and cannot be updated.', 'easy-content-manager' ) ] );
		}

		$advanced_configuration = true;
		$advanced_configs = [];
		if ( $advanced_configuration ) {
			$advanced_configs = $payload['advanced_config_data'];
		}

		$advanced_configs = array_merge( $advanced_configs, [ 'advanced_configuration' => $payload['advanced_configuration'] ] );

		$prepared_data = $this->prepare_item_for_database( $payload, $advanced_configs );

		$post_data = [
			'ID' => $post_id,
			'post_title' => sanitize_text_field( $payload['singular_name'] ),
			'post_content' => maybe_serialize( $prepared_data ),
			'post_status' => 'publish',
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'post_name' => sanitize_title( $payload['post_slug'] ),
		];

		$updated_post_id = wp_update_post( $post_data );

		if ( is_wp_error( $updated_post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to update post.', 'easy-content-manager' ) ] );
		}

		$this->clear_cache();

		$post_data = get_post( $updated_post_id );
		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success( $post_data );
	}
	public function delete_custom_post_type( $payload ) {
		$post_id = $payload['post_id'];

		$is_locked = apply_filters( 'ecm_post_type_validation_before_delete', false, $payload, get_post( $post_id ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'This post type is locked and cannot be deleted.', 'easy-content-manager' ) ] );
		}

		if ( ! get_post( $post_id ) || Helper::ECM_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Custom Post not found.', 'easy-content-manager' ) ] );
		}

		$deleted = wp_delete_post( $post_id, true );

		if ( ! $deleted ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete post.', 'easy-content-manager' ) ] );
		}

		$this->clear_cache();

		wp_send_json_success([
			'message' => __( 'Post deleted successfully.', 'easy-content-manager' ),
			'id' => $payload['post_id']
		]);
	}

	public function get_single_custom_post_type( $payload ) {
		$post_id = $payload['post_id'] ?? 0;
		$post_title = $payload['post_title'] ?? '';

		$post = [];

		if ( $post_id ) {
			$post = get_post( $post_id );
		} elseif ( $post_title ) {
			$query = new WP_Query([
				'title'        => $post_title,
				'post_type'    => Helper::ECM_POST_TYPE_NAME,
				'post_status'  => 'any',
				'posts_per_page' => 1,
			]);

			if ( $query->have_posts() ) {
				$post = $query->posts[0];
			}
		} else {
			wp_send_json_error( [ 'message' => __( 'post_id or post_title is required.', 'easy-content-manager' ) ], 400 );
		}

		if ( ! $post ) {
			wp_send_json_error( [ 'message' => __( 'Post not found.', 'easy-content-manager' ) ], 404 );
		}

		$post_data = [
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => maybe_unserialize( $post->post_content ),
			'post_status' => $post->post_status,
		];

		wp_send_json_success( $post_data );
	}

	public function get_all_custom_post_types( $payload ) {
		$search = $payload['search'] ?? '';
		$status = isset( $payload['status'] ) ? [ $payload['status'] ] : [ 'publish', 'draft' ];
		$args = [
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'posts_per_page' => -1,
			's' => $search,
			'post_status' => $status,
		];

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts = [];
			while ( $query->have_posts() ) {
				$query->the_post();
				$content = maybe_unserialize( get_the_content() );
				$posts[] = [
					'ID' => get_the_ID(),
					'post_title' => get_the_title(),
					'post_content' => $content,
					// A post type saved with no custom fields stores no `custom_fields`
					// key at all, and count( null ) is fatal on PHP 8.
					'total_fields' => is_countable( $content['custom_fields'] ?? null ) ? count( $content['custom_fields'] ) : 0,
					'post_status' => get_post_status(),
				];
			}

			wp_send_json_success( $posts );
		} else {
			wp_send_json_success( [] );
		}
	}

	public function duplicate_custom_post_type( $payload ) {
		if ( empty( $payload['post_id'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Post ID is required.', 'easy-content-manager' ) ] );
		}

		$original_post = get_post( $payload['post_id'] );

		if ( ! $original_post || Helper::ECM_POST_TYPE_NAME !== $original_post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post or post type.', 'easy-content-manager' ) ] );
		}

		$custom_post_details = maybe_unserialize( $original_post->post_content );

		$base_singular_name = $custom_post_details['labels']['singular_name'] . '_copy';
		$base_plural_name = $custom_post_details['labels']['plural_name'] . '_copies';
		$base_post_slug = $custom_post_details['post_slug'] . '-copy';

		$unique_singular_name = $base_singular_name;
		$unique_plural_name = $base_plural_name;
		$unique_post_slug = $base_post_slug;
		$counter = 1;

		while ( post_exists( $unique_singular_name, '', '', Helper::ECM_POST_TYPE_NAME ) ) {
			$unique_singular_name = $base_singular_name . '_' . $counter;
			$unique_plural_name = $base_plural_name . '_' . $counter;
			$unique_post_slug = $base_post_slug . '-' . $counter;
			$counter++;
		}

		$args = [
			'singular_name' => $unique_singular_name,
			'plural_name' => $unique_plural_name,
			'post_slug' => $unique_post_slug,
			'hierarchical' => $custom_post_details['hierarchical'],
			'custom_fields' => $custom_post_details['custom_fields'] ?? [],
		];

		$advanced_configurations = array_diff_key( $custom_post_details, array_flip( [ 'labels', 'post_slug', 'custom_fields', 'post_type', 'active', 'reset_base' ] ) );

		$prepared_data = $this->prepare_item_for_database( $args, $advanced_configurations );
		$post_data = [
			'post_title' => sanitize_text_field( $unique_singular_name ),
			'post_content' => maybe_serialize( $prepared_data ),
			'post_status' => 'draft',
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'post_name' => sanitize_title( $unique_post_slug ),
		];

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to create post.', 'easy-content-manager' ),
				'error' => $post_id->get_error_message()
			]);
		}

		update_option( 'ecm_flush_rewrite_rules', true );

		$this->clear_cache();

		$post_data = get_post( $post_id );

		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success([
			'ID' => $post_data->ID,
			'post_title' => $post_data->post_title,
			'post_content' => $post_data->post_content,
			'post_status' => $post_data->post_status,
			'post_slug' => $post_data->post_name,
		]);
	}

	private function prepare_item_for_database( $sanitized_data, $advanced_configuration = [] ) {
		return Helper::get_prepared_post_type_data( $sanitized_data, $advanced_configuration );
	}

	public function get_registered_post_metas( $payload ) {
		$object_type = $payload['object_type'] ?? null;
		$object_subtype = $payload['object_subtype'] ?? null;

		if ( ! $object_type ) {
			wp_send_json_error( [ 'message' => __( 'Object Type is required.', 'easy-content-manager' ) ] );
		}

		if ( ! $object_subtype ) {
			wp_send_json_error( [ 'message' => __( 'Object Subtype is required.', 'easy-content-manager' ) ] );
		}

		$meta_keys = get_registered_meta_keys( $object_type, $object_subtype );

		if ( empty( $meta_keys ) ) {
			wp_send_json_error( [ 'message' => __( 'No meta keys found for this post type.', 'easy-content-manager' ) ] );
		}

		wp_send_json_success( $meta_keys );
	}

	public function update_custom_post_type_status( $payload ) {
		$post_id     = $payload['post_id'];
		$post_status = $payload['post_status'];

		if ( ! get_post( $post_id ) || Helper::ECM_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Post not found.', 'easy-content-manager' ) ] );
		}

		$allowed_statuses = [ 'publish', 'draft', 'pending', 'private', 'trash' ];
		if ( ! in_array( $post_status, $allowed_statuses, true ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post status.', 'easy-content-manager' ) ] );
		}

		$updated_post_id = wp_update_post([
			'ID'          => $post_id,
			'post_status' => $post_status,
		]);

		if ( is_wp_error( $updated_post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to update post status.', 'easy-content-manager' ) ] );
		}

		$this->clear_cache();

		$post = get_post( $updated_post_id );
		$post_data = [
			'ID'           => (int) $post->ID,
			'post_title'   => $post->post_title,
			'post_content' => maybe_unserialize( $post->post_content ),
			'post_status'  => $post->post_status,
			'post_slug'    => $post->post_name,
		];

		wp_send_json_success( $post_data );
	}

	public function get_post_types_taxonomy( $payload ) {
		$taxonomies = get_taxonomies( [], 'objects' );
		$response = [];

		foreach ( $taxonomies as $taxonomy ) {
			$response[] = [
				'name'        => $taxonomy->name,
				'label'       => $taxonomy->label,
				'show_in_rest' => $taxonomy->show_in_rest,
			];
		}

		wp_send_json_success( $response );
	}

	public function save_default_post_types( $payload ) {
		$post_types = $payload['post_types'] ?? [];

		$all_post_types_objects = get_post_types( [ '_builtin' => true ], 'objects' );

		$filtered_post_types = [];

		foreach ( $all_post_types_objects as $key => $post_type_obj ) {
			if ( in_array( $key, [ 'post', 'page' ], true ) ) {
				$filtered_post_types[ $key ] = false;
			}
		}

		$ecm_settings = maybe_unserialize( get_option( 'ecm_settings' ) );

		if ( empty( $ecm_settings ) ) {
			$ecm_settings = [];
		}

		$ecm_settings = array_merge( $filtered_post_types, $ecm_settings );

		if ( is_array( $post_types ) ) {
			$ecm_settings = array_merge( $ecm_settings, $post_types );
		}

		update_option( 'ecm_settings', maybe_serialize( $ecm_settings ) );

		wp_send_json_success( $ecm_settings );
	}

    public function custom_template_url( $payload ) {
        $post_type = sanitize_key( $payload['post_type'] ?? '' );
        $type      = sanitize_key( $payload['type'] ?? 'single' );

        if ( ! in_array( $type, [ 'single', 'archive' ], true ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid template type.', 'easy-content-manager' ) ] );
        }

        if ( empty( $post_type ) ) {
            wp_send_json_error( [ 'message' => __( 'Post type is required.', 'easy-content-manager' ) ] );
        }

        if ( ! Helper::is_fse_theme() ) {
            wp_send_json_error( [ 'message' => __( 'Current theme does not support Site Editor.', 'easy-content-manager' ) ] );
        }

        $theme       = wp_get_theme();
        $theme_slug = $theme->get_stylesheet();

        $template_slug = ( $type === 'single' )
            ? 'single-' . $post_type
            : 'archive-' . $post_type;

        $template_id = $theme_slug . '//' . $template_slug;

        $template = get_block_template( $template_id, 'wp_template' );

        if ( ! $template ) {
            $fallback_slug = $type;
            $template_id  = $theme_slug . '//' . $fallback_slug;
            $template     = get_block_template( $template_id, 'wp_template' );
        }

        if ( ! $template ) {
            wp_send_json_error( [
                'message' => ucfirst( $type ) . ' template not found.',
            ] );
        }

        $editor_link = admin_url(
            'site-editor.php?postType=wp_template&postId=' . urlencode( $template->id )
        );

        wp_send_json_success( [
            'template_id'   => $template->id,
            'template_slug' => $template->slug,
            'template_type' => $type,
            'editor_link'   => $editor_link,
        ] );
    }




    private function clear_cache() {
		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );
		delete_transient( 'ecm_priority_meta_slugs' );
	}
}
