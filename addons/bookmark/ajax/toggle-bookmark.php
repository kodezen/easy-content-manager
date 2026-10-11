<?php
namespace ECMBookmark\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ToggleBookmark extends AbstractAjaxHandler {
    protected string $namespace = EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/bookmark';

    public function __construct() {
        $this->actions = [
            'toggle' => [
                'callback' => [$this, 'toggle_bookmark'],
                'fields'   => [
                    'post_id' => 'int',
                ],
                'allow_visitor_action' => false,
                'only_logged_in'       => true,
            ],
        ];
    }

    public function toggle_bookmark( $data ) {
        $post_id = $data['post_id'];
        $user_id = get_current_user_id();

        if ( ! $post_id ) {
            return new \WP_Error( 'missing_post_id', 'Post ID is required.', [ 'status' => 400 ] );
        }

        if( ! Helper::is_addon_enabled_for_post_type('bookmark', get_post_type($post_id)) ) {
            wp_send_json_error([
                'message' => __( 'Bookmarking is not enabled for this post type.', 'easy-content-manager' ),
            ]);
        }

        // Bookmarking a post you cannot even read (a draft, a private post) is not
        // something the UI ever offers — but the endpoint took any ID.
        if ( ! current_user_can( 'read_post', $post_id ) ) {
            wp_send_json_error([
                'message' => __( 'You do not have permission to bookmark this item.', 'easy-content-manager' ),
            ]);
        }

        // Get User Bookmarks (Array of Post IDs)
        $user_bookmarks = maybe_unserialize(get_user_meta( $user_id, 'ecm_user_bookmarks', true ));
        if ( ! is_array( $user_bookmarks ) ) {
            $user_bookmarks = [];
        }

        // Get Post Bookmarkers (Array of User IDs)
        $post_users = maybe_unserialize(get_post_meta( $post_id, 'ecm_bookmark_users', true ));
        if ( ! is_array( $post_users ) ) {
            $post_users = [];
        }

        $is_bookmarked = in_array( $post_id, $user_bookmarks );

        if ( $is_bookmarked ) {
            // Remove
            $user_bookmarks = array_diff( $user_bookmarks, [ $post_id ] );
            $post_users = array_diff( $post_users, [ $user_id ] );
            
            update_user_meta( $user_id, 'ecm_user_bookmarks', maybe_serialize(array_values( $user_bookmarks ) ) );
            update_post_meta( $post_id, 'ecm_bookmark_users', maybe_serialize(array_values( $post_users ) ) );
            
            wp_send_json_success(['bookmarked' => false, 'message' => __( 'Bookmark removed.', 'easy-content-manager' ), 'post_id' => $post_id ]);
        } else {
            if ( ! in_array( $post_id, $user_bookmarks ) ) {
                $user_bookmarks[] = $post_id;
            }
            if ( ! in_array( $user_id, $post_users ) ) {
                $post_users[] = $user_id;
            }

            update_user_meta( $user_id, 'ecm_user_bookmarks', maybe_serialize($user_bookmarks) );
            update_post_meta( $post_id, 'ecm_bookmark_users', maybe_serialize($post_users) );
            
            wp_send_json_success([ 'bookmarked' => true, 'message' => __( 'Post bookmarked.', 'easy-content-manager' ), 'post_id' => $post_id ]);
        }
    }
}
