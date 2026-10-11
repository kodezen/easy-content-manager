<?php
namespace EasyContentManager\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermPriority extends WP_REST_Controller {

	const PRIORITY_META_KEY = 'term_priority';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {

		register_rest_route(
			'easy-content-manager/v1',
			'/terms-priority',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_terms' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => [
						'taxonomy' => [
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Taxonomy slug.', 'easy-content-manager' ),
						],
						'page' => [
							'type'        => 'integer',
							'default'     => 1,
							'minimum'     => 1,
							'description' => __( 'Current page number.', 'easy-content-manager' ),
						],
						'per_page' => [
							'type'        => 'integer',
							'default'     => 20,
							'minimum'     => 1,
							'maximum'     => 100,
							'description' => __( 'Number of terms per page.', 'easy-content-manager' ),
						],
						'hide_empty' => [
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Hide terms with no posts.', 'easy-content-manager' ),
						],
						'search' => [
							'type'        => 'string',
							'description' => __( 'Search term name.', 'easy-content-manager' ),
						],
					],
				],

				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_priorities' ],
					'permission_callback' => [ $this, 'permissions_check' ],
					'args'                => [
						'taxonomy' => [
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Taxonomy slug.', 'easy-content-manager' ),
						],
						'priorities' => [
							'type'        => 'object',
							'required'    => true,
							'description' => __( 'Map of term_id => priority.', 'easy-content-manager' ),
						],
					],
				],
			]
		);
	}

	public function permissions_check( $request ) {
		$taxonomy = sanitize_key( $request->get_param( 'taxonomy' ) );

		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error(
				'invalid_taxonomy',
				__( 'Invalid taxonomy.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_obj ) {
			return false;
		}

		// Check if user can manage terms for this taxonomy
		$cap = isset( $taxonomy_obj->cap->manage_terms ) ? $taxonomy_obj->cap->manage_terms : 'manage_categories';

		return current_user_can( $cap );
	}

	public function get_terms( $request ) {
		$taxonomy   = sanitize_key( $request->get_param( 'taxonomy' ) );
		$page       = max( 1, (int) $request->get_param( 'page' ) );
		$per_page   = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$hide_empty = (bool) $request->get_param( 'hide_empty' );
		$search     = sanitize_text_field( $request->get_param( 'search' ) );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error(
				'invalid_taxonomy',
				__( 'Invalid taxonomy.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		// Check if any terms have priority set
		$has_priority = $this->has_term_priorities( $taxonomy );

		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => $hide_empty,
			'number'     => $per_page,
			'offset'     => ( $page - 1 ) * $per_page,
		];

		// Add search if provided
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		// Set ordering based on priority existence
		if ( $has_priority ) {
			// Get all terms and manually sort by priority
			$all_terms = get_terms( array_merge( $args, [
				'number' => 0,
				'offset' => 0,
			] ) );

			if ( is_wp_error( $all_terms ) ) {
				return new WP_Error(
					'terms_query_failed',
					$all_terms->get_error_message(),
					[ 'status' => 500 ]
				);
			}

			// Add priority to each term and sort
			foreach ( $all_terms as $term ) {
				$term->priority = (int) get_term_meta( $term->term_id, self::PRIORITY_META_KEY, true );
			}

			// Sort by priority (ASC), then name (ASC)
			usort( $all_terms, function( $a, $b ) {
				if ( $a->priority === $b->priority ) {
					return strcmp( $a->name, $b->name );
				}
				return $a->priority - $b->priority;
			} );

			// Apply pagination manually
			$terms = array_slice( $all_terms, ( $page - 1 ) * $per_page, $per_page );
			$total_terms = count( $all_terms );
		} else {
			$args['orderby'] = 'name';
			$args['order']   = 'ASC';

			// Get terms
			$terms = get_terms( $args );

			if ( is_wp_error( $terms ) ) {
				return new WP_Error(
					'terms_query_failed',
					$terms->get_error_message(),
					[ 'status' => 500 ]
				);
			}

			// Get total count for pagination
			$count_args = [
				'taxonomy'   => $taxonomy,
				'hide_empty' => $hide_empty,
				'fields'     => 'count',
			];

			if ( ! empty( $search ) ) {
				$count_args['search'] = $search;
			}

			$total_terms = get_terms( $count_args );
		}//end if
		$total_pages = ceil( $total_terms / $per_page );

		// Format response
		$items = [];
		foreach ( $terms as $term ) {
			$items[] = $this->prepare_term_response( $term );
		}

		$response = new WP_REST_Response( $items );
		$response->header( 'X-WP-Total', (int) $total_terms );
		$response->header( 'X-WP-TotalPages', (int) $total_pages );
		$response->header( 'X-WP-HasPriority', $has_priority ? '1' : '0' );

		return $response;
	}

	public function update_priorities( $request ) {
		$taxonomy   = sanitize_key( $request->get_param( 'taxonomy' ) );
		$priorities = $request->get_param( 'priorities' );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error(
				'invalid_taxonomy',
				__( 'Invalid taxonomy.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		if ( ! is_array( $priorities ) && ! is_object( $priorities ) ) {
			return new WP_Error(
				'invalid_payload',
				__( 'Priorities must be an object of term_id => priority.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		// Convert object to array if needed
		$priorities = (array) $priorities;

		$updated = [];
		$errors  = [];

		foreach ( $priorities as $term_id => $priority ) {
			$term_id  = (int) $term_id;
			$priority = max( 0, (int) $priority );

			// Get term and verify it exists
			$term = get_term( $term_id, $taxonomy );

			if ( is_wp_error( $term ) || ! $term ) {
				$errors[] = [
					'term_id' => $term_id,
					'message' => __( 'Term not found.', 'easy-content-manager' ),
				];
				continue;
			}

			// Verify term belongs to the specified taxonomy
			if ( $term->taxonomy !== $taxonomy ) {
				$errors[] = [
					'term_id' => $term_id,
					'message' => __( 'Term does not belong to specified taxonomy.', 'easy-content-manager' ),
				];
				continue;
			}

			// Check if user can edit this term
			$taxonomy_obj = get_taxonomy( $taxonomy );
			$cap = isset( $taxonomy_obj->cap->edit_terms ) ? $taxonomy_obj->cap->edit_terms : 'manage_categories';

			if ( ! current_user_can( $cap ) ) {
				$errors[] = [
					'term_id' => $term_id,
					'message' => __( 'Permission denied.', 'easy-content-manager' ),
				];
				continue;
			}

			// Update term meta with priority
			$result = update_term_meta( $term_id, self::PRIORITY_META_KEY, $priority );

            // @phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( false === $result && get_term_meta( $term_id, self::PRIORITY_META_KEY, true ) != $priority ) {
				$errors[] = [
					'term_id' => $term_id,
					'message' => __( 'Failed to update priority.', 'easy-content-manager' ),
				];
				continue;
			}

			$updated[] = [
				'id'       => $term_id,
				'name'     => $term->name,
				'slug'     => $term->slug,
				'taxonomy' => $term->taxonomy,
				'priority' => $priority,
			];
		}//end foreach

		$response_data = [
			'updated' => $updated,
		];

		if ( ! empty( $errors ) ) {
			$response_data['errors'] = $errors;
		}

		return new WP_REST_Response( $response_data );
	}

	private function has_term_priorities( $taxonomy ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT tm.term_id 
            FROM {$wpdb->termmeta} tm
            INNER JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id
            WHERE tt.taxonomy = %s 
            AND tm.meta_key = %s 
            AND CAST(tm.meta_value AS UNSIGNED) > 0
            LIMIT 1",
			$taxonomy,
			self::PRIORITY_META_KEY
		);

        // @phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( $query );

		return ! empty( $result );
	}

	private function prepare_term_response( $term ) {
		$priority = (int) get_term_meta( $term->term_id, self::PRIORITY_META_KEY, true );

		return [
			'id'          => (int) $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'taxonomy'    => $term->taxonomy,
			'description' => $term->description,
			'parent'      => (int) $term->parent,
			'count'       => (int) $term->count,
			'priority'    => $priority,
		];
	}
}
