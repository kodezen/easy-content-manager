<?php

namespace ECMReviewAndRatings\Admin\Api;

use EasyContentManager\Utils\Helper;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ECMReviewAndRatings\Admin\ReviewDisplay;

if ( ! defined( 'ABSPATH' ) ) exit;

class ReviewAdminApi extends WP_REST_Controller {

    private $current_search_term;

    public function __construct() {
        $this->namespace = EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/v1';
        $this->rest_base = 'reviews';

        add_action('rest_api_init', [ $this, 'register_routes' ]);
    }

    public function register_routes() {

        /**
         * GET /reviews
         * POST /reviews
         */
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
                'schema' => [ $this, 'get_public_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'id' => [
                            'description' => 'Review ID',
                            'type'        => 'integer',
                            'required'    => true,
                        ]
                    ],
                ],
                [
                    'methods'             => ['PUT', 'PATCH'],
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'force' => [
                            'default'     => false,
                            'description' => 'Whether to bypass trash and force deletion.',
                        ],
                    ],
                ],
                'schema' => [ $this, 'get_public_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)/approve',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'approve_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                'schema' => [ $this, 'get_action_schema' ],
            ]
        );
    }

    public function permissions_check() {
        return current_user_can('manage_options');
    }

    public function get_items( $request ) {
        $page     = max(1, intval($request->get_param('page')));
        $per_page = max(1, intval($request->get_param('per_page')));
        $offset   = ($page - 1) * $per_page;

        $status_param = $request->get_param('status');
        $status = 'all';

        if ( isset( $status_param ) && $status_param !== '' ) {
            if ( in_array( $status_param, ['1', 'approve', 'true'], true ) ) {
                $status = 'approve';
            } elseif ( in_array( $status_param, ['0', 'hold', 'pending', 'false'], true ) ) {
                $status = 'hold';
            } else {
                $status = $status_param;
            }
        }

        $search = $request->get_param('search');

        $args = [
            'type'   => 'ecm_review',
            'status' => $status,
            'number' => $per_page,
            'offset' => $offset,
        ];

        $count_args = [ 'type' => 'ecm_review', 'status' => $status, 'count' => true ];

        if ( $search ) {
            $this->current_search_term = $search;
            add_filter( 'comments_clauses', [ $this, 'custom_search_clauses' ] );
        }

        $comments = get_comments($args);
        $total    = get_comments($count_args);

        if ( $search ) {
            remove_filter( 'comments_clauses', [ $this, 'custom_search_clauses' ] );
            $this->current_search_term = null;
        }
        
        $total_pages = ceil($total / $per_page);

        $data = [];

        foreach ($comments as $comment) {
            $item = $this->prepare_item_for_response($comment, $request);
            $data[] = $this->prepare_response_for_collection($item);
        }

        $response = rest_ensure_response($data);
        $response->header('X-WP-Total', $total);
        $response->header('X-WP-TotalPages', $total_pages);

        return $response;
    }

    public function get_item( $request ) {

        $id = intval($request['id']);
        $comment = get_comment($id);

        if ( ! $comment ) {
            return new WP_Error('not_found', 'Review not found', ['status' => 404]);
        }

        return rest_ensure_response(
            $this->prepare_item_for_response($comment, $request)
        );
    }

    public function update_item( $request ) {
        $id = intval($request['id']);
        $comment = get_comment($id);

        if ( ! $comment ) {
            return new WP_Error('not_found', 'Review not found', ['status' => 404]);
        }

        $comment_data = ['comment_ID' => $id];
        $update_needed = false;

        if ( isset($request['content']) ) {
            $comment_data['comment_content'] = sanitize_textarea_field($request['content']);
            $update_needed = true;
        }

        if ( isset($request['author']) ) {
            $comment_data['comment_author'] = sanitize_text_field($request['author']);
            $update_needed = true;
        }

        if ( isset($request['email']) ) {
            $comment_data['comment_author_email'] = sanitize_email($request['email']);
            $update_needed = true;
        }

        if ( isset($request['status']) ) {
             // Map status string/int to WP comment status if needed, assuming int/string match for now
             // 'approve', 'hold', 'spam', 'trash', or 1, 0, 'spam', 'trash'
             $comment_data['comment_approved'] = $request['status'];
             $update_needed = true;
        }

        if ( $update_needed ) {
            wp_update_comment($comment_data);
        }

        if ( isset($request['rating']) ) {
            update_comment_meta($id, '_ecm_rating', intval($request['rating']));
        }

        if ( isset($request['title']) ) {
            update_comment_meta($id, '_ecm_review_title', sanitize_text_field($request['title']));
        }

        // Recalculate rating for the post
        ReviewDisplay::recalculate_post_rating($comment->comment_post_ID);

        $updated_comment = get_comment($id);
        return rest_ensure_response(
            $this->prepare_item_for_response($updated_comment, $request)
        );
    }

    public function approve_item( $request ) {

        $id = intval($request['id']);
        $comment = get_comment($id);

        if ( ! $comment ) {
            return new WP_Error('not_found', 'Review not found', ['status' => 404]);
        }

        wp_set_comment_status($id, 'approve');

        ReviewDisplay::recalculate_post_rating($comment->comment_post_ID);

        return rest_ensure_response([
            'success' => true,
            'message' => __( 'Review approved successfully.', 'easy-content-manager' ),
        ]);
    }

    public function delete_item( $request ) {

        $id = intval($request['id']);
        $force = isset($request['force']) ? (bool) $request['force'] : false;
        $comment = get_comment($id);

        if ( ! $comment ) {
            return new WP_Error('not_found', 'Review not found', ['status' => 404]);
        }

        if ( $force ) {
            $result = wp_delete_comment($id, true);
            $message = 'Review permanently deleted.';
        } else {
            $result = wp_trash_comment($id);
            $message = 'Review moved to trash.';
        }

        if ( ! $result ) {
             return new WP_Error('delete_failed', 'Failed to delete review', ['status' => 500]);
        }

        ReviewDisplay::recalculate_post_rating($comment->comment_post_ID);

        return rest_ensure_response([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function prepare_item_for_response( $comment, $request ) {

        $schema = $this->get_item_schema();
        $data   = [];

        // ID
        if ( isset( $schema['properties']['id'] ) ) {
            $data['id'] = (int) $comment->comment_ID;
        }

        // Post ID
        if ( isset( $schema['properties']['post_id'] ) ) {
            $data['post_id'] = (int) $comment->comment_post_ID;
        }

        // Post Title
        if ( isset( $schema['properties']['post_title'] ) ) {
            $data['post_title'] = (string) get_the_title( $comment->comment_post_ID );
        }

        // Author name
        if ( isset( $schema['properties']['author'] ) ) {
            $data['author'] = (string) $comment->comment_author;
        }

        // Author email
        if ( isset( $schema['properties']['email'] ) ) {
            $data['email'] = (string) $comment->comment_author_email;
        }

        // Content
        if ( isset( $schema['properties']['content'] ) ) {
            $data['content'] = (string) $comment->comment_content;
        }

        // Rating
        if ( isset( $schema['properties']['rating'] ) ) {
            $data['rating'] = (int) get_comment_meta( $comment->comment_ID, '_ecm_rating', true );
        }

        // Review title
        if ( isset( $schema['properties']['title'] ) ) {
            $data['title'] = (string) get_comment_meta( $comment->comment_ID, '_ecm_review_title', true );
        }

        // Status (approved/pending/spam)
        if ( isset( $schema['properties']['status'] ) ) {
            $data['status'] = (integer) $comment->comment_approved;
        }

        // Date
        if ( isset( $schema['properties']['date'] ) ) {
            $data['date'] = (string) $comment->comment_date;
        }

        return rest_ensure_response( $data );
    }

    public function get_public_item_schema() {
        return $this->get_item_schema();
    }

    public function get_item_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title'   => 'ecm_review',
            'type'    => 'object',
            'properties' => [
                'id'        => ['type' => 'integer'],
                'post_id'   => ['type' => 'integer'],
                'post_title'=> ['type' => 'string'],
                'author'    => ['type' => 'string'],
                'email'     => ['type' => 'string'],
                'content'   => ['type' => 'string'],
                'rating'    => ['type' => 'integer'],
                'title'     => ['type' => 'string'],
                'status'    => ['type' => 'integer'],
                'date'      => ['type' => 'string'],
            ]
        ];
    }

    public function get_action_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title'   => 'ecm_action_response',
            'type'    => 'object',
            'properties' => [
                'success' => ['type' => 'boolean'],
                'message' => ['type' => 'string'],
            ]
        ];
    }

    public function get_collection_params() {
        return [
            'page' => [
                'description' => 'Current page number.',
                'type'        => 'integer',
                'default'     => 1,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'description' => 'Items per page.',
                'type'        => 'integer',
                'default'     => 20,
                'sanitize_callback' => 'absint',
            ],
            'status' => [
                'description' => 'Limit result set to reviews assigned to specific statuses.',
                'type'        => 'string',
                'default'     => 'all',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'search' => [
                'description' => 'Limit results to those matching a string.',
                'type'        => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
    public function custom_search_clauses( $clauses ) {
        global $wpdb;

        if ( ! $this->current_search_term ) {
            return $clauses;
        }

        // Left join to search title
        $clauses['join'] .= " LEFT JOIN {$wpdb->commentmeta} AS ecm_cm ON ({$wpdb->comments}.comment_ID = ecm_cm.comment_id AND ecm_cm.meta_key = '_ecm_review_title') ";

        $words = explode( ' ', $this->current_search_term );
        $search_sql = [];

        foreach ( $words as $word ) {
            $word = $wpdb->esc_like( $word );
            $search_sql[] = $wpdb->prepare(
                "(
                    {$wpdb->comments}.comment_content LIKE %s
                    OR {$wpdb->comments}.comment_author LIKE %s
                    OR ecm_cm.meta_value LIKE %s
                )",
                '%' . $word . '%',
                '%' . $word . '%',
                '%' . $word . '%'
            );
        }

        if ( ! empty( $search_sql ) ) {
            $clauses['where'] .= " AND (" . implode( " AND ", $search_sql ) . ")";
        }

        return $clauses;
    }
}
