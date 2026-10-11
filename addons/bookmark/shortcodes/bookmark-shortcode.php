<?php
namespace ECMBookmark\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookmarkShortcode {
    public function __construct() {
        add_shortcode( 'ecm_bookmark_button', [ $this, 'render_bookmark_button' ] );
        add_shortcode( 'ecm_bookmark_badge', [ $this, 'render_bookmark_badge' ] );
        add_shortcode( 'ecm_bookmark_list', [ $this, 'render_bookmark_list' ] );
        add_shortcode( 'ecm_bookmark_count', [ $this, 'render_bookmark_count' ] );
    }

    public function render_bookmark_button( $atts ) {
        if ( ! is_user_logged_in() ) {
            return ''; 
        }

        $atts = shortcode_atts( [
            'id' => get_the_ID(),
            'bookmark_text' => '',
            'bookmarked_text' => '',
        ], $atts );

        $post_id = intval( $atts['id'] );
        $user_id = get_current_user_id();

        // Check status via User Meta
        $user_bookmarks = maybe_unserialize(get_user_meta( $user_id, 'ecm_user_bookmarks', true ));
        $is_bookmarked = is_array( $user_bookmarks ) && in_array( $post_id, $user_bookmarks );

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/bookmark/bookmark-button.php', [
            'post_id' => $post_id,
            'is_bookmarked' => $is_bookmarked,
            'bookmark_text' => $atts['bookmark_text'],
            'bookmarked_text' => $atts['bookmarked_text'],
        ] );
        return ob_get_clean();
    }

    public function render_bookmark_badge( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $atts = shortcode_atts( [
            'id' => get_the_ID(),
        ], $atts );

        $post_id = intval( $atts['id'] );
        $user_id = get_current_user_id();

        $user_bookmarks = maybe_unserialize(get_user_meta( $user_id, 'ecm_user_bookmarks', true ));

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template('addons/bookmark/bookmark-badge.php', [
            'user_bookmarks' => $user_bookmarks,
            'post_id' => $post_id,
        ]);
        return ob_get_clean();
    }

    public function render_bookmark_list( $atts ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Please login to view bookmarks.', 'easy-content-manager' );
        }
        
        $atts = shortcode_atts( [
            'user_id' => get_current_user_id(),
            'not_found_text' => esc_html__( 'No bookmarks found.', 'easy-content-manager' ),
        ], $atts );

        $user_id = intval( $atts['user_id'] );
        $user_bookmarks = maybe_unserialize(get_user_meta( $user_id, 'ecm_user_bookmarks', true ));

        if ( empty( $user_bookmarks ) || ! is_array( $user_bookmarks ) ) {
            return '<p>No bookmarks found.</p>';
        }

        $args = [
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post__in' => $user_bookmarks,
            'orderby' => 'post__in' // Maintain order if desired
        ];

        $query = new \WP_Query( $args );

        do_action( 'ecm_shortcode_loaded'); 
        
        ob_start();
        Helper::get_template( 'addons/bookmark/bookmark-list.php', [
            'query' => $query,
            'user_id' => $user_id,
            'user_bookmarks' => $user_bookmarks,
            'not_found_text' => $atts['not_found_text'],
        ]);
        return ob_get_clean();
    }

    public function render_bookmark_count( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '0';
        }

        $atts = shortcode_atts( [
            'user_id'       => get_current_user_id(),
            'show_label'    => 'false',
            'show_icon'     => 'false',
            'label_single'  => esc_html__( 'bookmark', 'easy-content-manager' ),
            'label_plural'  => esc_html__( 'bookmarks', 'easy-content-manager' ),
            'wrapper_class' => 'ecm-bookmark-count',
        ], $atts );

        $user_id        = intval( $atts['user_id'] );
        $user_bookmarks = maybe_unserialize( get_user_meta( $user_id, 'ecm_user_bookmarks', true ) );
        $count          = is_array( $user_bookmarks ) ? count( $user_bookmarks ) : 0;

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/bookmark/bookmark-count.php', [
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
