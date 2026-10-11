<?php
namespace ECMClaim\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class ClaimShortcode {
    public function __construct() {
        add_shortcode( 'ecm_claim_button', [ $this, 'render_claim_button' ] );
        add_shortcode( 'ecm_claim_badge', [ $this, 'render_claim_badge' ] );
        add_shortcode( 'ecm_claim_list', [ $this, 'render_claim_list' ] );
        add_shortcode( 'ecm_claim_count', [ $this, 'render_claim_count' ] );
    }

    public function render_claim_button( $atts ) {
        $atts = shortcode_atts( [
            'post_id' => get_the_ID(),
            'text'    => '',
        ], $atts );

        $post_id = intval( $atts['post_id'] );
        if ( ! $post_id ) return '';

        // Check if claimed
        global $wpdb;
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
        $is_claimed = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE post_id = %d AND status = 'approved'", $post_id ) );
        
        $already_apply = false;
        
        if( is_user_logged_in() ) {
            $already_apply = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE user_id = %d AND post_id = %d AND status = 'pending'", get_current_user_id(), $post_id ) );
        }

        do_action( 'ecm_shortcode_loaded');

        // Output claim button/form template
        ob_start();
        Helper::get_template('addons/claim/claim-button.php', [
            'is_claimed' => $is_claimed,
            'already_apply' => $already_apply,
            'text' => $atts['text'],
        ]);
        return ob_get_clean();
    }

    public function render_claim_badge( $atts ) {
        $atts = shortcode_atts( [
            'post_id' => get_the_ID(),
            'text'    => '',
        ], $atts );

        $post_id = intval( $atts['post_id'] );
        if ( ! $post_id ) return '';

        global $wpdb;
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
        $is_claimed = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE post_id = %d AND status = 'approved'", $post_id ) );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template('addons/claim/claim-badge.php', [
            'is_claimed' => $is_claimed,
            'text' => $atts['text'],
        ]);
        return ob_get_clean();
    }

    public function render_claim_list( $atts ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Please login to view claims.', 'easy-content-manager' );
        }
        
        $atts = shortcode_atts( [
            'user_id' => get_current_user_id(),
            'status' => 'approved',
            'not_found_text' => esc_html__( 'No claims found.', 'easy-content-manager' ),
        ], $atts );

        $user_id = intval( $atts['user_id'] );
        
        global $wpdb;
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
        
        $claims = $wpdb->get_results( $wpdb->prepare( 
            "SELECT * FROM $table WHERE user_id = %d AND status = %s ORDER BY created_at DESC", 
            $user_id, 
            $atts['status'] 
        ) );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/claim/claim-list.php', [
            'claims' => $claims,
            'not_found_text' => $atts['not_found_text'],
        ]);
        return ob_get_clean();
    }

    public function render_claim_count( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '0';
        }

        $atts = shortcode_atts( [
            'user_id'       => get_current_user_id(),
            'status'        => 'approved',
            'show_label'    => 'false',
            'show_icon'     => 'false',
            'label_single'  => esc_html__( 'claim', 'easy-content-manager' ),
            'label_plural'  => esc_html__( 'claims', 'easy-content-manager' ),
            'wrapper_class' => 'ecm-claim-count',
        ], $atts );

        $user_id = intval( $atts['user_id'] );
        
        global $wpdb;
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
        
        $count = $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND status = %s", 
            $user_id, 
            $atts['status'] 
        ) );
        
        $count = intval( $count );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/claim/claim-count.php', [
            'count'         => $count,
            'wrapper_class' => $atts['wrapper_class'],
            'show_label'    => 'true' === $atts['show_label'],
            'show_icon'     => 'true' === $atts['show_icon'],
            'label_single'  => $atts['label_single'],
            'label_plural'  => $atts['label_plural'],
        ]);
        return ob_get_clean();
    }
}
