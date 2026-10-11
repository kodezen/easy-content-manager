<?php

namespace ECMReactions\Admin;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin extends AbstractAjaxHandler {
    public function __construct() {
        $this->actions = [
            'get_reactioned_users' => [
                'callback'   => [ $this, 'get_reactioned_users' ],
                'capability' => 'manage_options',
                'fields'     => [
                    'post_id' => 'absint',
                    'page'    => 'absint',
                    'per_page'=> 'absint',
                    'search'  => 'string',
                ],
            ],
        ];
    }
    public function get_reactioned_users( array $payload ) {
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

        // Reactions are stored as `reaction_type => [user_ids]`; flatten to a
        // unique user => reaction map for the admin listing.
        $reactions = get_post_meta( $post_id, '_ecm_reactions', true );
        $reactions = is_array( $reactions ) ? $reactions : [];

        $reactioners = [];
        foreach ( $reactions as $reaction_type => $user_ids ) {
            foreach ( (array) $user_ids as $uid ) {
                if ( ! isset( $reactioners[ $uid ] ) ) {
                    $reactioners[ $uid ] = $reaction_type;
                }
            }
        }

        $results = [];

        foreach ( $reactioners as $user_id => $reaction_type ) {
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
                'reaction'      => $reaction_type,
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

        return [
            'data'       => array_values($paged),
            'pagination' => [
                'page'       => $page,
                'per_page'   => $per_page,
                'total'      => $total,
                'total_page' => $total_pages,
            ],
        ];
    }
}