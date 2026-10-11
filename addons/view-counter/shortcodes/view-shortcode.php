<?php
namespace ECMViewCounter\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ViewShortcode {

    public function __construct() {
        add_shortcode( 'ecm_view_counter', [ $this, 'render_shortcode' ] );
        add_shortcode( 'ecm_view_count', [ $this, 'render_view_count' ] );
    }

    public function render_shortcode( $atts = [] ) {

        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
        ], $atts );

        $post_id = intval( $atts['post_id'] );

        if ( ! $post_id ) return '';

        if ( ! Helper::is_addon_enabled_for_post_type( 'view_counter', get_post_type( $post_id ) ) ) {
            return '';
        }

        $this->view_count( $post_id);

        $view_count = intval( get_post_meta( $post_id, 'ecm_view_count', true ) );

        do_action( 'ecm_shortcode_loaded');

        ob_start();

        Helper::get_template('addons/view-counter/view-counter.php', [
            'view_count' => $view_count,
        ]);

        return ob_get_clean();
    }

    public function render_view_count( $atts = [] ) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'show_label' => 'false',
            'show_icon' => 'false',
            'label_single' => esc_html__( 'view', 'easy-content-manager' ),
            'label_plural' => esc_html__( 'views', 'easy-content-manager' ),
            'wrapper_class' => 'ecm-view-count',
        ], $atts );

        $post_id = intval( $atts['post_id'] );
        if ( ! $post_id ) return '';

        if ( ! Helper::is_addon_enabled_for_post_type( 'view_counter', get_post_type( $post_id ) ) ) {
            return '';
        }

        $view_count = intval( get_post_meta( $post_id, 'ecm_view_count', true ) );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/view-counter/view-count.php', [
            'count' => $view_count,
            'wrapper_class' => $atts['wrapper_class'],
            'show_label' => 'true' === $atts['show_label'],
            'show_icon' => 'true' === $atts['show_icon'],
            'label_single' => $atts['label_single'],
            'label_plural' => $atts['label_plural'],
        ] );
        return ob_get_clean();
    }

    private function view_count( $post_id ) {
        $cookie_name = 'ecm_viewed_posts';

        $view_counter_settings = Helper::get_settings( 'view_counter_addon', [] );

        $frequency_hours = isset( $view_counter_settings->view_count_frequency )
            ? max( 1, intval( $view_counter_settings->view_count_frequency ) )
            : 24;

        $cookie_lifetime = $frequency_hours * HOUR_IN_SECONDS;

        $enable_anonymous = ! empty( $view_counter_settings->enable_anonymous_view_count );

        if ( ! is_user_logged_in() && ! $enable_anonymous ) {
            return;
        }

        $views = [];

        if ( isset( $_COOKIE[ $cookie_name ] ) ) {
            $views = json_decode( wp_unslash( $_COOKIE[ $cookie_name ] ), true );
            $views = is_array( $views ) ? $views : [];
        }

        $current_time = time();

        if ( isset( $views[ $post_id ] ) ) {
            $last_view_time = intval( $views[ $post_id ] );

            if ( ( $current_time - $last_view_time ) < $cookie_lifetime ) {
                return;
            }
        }

        $this->increment_count( $post_id );

        // Update timestamp
        $views[ $post_id ] = $current_time;

        // Persist cookie
        setcookie(
            $cookie_name,
            wp_json_encode( $views ),
            $current_time + $cookie_lifetime,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }


    private function increment_count( $post_id ) {
        $count = intval( get_post_meta( $post_id, 'ecm_view_count', true ) );
        $count++;
        update_post_meta( $post_id, 'ecm_view_count', $count );
    }
}
