<?php
namespace ECMUpvote\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class UpvoteShortcode {

    public function __construct() {
        add_shortcode( 'ecm_upvote', [ $this, 'render_shortcode' ] );
        add_shortcode( 'ecm_upvote_count', [ $this, 'render_upvote_count' ] );
        add_shortcode( 'ecm_upvote_list', [ $this, 'render_upvote_list' ] );
    }

    public function render_shortcode( $atts = [], $content = '' ) {

        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'text' => '',
        ], $atts, 'ecm_upvote' );

        $upvote_post_id = intval( $atts['post_id'] );

        if( ! $upvote_post_id ) {
            return '';
        }

        if ( ! Helper::is_addon_enabled_for_post_type( 'upvote', get_post_type( $upvote_post_id ) ) ) {
            return '';
        }

        $nonce = wp_create_nonce('easy_content_manager_nonce');
        $count = intval(get_post_meta($upvote_post_id, 'ecm_upvote_count', true));
        $is_upvoted = false;

        if( is_user_logged_in() ) {
            $is_upvoted = $this->is_current_user_upvoted( $upvote_post_id );
        } else {
            $is_upvoted = $this->is_current_guest_upvoted( $upvote_post_id );
        }

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template('addons/upvote/upvote-button.php', [
            'upvote_post_id' => $upvote_post_id,
            'count' => $count,
            'nonce' => $nonce,
            'is_upvoted' => $is_upvoted,
            'text' => $atts['text'],
        ]);
        return ob_get_clean();
    }

    public function render_upvote_count( $atts = [] ) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'show_label' => 'false',
            'show_icon' => 'false',
            'label_single' => esc_html__( 'upvote', 'easy-content-manager' ),
            'label_plural' => esc_html__( 'upvotes', 'easy-content-manager' ),
            'wrapper_class' => 'ecm-upvote-count',
        ], $atts );

        $post_id = intval( $atts['post_id'] );
        if ( ! $post_id ) return '';

        $count = intval( get_post_meta( $post_id, 'ecm_upvote_count', true ) );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/upvote/upvote-count.php', [
            'count' => $count,
            'wrapper_class' => $atts['wrapper_class'],
            'show_label' => 'true' === $atts['show_label'],
            'show_icon' => 'true' === $atts['show_icon'],
            'label_single' => $atts['label_single'],
            'label_plural' => $atts['label_plural'],
        ]);
        return ob_get_clean();
    }

    public function render_upvote_list( $atts = [] ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Please login to view upvoted posts.', 'easy-content-manager' );
        }

        $atts = shortcode_atts([
            'user_id' => get_current_user_id(),
            'not_found_text' => esc_html__( 'No upvoted posts found.', 'easy-content-manager' ),
        ], $atts );

        $user_id = intval( $atts['user_id'] );

        $upvoted_posts = get_user_meta( $user_id, '_ecm_upvoted_posts', true );
        $upvoted_posts = is_array( $upvoted_posts ) ? $upvoted_posts : [];

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/upvote/upvote-list.php', [
            'upvoted_posts' => $upvoted_posts,
            'not_found_text' => $atts['not_found_text'],
        ]);
        return ob_get_clean();
    }

    private function is_current_guest_upvoted( $upvoted_post_id ) {
        $cookie_name = ECM_UPVOTE_COOKIE_NAME;

        $guest_upvotes = isset($_COOKIE[$cookie_name])
            ? json_decode( wp_unslash( $_COOKIE[$cookie_name] ), true )
            : [];

        $guest_upvotes = is_array($guest_upvotes) ? $guest_upvotes : [];

        if ( in_array( $upvoted_post_id, $guest_upvotes, true ) ) {
            return true;
        }
    
        return false;
    }

    private function is_current_user_upvoted( $upvoted_post_id ) {
        $user_id = get_current_user_id();

        $upvoters = get_post_meta( $upvoted_post_id, '_ecm_upvoted_users', true );
        $upvoters = is_array( $upvoters ) ? $upvoters : [];

        if ( in_array( $user_id, $upvoters, true ) ) {
            return true;
        }

        return false;
    }
}
