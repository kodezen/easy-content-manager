<?php
namespace ECMUpvote\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) exit;

class UpvoteHandler extends AbstractAjaxHandler {

    public function __construct() {

        $this->actions = [
            'submit_upvote' => [
                'callback'             => [ $this, 'process_upvote' ],
                'fields'               => [
                    'post_id' => 'absint',
                ],
                'allow_visitor_action' => true,
            ],
        ];

        $this->dispatch_actions();
    }


    public function process_upvote( array $payload ) {

        $post_id = absint( $payload['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error([ 'message' => __( 'Invalid post.', 'easy-content-manager' ) ]);
        }

        if ( ! Helper::is_addon_enabled_for_post_type( 'upvote', get_post_type( $post_id ) ) ) {
            wp_send_json_error([
                'message' => __( 'Upvotes are not enabled for this post type.', 'easy-content-manager' ),
            ]);
        }

        // Upvoting a post you cannot even read (a draft, a private post) is not
        // something the UI ever offers — but the endpoint took any ID.
        if ( ! current_user_can( 'read_post', $post_id ) ) {
            wp_send_json_error([ 'message' => __( 'You do not have permission to upvote this item.', 'easy-content-manager' ) ]);
        }

        $upvote_settings  = Helper::get_settings('upvote_addon', []);
        $allow_anonymous  = ! empty( $upvote_settings->enable_anonymous_upvote );

        $count = (int) get_post_meta( $post_id, 'ecm_upvote_count', true );
        $count = max( 0, $count );

        $toggled = 'added';

        if ( is_user_logged_in() ) {
            $user_id  = get_current_user_id();
            
            $upvoters = get_post_meta( $post_id, '_ecm_upvoted_users', true );
            $upvoters = is_array( $upvoters ) ? $upvoters : [];
            
            $user_upvotes = get_user_meta( $user_id, '_ecm_upvoted_posts', true );
            $user_upvotes = is_array( $user_upvotes ) ? $user_upvotes : [];
            
            if ( in_array( $user_id, $upvoters, true ) ) {
                $upvoters = array_values( array_diff( $upvoters, [ $user_id ] ) );
                $user_upvotes = array_values( array_diff( $user_upvotes, [ $post_id ] ) );
                $count--;
                $toggled = 'removed';
            } else {
                $upvoters[] = $user_id;
                $user_upvotes[] = $post_id;
                $count++;
            }
            
            update_post_meta( $post_id, '_ecm_upvoted_users', $upvoters );
            update_user_meta( $user_id, '_ecm_upvoted_posts', $user_upvotes );
        } elseif ( $allow_anonymous ) {

            $cookie_name   = ECM_UPVOTE_COOKIE_NAME;
            $guest_upvotes = isset( $_COOKIE[ $cookie_name ] )
                ? json_decode( wp_unslash( $_COOKIE[ $cookie_name ] ), true )
                : [];

            $guest_upvotes = is_array( $guest_upvotes ) ? $guest_upvotes : [];

            // Server-side dedup so rotating/clearing the client cookie can't inflate
            // the count. Keyed by IP + post, independent of the cookie state.
            //
            // The window is deliberately short-lived: one row per IP x post that never
            // expires would grow wp_options without bound, and shared addresses (office
            // NAT, CGNAT, mobile carriers) would lock out unrelated visitors for as long
            // as it lived. A day stops burst abuse without either cost.
            $ip     = Helper::get_client_ip();
            $ip_key = 'ecm_upvote_ip_' . md5( $ip . '_' . $post_id );
            $ip_ttl = (int) apply_filters( 'easy_content_manager/upvote/guest_dedup_ttl', DAY_IN_SECONDS, $post_id );

            if ( in_array( $post_id, $guest_upvotes, true ) ) {
                $guest_upvotes = array_values( array_diff( $guest_upvotes, [ $post_id ] ) );
                $count--;
                $toggled = 'removed';
                delete_transient( $ip_key );
            } else {
                if ( get_transient( $ip_key ) ) {
                    wp_send_json_error([ 'message' => __( 'You have already upvoted this.', 'easy-content-manager' ) ]);
                }
                $guest_upvotes[] = $post_id;
                $count++;
                set_transient( $ip_key, 1, $ip_ttl );
            }

            setcookie(
                $cookie_name,
                wp_json_encode( $guest_upvotes ),
                time() + ( YEAR_IN_SECONDS * 5 ),
                COOKIEPATH,
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        } else {
            wp_send_json_error([ 'message' => __( 'You must be logged in to upvote.', 'easy-content-manager' ) ]);
        }

        $count = max( 0, $count );
        update_post_meta( $post_id, 'ecm_upvote_count', $count );

        wp_send_json_success([
            'upvote_count' => $count,
            'status'       => $toggled,
        ]);
    }

}
