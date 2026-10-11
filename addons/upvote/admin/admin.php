<?php

namespace ECMUpvote\Admin;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin extends AbstractAjaxHandler {
    public function __construct() {
        $this->actions = [
            'get_upvoted_users' => [
                'callback'   => [ $this, 'get_upvoted_users' ],
                'capability' => 'manage_options',
                'fields'     => [
                    'post_id' => 'absint',
                    'page'    => 'absint',
                    'per_page'=> 'absint',
                    'search'  => 'string',
                ],
            ],
            'manage_anonymous_upvote' => [
                'callback'   => [ $this, 'manage_anonymous_upvote' ],
                'capability' => 'manage_options',
                'fields'     => [
                    'enable_anonymous_upvote' => 'boolean',
                ],
            ],
        ];
    }
    public function get_upvoted_users( array $payload ) {
        $post_id = isset($payload['post_id']) ? absint($payload['post_id']) : 0;
        if ( ! $post_id ) {
            return [
                'data'       => [],
                'pagination' => [
                    'page'       => 1,
                    'per_page'   => 0,
                    'total'      => 0,
                    'total_page' => 0,
                ],
            ];
        }

        
        $page     = isset($payload['page']) && $payload['page'] > 0 ? (int) $payload['page'] : 1;
        $per_page = isset($payload['per_page']) && $payload['per_page'] > 0 ? (int) $payload['per_page'] : 10;
        $search   = isset($payload['search']) ? sanitize_text_field($payload['search']) : '';

        $upvoters = get_post_meta( $post_id, '_ecm_upvoted_users', true );
        $upvoters = is_array( $upvoters ) ? $upvoters : [];

        $results = [];

        foreach ( $upvoters as $user_id ) {
            $user_info = get_userdata( $user_id );
            if ( ! $user_info ) {
                continue;
            }

            $entry = [
                'post_id'       => $post_id,
                'user_id'       => $user_info->ID,
                'user_name'     => $user_info->user_login,
                'user_email'    => $user_info->user_email,
                'display_name'  => $user_info->display_name,
                'avatar'        => get_avatar_url( $user_info->ID ),
            ];

            if ( $search ) {
                $haystack = strtolower( implode( ' ', [
                    $entry['user_name'],
                    $entry['display_name'],
                    $entry['user_email'],
                ] ) );
                if ( strpos( $haystack, strtolower( $search ) ) === false ) {
                    continue;
                }
            }

            $results[] = $entry;
        }

        $total       = count($results);
        $total_pages = $per_page > 0 ? ceil($total / $per_page) : 1;

        $offset  = ($page - 1) * $per_page;
        $paged   = array_slice($results, $offset, $per_page);

        $anonymous_users = max(0, intval(get_post_meta( $post_id, 'ecm_upvote_count', true )) - $total);

        return [
            'data'       => array_values($paged),
            'anonymous_users' => $anonymous_users,
            'pagination' => [
                'page'       => $page,
                'per_page'   => $per_page,
                'total'      => $total,
                'total_page' => $total_pages,
            ],
        ];
    }

    public function manage_anonymous_upvote($payload){
        $anonymous_upvote = isset($payload['enable_anonymous_upvote']) ? boolval($payload['enable_anonymous_upvote']) : false;
        update_option('ecm_upvote_anonymous_upvote', $anonymous_upvote);

        wp_send_json_success([
            'enable_anonymous_upvote' => $anonymous_upvote
        ]); 
    }
}