<?php

namespace EasyContentManager\Utils\Traits;

use EasyContentManager\Features\RegisterPostTypes;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait PostTypes {


	public static function get_all_custom_post_types() {
		$args = [
			'post_type' => Helper::ECM_POST_TYPE_NAME,
			'posts_per_page' => -1,
		];

		return get_posts( $args );
	}

	public static function get_post_type_from_post_type_id( $post_type_id ) {
		$post_data = get_post( $post_type_id );

		$post_type_data = maybe_unserialize( $post_data->post_content );

		return $post_type_data['post_type'] ?? '';
	}

	public static function get_post_types_from_post_type_ids( $post_type_ids ) {
		if ( empty( $post_type_ids ) || ! is_array( $post_type_ids ) ) {
			return [];
		}

		$post_datas = get_posts([
			'post_type'      => Helper::ECM_POST_TYPE_NAME,
			'post__in'       => $post_type_ids,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		]);

		return array_filter(array_map(function( $item ) {
			if ( ! isset( $item->post_content ) ) {
				return null;
			}

			$content = maybe_unserialize( $item->post_content );
			return strtolower( $content['post_slug'] ?? null );

		}, $post_datas));
	}


	/**
	 * Per-request memo for the localise payloads below. Reset via
	 * flush_post_type_runtime_cache() whenever the underlying posts change, so a
	 * create-then-read within one request never sees pre-write data.
	 *
	 * @var array
	 */
	private static $runtime_post_type_cache = [];

	public static function flush_post_type_runtime_cache(): void {
		self::$runtime_post_type_cache = [];
	}

	public static function get_all_custom_post_types_with_modified_data() {
		if ( isset( self::$runtime_post_type_cache['post_types'] ) ) {
			return self::$runtime_post_type_cache['post_types'];
		}

		$post_type = Helper::ECM_POST_TYPE_NAME;

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		$local_post_type = ecm_get_json_from_files( ecm_get_plugins_post_type_path() );
		$theme_post_type = [];

		if ( Helper::has_theme_support() ) {
			$theme_post_type = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/post-types' ] );
		}

		$local_post_type = array_merge( $local_post_type, $theme_post_type );

		$posts = array_merge(
			$local_post_type,
			array_map( 'get_object_vars', get_posts( $args ) )
		);

		$structured = self::prepare_post_type_for_localise( $posts );

		self::$runtime_post_type_cache['post_types'] = self::handle_inherited_post_types( $structured );

		return self::$runtime_post_type_cache['post_types'];
	}

	public static function handle_inherited_post_types( array &$structured ): array {
		$inherited_post_types = Helper::get_settings( 'inherited_post_type' ) ?? [];

		foreach ( $inherited_post_types as $post_type => $is_enabled ) {

			if ( ! $is_enabled ) {
				continue;
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( ! $post_type_object ) {
				continue;
			}

			$taxonomies_native = get_object_taxonomies( $post_type, 'object' );

			$taxonomies = array_values(array_map(
				function ( $taxonomy ) {
					return [
						'label'        => $taxonomy->name,
						'hierarchical' => $taxonomy->hierarchical,
					];
				},
				$taxonomies_native
			));

			// Build structured post type configuration
			$structured[ $post_type ] = [
				'title'          => ucfirst( $post_type_object->name ),
				'slug'           => strtolower( $post_type ),
				'rest_base'      => $post_type_object->rest_base,
				'rest_namespace' => $post_type_object->rest_namespace,
				'custom_fields'  => [],
				'supports'       => array_keys( get_all_post_type_supports( $post_type ) ),
				'taxonomy'       => $taxonomies,
			];
		}//end foreach

		return $structured;
	}

	public static function get_all_custom_taxonomies_with_modified_data() {
		if ( isset( self::$runtime_post_type_cache['taxonomies'] ) ) {
			return self::$runtime_post_type_cache['taxonomies'];
		}

		$post_type = Helper::ECM_TAXONOMY_POST_TYPE_NAME;

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		self::$runtime_post_type_cache['taxonomies'] = self::prepare_taxonomy_for_localise( get_posts( $args ) );

		return self::$runtime_post_type_cache['taxonomies'];
	}

	public static function get_post_types_from_location_array( array $locations ): array {
		$post_types = [];
		foreach ( $locations as $orGroup ) {
			foreach ( $orGroup as $condition ) {
				if ( $condition['selected_type'] && 'is-equal-to' === $condition['variant'] ) {
					if ( empty( $post_types[ $condition['selected_type'] ] ) ) {
						$post_types[ $condition['selected_type'] ] = [];
					}
					$post_types[ $condition['selected_type'] ][] = $condition['selected_page'];
				}
			}
		}

		return $post_types;
	}

	public static function get_post_type_default_data() {
		return [
			'post_type' => '',
			'post_slug' => '',
			'active' => true,
			'ui_type' => 'modern',
			'advanced_configuration' => true,
			'labels' => [
				'name' => '',
				'singular_name' => '',
				'menu_name' => '',
				'all_items' => '',
				'edit_item' => 'Edit ',
				'view_item' => 'View ',
				'view_items' => 'View ',
				'add_new_item' => 'Add New ',
				'add_new' => 'Add New ',
				'new_item' => 'New ',
				'parent_item_colon' => 'Parent :',
				'search_items' => 'Search ',
				'not_found' => 'No test found',
				'not_found_in_trash' => 'No  found in Trash',
				'archives' => ' Archives',
				'attributes' => ' Attributes',
				'featured_image' => '',
				'set_featured_image' => '',
				'remove_featured_image' => '',
				'use_featured_image' => '',
				'insert_into_item' => 'Insert into ',
				'uploaded_to_this_item' => 'Uploaded to this ',
				'filter_items_list' => 'Filter  list',
				'filter_by_date' => 'Filter  by date',
				'items_list_navigation' => ' list navigation',
				'items_list' => ' list',
				'item_published' => ' published.',
				'item_published_privately' => ' published privately.',
				'item_reverted_to_draft' => ' reverted to draft.',
				'item_scheduled' => ' scheduled.',
				'item_updated' => ' updated.',
				'item_link' => ' Link',
				'item_link_description' => 'A link to a .',
			],
			'description' => '',
			'public' => false,
			'hierarchical' => false,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'admin_menu_parent' => '',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'show_in_rest' => true,
			'rest_base' => '',
			'rest_namespace' => 'wp/v2',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'menu_position' => '',
			'menu_icon' => [
				'type' => 'dashicons',
				'value' => 'dashicons-admin-post',
			],
			'rename_capabilities' => false,
			'singular_capability_name' => 'post',
			'plural_capability_name' => 'posts',
			'supports' => [
				'title',
				'editor',
				'thumbnail',
				'custom-fields',
			],
			'taxonomies' => [],
			'has_archive' => true,
			'has_archive_slug' => '',
			'rewrite' => [
				'permalink_rewrite' => 'post_type_key',
				'with_front' => '1',
				'feeds' => '0',
				'pages' => '1',
			],
			'query_var' => 'post_type_key',
			'query_var_name' => '',
			'can_export' => true,
			'delete_with_user' => false,
			'register_meta_box_cb' => '',
			'enter_title_here' => '',
		];
	}

	public static function get_prepared_post_type_data( array $data, array $advanced_configuration = [] ): array {
		$default = Helper::get_post_type_default_data();
		$data = [
			'post_type' => $data['singular_name'],
			'post_slug' => $data['post_slug'],
			'labels' => [
				'name' => $data['singular_name'],
				'singular_name' => $data['singular_name'],
				'plural_name' => $data['plural_name'],
				'menu_name' => $data['singular_name'],
				'all_items' => $data['singular_name'],
				'edit_item' => "Edit {$data['singular_name']}",
				'view_item' => "View {$data['singular_name']}",
				'view_items' => "View {$data['singular_name']}",
				'add_new_item' => "Add New {$data['singular_name']}",
				'add_new' => "Add New {$data['singular_name']}",
				'new_item' => "New {$data['singular_name']}",
				'parent_item_colon' => "Parent {$data['singular_name']}:",
				'search_items' => "Search {$data['singular_name']}",
				'not_found' => 'No test found',
				'not_found_in_trash' => "No {$data['singular_name']} found in Trash",
				'archives' => "{$data['singular_name']} Archives",
				'attributes' => "{$data['singular_name']} Attributes",
				'insert_into_item' => "Insert into {$data['singular_name']}",
				'uploaded_to_this_item' => "Uploaded to this {$data['singular_name']}",
				'filter_items_list' => "Filter {$data['singular_name']} list",
				'filter_by_date' => "Filter {$data['singular_name']} by date",
				'items_list_navigation' => "{$data['singular_name']} list navigation",
				'items_list' => "{$data['singular_name']} list",
				'item_published' => "{$data['singular_name']} published.",
				'item_published_privately' => "{$data['singular_name']} published privately.",
				'item_reverted_to_draft' => "{$data['singular_name']} reverted to draft.",
				'item_scheduled' => "{$data['singular_name']} scheduled.",
				'item_updated' => "{$data['singular_name']} updated.",
				'item_link' => "{$data['singular_name']} Link",
				'item_link_description' => "A link to a {$data['singular_name']}.",
			],
			'public' => $data['is_public'],
			'hierarchical' => $data['hierarchical'],
			'rest_base' => $data['post_slug'],
			'rest_namespace' => $data['rest_namespace'] ?? EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/v1',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'show_in_menu' => false,
			'custom_fields' => $data['custom_fields'] ?? [],
		];

		$data = Helper::deep_merge_args( $default, $data );

		if ( ! empty( $advanced_configuration ) ) {
			$data = Helper::deep_merge_args( $data, $advanced_configuration );
		}

		return $data;
	}

	public static function prepare_post_type_for_localise( array $posts ) {
		$structured = [];

		foreach ( $posts as $key => $post ) {
			$slug  = $post['post_name'] ?? $post['post_content']['post_slug'];

			$post_content = maybe_unserialize( $post['post_content'] );

			$taxonomies = isset( $post_content['taxonomies'] ) ?
				array_map(function ( $taxonomy ) {
					$tax_object = get_taxonomy( $taxonomy );
					return [
						'label' => $taxonomy,
						'hierarchical' => $tax_object ? $tax_object->hierarchical : false
					];
				}, $post_content['taxonomies']) :
				array_map(function ( $taxonomy ) {
					return [
						'label' => $taxonomy->label,
						'hierarchical' => $taxonomy->hierarchical
					];
				}, get_object_taxonomies( $post->post_type, 'objects' ));

			$structured[ $slug ] = [
				'title'          => $post['post_title'] ?? '',
				'slug'           => $slug,
				'rest_base'      => $post_content['rest_base'] ?? '',
				'rest_namespace' => $post_content['rest_namespace'] ?? '',
				'custom_fields'  => $post_content['custom_fields'] ?? [],
				'supports'       => $post_content['supports'] ?? [],
				'taxonomy'       => $taxonomies
			];
		}//end foreach

		return $structured;
	}

	public static function prepare_taxonomy_for_localise( array $posts ) {
		$structured = [];

		foreach ( $posts as $key => $post ) {
			$post_content = maybe_unserialize( $post->post_content );

			$structured[ $post_content['taxonomy_slug'] ] = [
				'title'          => $post_content['lablels']['name'] ?? '',
				'slug'           => $post_content['taxonomy_slug'] ?? '',
				'rest_base'      => $post_content['rest_base'] ?? '',
				'rest_namespace' => $post_content['rest_namespace'] ?? '',
				'custom_fields'  => $post_content['custom_fields'] ?? [],
			];
		}//end foreach

		return $structured;
	}
}
