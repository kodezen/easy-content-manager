<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FrontendSubmission extends AbstractAjaxHandler {

	public function __construct() {
		// When anonymous submission is switched on, saving must be reachable by
		// visitors; listing "my submissions" still requires an account either way.
		$save_gate = self::is_anonymous_submission_enabled()
			? [ 'allow_visitor_action' => true ]
			: [ 'only_logged_in' => true ];

		$this->actions = [
			'get_user_posts' => [
				'callback'       => [ $this, 'get_user_posts' ],
				'only_logged_in' => true,
				'fields'   => [
					'post_type' => 'string',
					'page'      => 'integer',
					'per_page'  => 'integer',
					'orderby'   => 'string',
					'order'     => 'string',
				],
			],
			'save_user_post' => $save_gate + [
				'callback'       => [ $this, 'save_user_post' ],
				'fields'   => [
					'post_id'        => 'integer',
					'post_type'      => 'string',
					'post_title'     => 'string',
					// `post` (wp_kses_post), not `string`: sanitize_text_field() would
					// strip every tag and line break here, so the wp_kses_post() call
					// in save_user_post() only ever saw already-flattened text and no
					// submission could contain formatting.
					'post_content'   => 'post',
					'post_excerpt'   => 'textarea',
					'meta'           => 'array|string',
					'categories'     => 'array|string',
					'tags'           => 'array|string',
					'featured_media' => 'integer',
				],
			],
		];
	}

	/**
	 * Whether the front-end submission addon is configured to accept posts from
	 * visitors who are not logged in.
	 *
	 * @return bool
	 */
	private static function is_anonymous_submission_enabled(): bool {
		$settings = Helper::get_frontend_submission_shortcode_settings();

		return ! empty( $settings['enable_anonymous_submission'] );
	}

	/**
	 * Role gate for logged-in submitters. Administrators always pass.
	 *
	 * @param array $settings
	 * @return void
	 */
	private function require_submitter_role( array $settings ) {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$roles         = (array) wp_get_current_user()->roles;
		$allowed_roles = (array) ( $settings['user_roles'] ?? [] );

		if ( ! array_intersect( $roles, $allowed_roles ) ) {
			wp_send_json_error( [ 'message' => __( 'You do not have permission.', 'easy-content-manager' ) ], 403 );
		}
	}

	public function get_user_posts( $payload ) {
		$settings = Helper::get_frontend_submission_shortcode_settings();

		$administration = current_user_can( 'manage_options' );

		// Listing a user's own submissions always requires an account, even when
		// anonymous submission is enabled.
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'easy-content-manager' ) ], 401 );
		}

		if ( ! self::is_anonymous_submission_enabled() ) {
			$this->require_submitter_role( $settings );
		}

		$post_type = sanitize_key( $payload['post_type'] ?? '' );

		if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'easy-content-manager' ) ], 400 );
		}

		if( ! in_array( $post_type, (array) ( $settings['specific_post_types'] ?? [] ), true ) ) {
			wp_send_json_error( [ 'message' => __( 'Post type not allowed.', 'easy-content-manager' ) ], 403 );
		}

		$page     = max( 1, intval( $payload['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, intval( $payload['per_page'] ?? 10 ) ) );
		$orderby  = sanitize_key( $payload['orderby'] ?? 'date' );
		$order    = strtoupper( $payload['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC';

		$query = new WP_Query( [
			'post_type'      => $post_type,
			'post_status'    => [ 'publish', 'pending', 'draft' ],
			'author'         => $administration ? false : $user_id,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => $orderby,
			'order'          => $order,
		] );

		$posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();

				$posts[] = $this->prepare_post_for_response( $post, $post_type );
			}
			wp_reset_postdata();
		}

		wp_send_json_success( [
			'posts'      => $posts,
			'pagination' => [
				'current_page' => $page,
				'per_page'     => $per_page,
				'total'        => (int) $query->found_posts,
				'total_pages'  => (int) $query->max_num_pages,
			],
		] );
	}

	/**
	 * Per-IP rate limit for submissions.
	 *
	 * With anonymous submission on, save_user_post() is registered on
	 * `wp_ajax_nopriv_` — an unauthenticated content-creation endpoint. The claim
	 * endpoint already throttles this way; this one did not, leaving it open to
	 * automated flooding. Users who can publish outright are exempt.
	 *
	 * @return void
	 */
	private function throttle_submission(): void {
		if ( current_user_can( 'publish_posts' ) ) {
			return;
		}

		$limit  = (int) apply_filters( 'easy_content_manager/frontend_submission/rate_limit', 10 );
		$window = (int) apply_filters( 'easy_content_manager/frontend_submission/rate_window', 10 * MINUTE_IN_SECONDS );

		if ( Helper::rate_limit_exceeded( 'ecm_fs_rl_', $limit, $window ) ) {
			wp_send_json_error(
				[ 'message' => __( 'Too many submissions. Please try again later.', 'easy-content-manager' ) ],
				429
			);
		}
	}

	public function save_user_post( $payload ) {
		$settings = Helper::get_frontend_submission_shortcode_settings();

		$user_id     = get_current_user_id();
		$is_anonymous = self::is_anonymous_submission_enabled();

		if ( ! $is_anonymous ) {
			if ( ! $user_id ) {
				wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'easy-content-manager' ) ], 401 );
			}

			$this->require_submitter_role( $settings );
		}

		$this->throttle_submission();

		$post_type = sanitize_key( $payload['post_type'] ?? '' );

		if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'easy-content-manager' ) ], 400 );
		}

		if( ! in_array( $post_type, (array) ( $settings['specific_post_types'] ?? [] ), true ) ) {
			wp_send_json_error( [ 'message' => __( 'Post type not allowed.', 'easy-content-manager' ) ], 403 );
		}

		$post_id    = intval( $payload['post_id'] ?? 0 );
		$post_title = sanitize_text_field( $payload['post_title'] ?? '' );

		// Anonymous submitters own nothing, so they may only create. Without this a
		// visitor could pass the ID of any existing author-less post and edit it.
		if ( ! $user_id && $post_id > 0 ) {
			wp_send_json_error( [ 'message' => __( 'You must be logged in to edit a submission.', 'easy-content-manager' ) ], 401 );
		}

		if ( empty( $post_title ) ) {
			wp_send_json_error( [ 'message' => __( 'Post title is required.', 'easy-content-manager' ) ], 400 );
		}

		$submission_status = sanitize_key( $settings['submission_status'] ?? 'draft' );
		if ( ! in_array( $submission_status, get_post_stati(), true ) ) {
			$submission_status = 'draft';
		}

		$post_data = [
			'post_title'   => $post_title,
			'post_content' => wp_kses_post( $payload['post_content'] ?? '' ),
			'post_excerpt' => sanitize_textarea_field( $payload['post_excerpt'] ?? '' ),
			'post_type'    => $post_type,
			'post_status'  => $submission_status,
			'post_author'  => $user_id,
		];

		if ( $post_id > 0 ) {
			$existing_post = get_post( $post_id );

			if ( ! $existing_post ) {
				wp_send_json_error( [ 'message' => __( 'Post not found.', 'easy-content-manager' ) ], 404 );
			}

			if ( (int) $existing_post->post_author !== $user_id && ! current_user_can( 'edit_others_posts' ) ) {
				wp_send_json_error( [ 'message' => __( 'You cannot edit this post.', 'easy-content-manager' ) ], 403 );
			}

			$post_data['ID'] = $post_id;
			$result = wp_update_post( $post_data, true );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ], 500 );
		}

		$saved_post_id = $result;

		if ( isset( $payload['featured_media'] ) ) {
			$this->handle_featured_media( $saved_post_id, $payload['featured_media'] );
		}

		if ( ! empty( $payload['meta'] ) && is_array( $payload['meta'] ) ) {
			$this->handle_meta_fields( $saved_post_id, $payload['meta'], $post_type );
		}

		if ( isset( $payload['categories'] ) ) {
			$this->handle_taxonomy_terms( $saved_post_id, $post_type, 'category', $payload['categories'] );
		}

		if ( isset( $payload['tags'] ) ) {
			$this->handle_taxonomy_terms( $saved_post_id, $post_type, 'post_tag', $payload['tags'] );
		}

		if ( ! empty( $payload['taxonomies'] ) && is_array( $payload['taxonomies'] ) ) {
			foreach ( $payload['taxonomies'] as $taxonomy => $terms ) {
				$this->handle_taxonomy_terms( $saved_post_id, $post_type, $taxonomy, $terms );
			}
		}

		$post = get_post( $saved_post_id );

		$response = $this->prepare_post_for_response( $post, $post_type );
		$response['is_new'] = $post_id === 0;

		wp_send_json_success( $response );
	}

	private function handle_featured_media( $post_id, $featured_media ) {
		$image_id = intval( $featured_media );

		if ( $image_id > 0 ) {
			$attachment = get_post( $image_id );

			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				return;
			}

			// Existence is not access. Without an ownership check any attachment ID
			// could be attached, and prepare_post_for_response() hands back its
			// `featured_media_url` — an enumeration oracle for private media. The
			// submitter must own the attachment, or already be able to edit it.
			$user_id = get_current_user_id();
			$owns    = $user_id && (int) $attachment->post_author === $user_id;

			if ( ! $owns && ! current_user_can( 'edit_post', $image_id ) ) {
				return;
			}

			set_post_thumbnail( $post_id, $image_id );
		} elseif ( $image_id === 0 ) {
			// Explicitly remove featured image
			delete_post_thumbnail( $post_id );
		}
	}

	/**
	 * Meta keys a front-end submission is allowed to write for this post type.
	 *
	 * Restricted to the keys ECM itself registered for the post type. Skipping
	 * underscore-prefixed keys is not enough on its own: it still let a submitter
	 * name any public meta key they liked, including keys other plugins read.
	 *
	 * @param string $post_type
	 * @return string[]
	 */
	private function allowed_meta_keys( $post_type ): array {
		$keys = array_keys( (array) get_registered_meta_keys( 'post', $post_type ) );

		/**
		 * Filters the meta keys writable through front-end submission.
		 *
		 * @param string[] $keys      Registered meta keys for this post type.
		 * @param string   $post_type Post type key.
		 */
		return (array) apply_filters( 'easy_content_manager/frontend_submission/allowed_meta_keys', $keys, $post_type );
	}

	private function handle_meta_fields( $post_id, $meta_data, $post_type ) {
		$allowed = $this->allowed_meta_keys( $post_type );

		if ( empty( $allowed ) ) {
			return;
		}

		foreach ( $meta_data as $key => $value ) {
			$key = sanitize_key( $key );

			if ( empty( $key ) || strpos( $key, '_' ) === 0 ) {
				continue;
			}

			if ( ! in_array( $key, $allowed, true ) || is_protected_meta( $key, 'post' ) ) {
				continue;
			}

			$sanitized_value = $this->sanitize_meta_value( $post_id, $key, $value, $post_type );

			update_post_meta( $post_id, $key, $sanitized_value );
		}
	}

	private function sanitize_meta_value( $post_id, $meta_key, $value, $post_type ) {
		$registered_meta = get_registered_meta_keys( 'post', $post_type );

		if ( isset( $registered_meta[ $meta_key ] ) && ! empty( $registered_meta[ $meta_key ]['sanitize_callback'] ) ) {
			$callback = $registered_meta[ $meta_key ]['sanitize_callback'];
			
			if ( is_callable( $callback ) ) {
				return call_user_func( $callback, $value, $meta_key, 'post', $post_type );
			}
		}

		if ( is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_meta_value_recursive' ], $value );
		}

		return $this->sanitize_meta_value_recursive( $value );
	}

	private function sanitize_meta_value_recursive( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_meta_value_recursive' ], $value );
		}

		if ( is_numeric( $value ) ) {
			return $value;
		}

		if ( is_bool( $value ) ) {
			return (bool) $value;
		}

		return wp_kses_post( $value );
	}

	private function handle_taxonomy_terms( $post_id, $post_type, $taxonomy, $terms ) {
		$taxonomy = sanitize_key( $taxonomy );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$post_type_taxonomies = get_object_taxonomies( $post_type );
		if ( ! in_array( $taxonomy, $post_type_taxonomies, true ) ) {
			return;
		}

		if ( ! is_array( $terms ) ) {
			$terms = [ $terms ];
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$terms = array_filter( array_map( 'intval', $terms ) );
		} else {
			$terms = array_filter( array_map( 'sanitize_text_field', $terms ) );
		}

		if ( ! empty( $terms ) ) {
			wp_set_object_terms( $post_id, $terms, $taxonomy );
		} else {
			wp_set_object_terms( $post_id, [], $taxonomy );
		}
	}

	private function prepare_post_for_response( $post, $post_type ) {
		$post_id = $post->ID;

		$featured_media = 0;
		$featured_media_url = null;
		if ( has_post_thumbnail( $post_id ) ) {
			$featured_media = get_post_thumbnail_id( $post_id );
			$featured_media_url = wp_get_attachment_url( $featured_media );
		}

		$categories = [];
		if ( post_type_supports( $post_type, 'category' ) || $post_type === 'post' ) {
			$post_categories = get_the_category( $post_id );
			if ( ! empty( $post_categories ) ) {
				$categories = array_map( function( $cat ) {
					return $cat->term_id;
				}, $post_categories );
			}
		}

		$tags = [];
		if ( post_type_supports( $post_type, 'post-tag' ) || $post_type === 'post' ) {
			$post_tags = get_the_tags( $post_id );
			if ( ! empty( $post_tags ) ) {
				$tags = array_map( function( $tag ) {
					return $tag->term_id;
				}, $post_tags );
			}
		}

		$meta = $this->get_post_meta_for_rest( $post_id );

		$response = [
			'id'             => $post_id,
			'date'           => mysql2date( 'c', $post->post_date, false ),
			'date_gmt'       => mysql2date( 'c', $post->post_date_gmt, false ),
			'modified'       => mysql2date( 'c', $post->post_modified, false ),
			'modified_gmt'   => mysql2date( 'c', $post->post_modified_gmt, false ),
			'slug'           => $post->post_name,
			'status'         => $post->post_status,
			'type'           => $post->post_type,
			'link'           => get_permalink( $post_id ),
			'title'          => [
				'rendered' => get_the_title( $post_id ),
				'raw'      => $post->post_title,
			],
			'content'        => [
				'rendered' => apply_filters( 'the_content', $post->post_content ),
				'raw'      => $post->post_content,
				'protected' => ! empty( $post->post_password ),
			],
			'excerpt'        => [
				'rendered' => apply_filters( 'the_excerpt', $post->post_excerpt ),
				'raw'      => $post->post_excerpt,
				'protected' => ! empty( $post->post_password ),
			],
			'author'         => (int) $post->post_author,
			'featured_media' => $featured_media,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'sticky'         => is_sticky( $post_id ),
			'template'       => get_page_template_slug( $post_id ),
			'format'         => get_post_format( $post_id ) ?: 'standard',
			'meta'           => $meta,
			'categories'     => $categories,
			'tags'           => $tags,
		];

		if ( $featured_media_url ) {
			$response['featured_media_url'] = $featured_media_url;
		}

		$response['taxonomies'] = $this->get_post_taxonomies( $post_id, $post_type );

		if ( current_user_can( 'edit_post', $post_id ) ) {
			$response['edit_link'] = get_edit_post_link( $post_id, 'raw' );
		}

		return $response;
	}

	private function get_post_meta_for_rest( $post_id ) {
		$meta = [];
		$all_meta = get_post_meta( $post_id );

		foreach ( $all_meta as $key => $values ) {
			// Skip protected meta fields (starting with _)
			if ( strpos( $key, '_' ) === 0 ) {
				continue;
			}

			// Get single value (most meta fields are single)
			$value = get_post_meta( $post_id, $key, true );
			$meta[ $key ] = $value;
		}

		return (object) $meta;
	}

	private function get_post_taxonomies( $post_id, $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		$result = [];

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'all' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$result[ $taxonomy ] = array_map( function ( $term ) {
					return [
						'term_id' => $term->term_id,
						'name'    => $term->name,
						'slug'    => $term->slug,
					];
				}, $terms );
			} else {
				$result[ $taxonomy ] = [];
			}
		}

		return $result;
	}
}
