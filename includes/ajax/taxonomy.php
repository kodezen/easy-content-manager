<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Taxonomy extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_TAXONOMIES;

	public function __construct() {
		$this->actions = [
			'create_custom_taxonomy' => [
				'callback' => [ $this, 'create_custom_taxonomy' ],
				'fields' => [
					'taxonomy_slug' => 'string',
					'singular_name' => 'string',
					'plural_name' => 'string',
					'object_type' => 'array|string',
					'custom_fields' => 'array|string',
					'advanced_configuration' => 'boolean',
					'hierarchical' => 'boolean',
					'configuration' => 'array|string',
				],
			],
			'update_custom_taxonomy' => [
				'callback' => [ $this, 'update_custom_taxonomy' ],
				'fields' => [
					'post_id' => 'integer',
					'taxonomy_slug' => 'string',
					'singular_name' => 'string',
					'plural_name' => 'string',
					'object_type' => 'array|string',
					'custom_fields' => 'array|string',
					'advanced_configuration' => 'boolean',
					'hierarchical' => 'boolean',
					'configuration' => 'array|string',
				],
			],
			'delete_custom_taxonomy' => [
				'callback' => [ $this, 'delete_custom_taxonomy' ],
				'fields' => [
					'post_id' => 'integer',
				],
			],
			'duplicate_custom_taxonomy' => [
				'callback' => [ $this, 'duplicate_custom_taxonomy' ],
				'fields' => [
					'taxonomy_id' => 'integer',
				]
			],
			'get_single_custom_taxonomy' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_single_custom_taxonomy' ],
				'fields' => [
					'post_id' => 'integer',
				],
			],
			'get_all_custom_taxonomies' => [
				'capability' => Capabilities::VIEW_APP,
				'callback' => [ $this, 'get_all_custom_taxonomies' ],
				'fields' => [
					'search' => 'string',
					'status' => 'string',
					'page' => 'integer',
					'per_page' => 'integer',
					'orderby' => 'string',
					'order' => 'string',
				],
			],
		];
	}

	public function create_custom_taxonomy( $payload ) {
		$slug = sanitize_key( $payload['taxonomy_slug'] );

		if ( empty( $slug ) ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy slug is required.', 'easy-content-manager' ) ] );
		}

		$is_locked = apply_filters( 'ecm_taxonomy_validation_before_create', false, $payload );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy creation was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$payload['configuration']['labels']['singular_name'] = $payload['singular_name'];
		$payload['configuration']['labels']['name'] = $payload['plural_name'];
		$payload['advanced_configuration'] = true;

		$config = $payload['advanced_configuration']
			? Helper::deep_merge_args( $this->get_taxonomy_default_data(), $payload['configuration'] )
			: $this->get_taxonomy_default_data_with_labels( $payload['singular_name'], $payload['plural_name'] );

		$config['taxonomy_slug'] = $slug;
		$config['object_type'] = (array) $payload['object_type'];
		$config['advanced_configuration'] = (bool) $payload['advanced_configuration'];
		$config['hierarchical'] = (bool) $payload['hierarchical'];
		$config['custom_fields'] = $payload['custom_fields'] ?? [];

		$post_data = [
			'post_title' => $slug,
			'post_content' => maybe_serialize( $config ),
			'post_status' => 'publish',
			'post_type' => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
			'post_name' => $slug,
		];

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to create taxonomy.', 'easy-content-manager' ),
				'error' => $post_id->get_error_message()
			]);
		}

		foreach ( $config['object_type'] as $post_type ) {
			$post = get_posts([
				'name'        => $post_type['post_type'] ?? $post_type['title'],
				'post_type'   => Helper::ECM_POST_TYPE_NAME,
				'numberposts' => 1,
			]);

			if ( ! empty( $post ) ) {
				$post_content = maybe_unserialize( $post[0]->post_content );
				$post_content['taxonomies'] = $post_content['taxonomies'] ?? [];

				if ( ! in_array( $slug, $post_content['taxonomies'], true ) ) {
					$post_content['taxonomies'][] = $slug;

					wp_update_post([
						'ID'           => $post[0]->ID,
						'post_content' => maybe_serialize( $post_content ),
					]);
				}
			}
		}//end foreach

		update_option( 'ecm_flush_taxonomy_rules', true );

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );

		$post_data = get_post( $post_id );
		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success( $post_data );
	}

	public function update_custom_taxonomy( $payload ) {
		$post_id = $payload['post_id'];

		if ( ! get_post( $post_id ) || Helper::ECM_TAXONOMY_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy not found.', 'easy-content-manager' ) ], 404 );
		}

		$is_locked = apply_filters( 'ecm_taxonomy_validation_before_update', false, $payload, get_post( $post_id ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy update was prevented by a filter.', 'easy-content-manager' ) ] );
		}

		$payload['configuration']['labels']['singular_name'] = $payload['singular_name'];
		$payload['configuration']['labels']['name'] = $payload['plural_name'];
		$payload['advanced_configuration'] = true;

		$slug = sanitize_key( $payload['taxonomy_slug'] );

		$config = $payload['advanced_configuration']
			? Helper::deep_merge_args( $this->get_taxonomy_default_data(), $payload['configuration'] )
			: $this->get_taxonomy_default_data_with_labels( $payload['singular_name'], $payload['plural_name'] );

		$config['taxonomy_slug'] = $slug;
		$config['object_type'] = (array) $payload['object_type'];
		$config['advanced_configuration'] = (bool) $payload['advanced_configuration'];
		$config['hierarchical'] = (bool) $payload['hierarchical'];
		$config['custom_fields'] = $payload['custom_fields'] ?? [];

		foreach ( $config['object_type'] as $post_type ) {
			$post = get_posts([
				'name'        => $post_type['post_type'] ?? $post_type['title'],
				'post_type'   => Helper::ECM_POST_TYPE_NAME,
				'numberposts' => 1,
			]);

			if ( ! empty( $post ) ) {
				$post_content = maybe_unserialize( $post[0]->post_content );
				$post_content['taxonomies'] = $post_content['taxonomies'] ?? [];

				if ( ! in_array( $slug, $post_content['taxonomies'], true ) ) {
					$post_content['taxonomies'][] = $slug;

					wp_update_post([
						'ID'           => $post[0]->ID,
						'post_content' => maybe_serialize( $post_content ),
					]);
				}
			}
		}//end foreach

		$post_data = [
			'ID' => $post_id,
			'post_title' => $slug,
			'post_content' => maybe_serialize( $config ),
		];

		$updated_id = wp_update_post( $post_data );

		if ( is_wp_error( $updated_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to update taxonomy.', 'easy-content-manager' ) ] );
		}

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );

		$post_data = get_post( $updated_id );
		$post_data->post_content = maybe_unserialize( $post_data->post_content );

		wp_send_json_success( $post_data );
	}

	public function delete_custom_taxonomy( $payload ) {
		$post_id = $payload['post_id'];

		$is_locked = apply_filters( 'ecm_taxonomy_validation_before_delete', false, $payload, get_post( $post_id ) );

		if( $is_locked ) {
			wp_send_json_error( [ 'message' => __( 'This taxonomy is locked and cannot be deleted.', 'easy-content-manager' ) ] );
		}

		if ( ! get_post( $post_id ) || Helper::ECM_TAXONOMY_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy not found.', 'easy-content-manager' ) ] );
		}

		$deleted = wp_delete_post( $post_id, true );

		if ( ! $deleted ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete taxonomy.', 'easy-content-manager' ) ] );
		}

		wp_send_json_success([
			'message' => __( 'Taxonomy deleted.', 'easy-content-manager' ),
			'id' => $post_id
		]);
	}

	public function get_single_custom_taxonomy( $payload ) {
		$post = get_post( $payload['post_id'] );

		if ( ! $post ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy not found.', 'easy-content-manager' ) ], 404 );
		}

		$post->post_content = maybe_unserialize( $post->post_content );
		wp_send_json_success( $post );
	}

	public function get_all_custom_taxonomies( $payload ) {
		$search = $payload['search'] ?? '';

		$status = isset( $payload['status'] )
			? [ $payload['status'] ]
			: [ 'publish', 'draft' ];

		$page     = ! empty( $payload['page'] ) ? max( 1, intval( $payload['page'] ) ) : 1;
		$per_page = ! empty( $payload['per_page'] ) ? intval( $payload['per_page'] ) : 10;
		$order_by = ! empty( $payload['order_by'] ) ? $payload['order_by'] : 'post_title';
		$order    = ! empty( $payload['order'] ) ? $payload['order'] : 'asc';

		$args = [
			'post_type'      => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			's'              => $search,
			'post_status'    => $status,
			'orderby'        => $order_by,
			'order'          => $order
		];

		$query = new \WP_Query( $args );

		$results = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = [
					'ID'          => get_the_ID(),
					'post_title'  => get_the_title(),
					'post_content' => maybe_unserialize( get_the_content() ),
					'post_status' => get_post_status(),
				];
			}
		}

		wp_send_json_success([
			'data' => $results,
			'pagination' => [
				'current_page' => $page,
				'per_page'     => $per_page,
				'total'        => intval( $query->found_posts ),
				'total_pages'  => intval( $query->max_num_pages ),
			]
		]);
	}

	public function duplicate_custom_taxonomy( $payload ) {
		if ( empty( $payload['taxonomy_id'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Taxonomy ID is required.', 'easy-content-manager' ) ] );
		}

		$taxonomy_post_type = Helper::ECM_TAXONOMY_POST_TYPE_NAME;
		$taxonomy_id = intval( $payload['taxonomy_id'] );
		$taxonomy = get_post( $taxonomy_id );

		if ( ! $taxonomy || $taxonomy->post_type !== $taxonomy_post_type ) {
			wp_send_json_error( [ 'message' => __( 'Invalid taxonomy or post type.', 'easy-content-manager' ) ], 404 );
		}

		$base_title = $taxonomy->post_title . '-copy';
		$base_slug  = $taxonomy->post_name . '-copy';

		$unique_title = $base_title;
		$unique_slug  = $base_slug;
		$counter      = 1;

		while ( post_exists( $unique_title, '', '', $taxonomy_post_type ) ) {
			$unique_title = $base_title . ( $counter > 1 ? '-' . $counter : '' );
			$unique_slug  = $base_slug . ( $counter > 1 ? '-' . $counter : '' );
			$counter++;
		}

		$new_taxonomy = [
			'post_title'   => sanitize_text_field( $unique_title ),
			'post_content' => $taxonomy->post_content,
			'post_status'  => 'draft',
			'post_type'    => $taxonomy_post_type,
			'post_name'    => sanitize_title( $unique_slug ),
		];

		$new_taxonomy_id = wp_insert_post( $new_taxonomy );

		if ( is_wp_error( $new_taxonomy_id ) ) {
			wp_send_json_error([
				'message' => __( 'Failed to duplicate taxonomy.', 'easy-content-manager' ),
				'error'   => $new_taxonomy_id->get_error_message()
			]);
		}

		$new_taxonomy_post = get_post( $new_taxonomy_id );

		wp_send_json_success([
			'ID'           => $new_taxonomy_post->ID,
			'post_title'   => $new_taxonomy_post->post_title,
			'post_name'    => $new_taxonomy_post->post_name,
			'post_status'  => $new_taxonomy_post->post_status,
			'post_content' => maybe_unserialize( $new_taxonomy_post->post_content ),
		]);
	}

	private function get_taxonomy_default_data_with_labels( $singular, $plural ) {
		$labels = [
			'name' => $plural,
			'singular_name' => $singular,
			'menu_name' => $plural,
			'all_items' => "All $plural",
			'edit_item' => "Edit $singular",
			'view_item' => "View $singular",
			'update_item' => "Update $singular",
			'add_new_item' => "Add New $singular",
			'new_item_name' => "New $singular Name",
			'parent_item' => "Parent $singular",
			'parent_item_colon' => "Parent $singular:",
			'search_items' => "Search $plural",
			'popular_items' => "Popular $plural",
			'separate_items_with_commas' => "Separate $plural with commas",
			'add_or_remove_items' => "Add or remove $plural",
			'choose_from_most_used' => "Choose from the most used $plural",
			'not_found' => "No $plural found.",
			'no_terms' => "No $plural items",
			'items_list_navigation' => "$plural list navigation",
			'items_list' => "$plural list",
			'most_used' => 'Most Used',
			'back_to_items' => "← Back to $plural",
			'item_link' => "$singular Link",
			'item_link_description' => "A link to a $singular.",
		];

		$defaults = $this->get_taxonomy_default_data();
		$defaults['labels'] = $labels;
		return $defaults;
	}

	private function get_taxonomy_default_data() {
		return [
			'taxonomy' => '',
			'taxonomy_slug' => '',
			'active' => true,
			'ui_type' => 'modern',
			'advanced_configuration' => false,
			'object_type' => [],
			'labels' => [
				'name' => '',
				'singular_name' => '',
				'search_items' => 'Search ',
				'popular_items' => 'Popular ',
				'all_items' => 'All ',
				'parent_item' => 'Parent ',
				'parent_item_colon' => 'Parent :',
				'edit_item' => 'Edit ',
				'view_item' => 'View ',
				'update_item' => 'Update ',
				'add_new_item' => 'Add New ',
				'new_item_name' => 'New  Name',
				'separate_items_with_commas' => 'Separate  with commas',
				'add_or_remove_items' => 'Add or remove ',
				'choose_from_most_used' => 'Choose from the most used ',
				'not_found' => 'No  found.',
				'no_terms' => 'No  items',
				'items_list_navigation' => ' list navigation',
				'items_list' => ' list',
				'back_to_items' => '← Back to ',
				'item_link' => ' Link',
				'item_link_description' => 'A link to a .',
			],
			'description' => '',
			'public' => true,
			'publicly_queryable' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'show_in_quick_edit' => true,
			'show_admin_column' => false,
			'show_in_rest' => true,
			'rest_base' => '',
			'rest_namespace' => 'wp/v2',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'rewrite' => [
				'enable_rewrite' => true,
				'slug' => '',
				'with_front' => true,
				'hierarchical' => false,
			],
			'capabilities_enabled' => false,
			'singular_capability_name' => 'term',
			'plural_capability_name' => 'terms',
			'capabilities' => [
				'manage_terms' => 'manage_categories',
				'edit_terms' => 'edit_categories',
				'delete_terms' => 'delete_categories',
				'assign_terms' => 'assign_categories',
			],
			'sort' => false,
			'args' => [],
			'default_term' => [
				'name' => '',
				'slug' => '',
				'description' => '',
			],
		];
	}
}
