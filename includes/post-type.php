<?php

namespace EasyContentManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PostType {




	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'register_post_types' ] );
	}

	public function register_post_types() {
		// registering ecm_post_type
		register_post_type(
			'ecm_post_types',
			[
				'labels' => [
					'name' => __( 'Post Types', 'easy-content-manager' ),
					'singular_name' => __( 'Post Type', 'easy-content-manager' ),
					'add_new' => __( 'Add New', 'easy-content-manager' ),
					'add_new_item' => __( 'Add New Post Type', 'easy-content-manager' ),
					'edit_item' => __( 'Edit Post Type', 'easy-content-manager' ),
					'new_item' => __( 'New Post Type', 'easy-content-manager' ),
					'view_item' => __( 'View Post Type', 'easy-content-manager' ),
					'search_items' => __( 'Search Post Types', 'easy-content-manager' ),
					'not_found' => __( 'No Post Types found', 'easy-content-manager' ),
					'not_found_in_trash' => __( 'No Post Types found in Trash', 'easy-content-manager' ),
				],
				'public' => false,
				'hierarchical' => true,
				'show_ui' => true,
				'show_in_menu' => false,
				'_builtin' => false,
				'capability_type' => 'ecm_post_type',
				'map_meta_cap' => true,
				'supports' => false,
				'rewrite' => false,
				'query_var' => false,
			]
		);

		// registering the ecm_field_group
		register_post_type(
			'ecm_field_group',
			[
				'labels' => [
					'name' => __( 'Field Group', 'easy-content-manager' ),
					'singular_name' => __( 'Field Group', 'easy-content-manager' ),
					'add_new' => __( 'Add New', 'easy-content-manager' ),
					'add_new_item' => __( 'Add New Field Group', 'easy-content-manager' ),
					'edit_item' => __( 'Edit Field Group', 'easy-content-manager' ),
					'new_item' => __( 'New Field Group', 'easy-content-manager' ),
					'view_item' => __( 'View Field Group', 'easy-content-manager' ),
					'search_items' => __( 'Search Field Group', 'easy-content-manager' ),
					'not_found' => __( 'No Field Group found', 'easy-content-manager' ),
					'not_found_in_trash' => __( 'No Field Group found in Trash', 'easy-content-manager' ),
				],
				'public' => false,
				'hierarchical' => true,
				'show_ui' => true,
				'show_in_menu' => false,
				'_builtin' => false,
				'capability_type' => 'ecm_field_group',
				'map_meta_cap' => true,
				'supports' => false,
				'rewrite' => false,
				'query_var' => false,
			]
		);

		// registering the ecm_settings
		register_post_type(
			'ecm_settings',
			[
				'labels' => [
					'name'                  => __( 'Settings', 'easy-content-manager' ),
					'singular_name'         => __( 'Setting', 'easy-content-manager' ),
					'add_new'               => __( 'Add New', 'easy-content-manager' ),
					'add_new_item'          => __( 'Add New Setting', 'easy-content-manager' ),
					'edit_item'             => __( 'Edit Setting', 'easy-content-manager' ),
					'new_item'              => __( 'New Setting', 'easy-content-manager' ),
					'view_item'             => __( 'View Setting', 'easy-content-manager' ),
					'search_items'          => __( 'Search Settings', 'easy-content-manager' ),
					'not_found'             => __( 'No Settings found', 'easy-content-manager' ),
					'not_found_in_trash'    => __( 'No Settings found in Trash', 'easy-content-manager' ),
				],
				'public'            => false,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'_builtin'          => false,
				'capability_type'   => 'ecm_settings',
				'map_meta_cap'      => true,
				'supports'          => false,
				'rewrite'           => false,
				'query_var'         => false,
			]
		);
	}
}
