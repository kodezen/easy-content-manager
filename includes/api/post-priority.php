<?php
namespace EasyContentManager\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Query;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostPriority extends WP_REST_Controller {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {

		register_rest_route(
			'easy-content-manager/v1',
			'/posts-priority',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_posts' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => [
						'post_type' => [
							'type'     => 'string',
							'required' => true,
						],
						'page' => [
							'type'    => 'integer',
							'default' => 1,
						],
						'per_page' => [
							'type'    => 'integer',
							'default' => 20,
						],
						'search' => [
							'type'    => 'string',
							'default' => '',
						],
					],
				],

				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_priorities' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => [
						'priorities' => [
							'type'        => 'object',
							'required'    => false,
							'description' => __( 'Map of post_id => priority.', 'easy-content-manager' ),
						],
						'move' => [
							'type'        => 'object',
							'required'    => false,
							'description' => __( 'Move a single post (post_id) to a target priority rank among every post of its type, shifting the rest to keep priorities unique.', 'easy-content-manager' ),
						],
					],
				],
			]
		);
	}

	public function permissions_check() {
		return current_user_can( 'edit_posts' );
	}

	public function get_posts( $request ) {
		$post_type = sanitize_key( $request->get_param( 'post_type' ) );
		$page      = max( 1, (int) $request->get_param( 'page' ) );
		$per_page  = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$search    = $request->get_param( 'search' );
		
		if ( ! post_type_exists( $post_type ) ) {
			return new WP_Error(
				'invalid_post_type',
				__( 'Invalid post type.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}
		
		global $wpdb;
		
		// Check if any posts have a menu_order set (greater than 0)
		$has_priority = (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_type = %s 
			AND post_status = 'publish' 
			AND menu_order > 0 
			LIMIT 1",
			$post_type
		) );
		
		$query_args = [
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $per_page,
			'paged'                  => $page,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'no_found_rows'          => false,
			's'                      => $search,
		];
		
		if ( $has_priority ) {
			$query_args['orderby'] = [
				'menu_order' => 'DESC',
				'date'       => 'DESC',
			];
			
			add_filter( 'posts_orderby', function( $orderby, $query ) use ( $wpdb ) {
				if ( $query->get( 'ecm_custom_order' ) ) {
					// Order by: menu_order = 0 last, then menu_order ASC, then date DESC
					$orderby = "CASE WHEN {$wpdb->posts}.menu_order = 0 THEN 1 ELSE 0 END ASC, 
								{$wpdb->posts}.menu_order ASC, 
								{$wpdb->posts}.post_date DESC";
				}
				return $orderby;
			}, 10, 2 );
			
			$query_args['ecm_custom_order'] = true;
		} else {
			$query_args['orderby'] = [ 'date' => 'DESC' ];
		}
		
		$query = new WP_Query( $query_args );
		
		$items = [];
		foreach ( $query->posts as $post ) {
			$items[] = [
				'id'         => (int) $post->ID,
				'title'      => get_the_title( $post ),
				'slug'       => $post->post_name,
				'created_at' => $post->post_date,
				'updated_at' => $post->post_modified,
				'priority'   => (int) $post->menu_order,
			];
		}
		
		$response = new WP_REST_Response( $items );
		$response->header( 'X-WP-Total', (int) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );
		
		return $response;
	}

	public function update_priorities( $request ) {

		$move = $request->get_param( 'move' );

		if ( is_array( $move ) && ! empty( $move['post_id'] ) ) {
			return $this->move_priority( $move );
		}

		$priorities = $request->get_param( 'priorities' );

		if ( ! is_array( $priorities ) ) {
			return new WP_Error(
				'invalid_payload',
				__( 'Priorities must be an object of post_id => priority.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		$updated = [];

		foreach ( $priorities as $post_id => $priority ) {

			$post_id  = (int) $post_id;
			$priority = max( 0, (int) $priority );
			$post = get_post( $post_id );

			if ( ! $post ) {
				continue;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			// Update the menu_order directly
			wp_update_post( [
				'ID'         => $post_id,
				'menu_order' => $priority,
			] );

			$updated[] = [
				'id'       => $post_id,
				'title'    => get_the_title( $post ),
				'slug'     => $post->post_name,
				'priority' => $priority,
			];
		}//end foreach

		return new WP_REST_Response( $updated );
	}

	/**
	 * Moves a single post to a target priority rank among every published post
	 * of its type, shifting the rest so priorities stay unique and contiguous.
	 * Operates on the complete set for the post type (not just what the client
	 * currently has loaded), so this stays correct even when the client's view
	 * is filtered by search or paged.
	 *
	 * @param array $move ['post_id' => int, 'priority' => int]
	 * @return WP_REST_Response|WP_Error
	 */
	private function move_priority( $move ) {
		$post_id  = (int) ( $move['post_id'] ?? 0 );
		$priority = isset( $move['priority'] ) ? (int) $move['priority'] : 0;

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'invalid_post',
				__( 'Invalid post.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'forbidden',
				__( 'You are not allowed to edit this post.', 'easy-content-manager' ),
				[ 'status' => 403 ]
			);
		}

		// A move is a bulk operation: it renumbers every published post of the type,
		// not just the one being dragged. Being able to edit the dragged post is
		// therefore not enough — the caller must be allowed to edit other people's
		// posts of this type as well. The per-post check in the write loop below
		// still applies on top of this.
		$post_type_object = get_post_type_object( $post->post_type );

		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			return new WP_Error(
				'forbidden',
				__( 'You are not allowed to reorder this post type.', 'easy-content-manager' ),
				[ 'status' => 403 ]
			);
		}

		global $wpdb;

		// Order the same way the list is displayed: explicit priority ascending,
		// 0/unset last, tie-broken by date desc.
		$siblings = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, menu_order FROM {$wpdb->posts}
			 WHERE post_type = %s AND post_status = 'publish'
			 ORDER BY CASE WHEN menu_order = 0 THEN 1 ELSE 0 END ASC, menu_order ASC, post_date DESC",
			$post->post_type
		) );

		$ids                 = [];
		$current_priorities  = [];

		foreach ( $siblings as $sibling ) {
			$sibling_id                        = (int) $sibling->ID;
			$ids[]                             = $sibling_id;
			$current_priorities[ $sibling_id ] = (int) $sibling->menu_order;
		}

		$from_index = array_search( $post_id, $ids, true );

		if ( false === $from_index ) {
			return new WP_Error(
				'not_found',
				__( 'Post not found among its siblings.', 'easy-content-manager' ),
				[ 'status' => 404 ]
			);
		}

		array_splice( $ids, $from_index, 1 );

		$to_index = max( 0, min( $priority - 1, count( $ids ) ) );
		array_splice( $ids, $to_index, 0, [ $post_id ] );

		$updated = [];
		$skipped = [];

		foreach ( $ids as $index => $id ) {
			$new_priority = $index + 1;

			if ( ( $current_priorities[ $id ] ?? 0 ) === $new_priority ) {
				continue;
			}

			// Mirrors update_priorities(): never write to a post this user cannot
			// edit. It keeps its existing priority and is reported back so the
			// client can reconcile rather than silently showing a stale order.
			if ( ! current_user_can( 'edit_post', $id ) ) {
				$skipped[] = [
					'id'       => $id,
					'priority' => $current_priorities[ $id ] ?? 0,
				];
				continue;
			}

			wp_update_post( [
				'ID'         => $id,
				'menu_order' => $new_priority,
			] );

			$updated[] = [
				'id'       => $id,
				'title'    => get_the_title( $id ),
				'priority' => $new_priority,
			];
		}

		$response = new WP_REST_Response( $updated );

		if ( ! empty( $skipped ) ) {
			$response->header( 'X-ECM-Skipped', (string) count( $skipped ) );
		}

		return $response;
	}
}
