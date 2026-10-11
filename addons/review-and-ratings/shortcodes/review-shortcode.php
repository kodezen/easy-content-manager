<?php

namespace ECMReviewAndRatings\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ReviewShortcode {

    public function __construct() {
        add_shortcode( 'ecm_review_form', [ $this, 'render_shortcode' ] );
        add_shortcode( 'ecm_review_list', [$this, 'render_review_list_shortcode']);
        add_shortcode( 'ecm_user_reviews', [$this, 'render_user_reviews_shortcode']);
    }

    public function render_shortcode( $atts = [], $content = '' ) {
        $atts = shortcode_atts(
            [
                'post_id' => get_the_ID(),
                'rating_layout'  => 'default',
            ],
            $atts,
            'ecm_review_form'
        );

        $post_id = intval( $atts['post_id'] );

        if ( ! $post_id ) {
            return '';
        }

        if ( ! is_user_logged_in() ) {
	        return '<p>' . esc_html__( 'Please log in to submit a review.', 'easy-content-manager' ) . '</p>';
        }

        $already_reviewed = false;
        $is_approved = false;

        $existing = get_comments([
            'post_id' => $post_id,
            'user_id' => get_current_user_id(),
            'type'    => 'ecm_review',
        ]);

        if ( count($existing) > 0 ) {
            $already_reviewed = true;
            $is_approved = (bool) current($existing)->comment_approved;
        }

        do_action( 'ecm_shortcode_loaded');

        if( in_array( $atts['rating_layout'], [ 'dropdown', 'star-basis', 'star-with-title' ] ) ) {
            ob_start();
            Helper::get_template('addons/review-and-ratings/review-form-' . $atts['rating_layout'] . '.php', [
                'is_reviewed' => $already_reviewed,
                'is_approved' => $is_approved,
                'form_post_id' => $post_id,
            ]);
            return ob_get_clean();
        }

        ob_start();

        Helper::get_template('addons/review-and-ratings/review-form-default.php', [
            'is_reviewed' => $already_reviewed,
            'is_approved' => $is_approved,
            'form_post_id' => $post_id,
        ]);
        
        return ob_get_clean();
    }

    public function render_review_list_shortcode( $atts = [], $content = '') {
        $atts = shortcode_atts(
            [
                'post_id' => get_the_ID(),
                'rating_layout'  => 'default',
            ],
            $atts,
            'ecm_review_list'
        );

        $post_id = $atts['post_id'];

        $reviews = get_comments([
            'post_id' => $post_id,
            'status'  => 'approve',
            'type'    => 'ecm_review',
            'orderby' => 'comment_date',
            'order'   => 'DESC',
        ]);

        foreach ( $reviews as $key => $review ) {
            $user = get_user_by('id', $review->user_id);
            $user_avatar = get_avatar_url($user->ID);
            $user_name = $user->display_name;

            $rating = intval(get_comment_meta($review->comment_ID, '_ecm_rating', true));
            $title  = get_comment_meta($review->comment_ID, '_ecm_review_title', true);

            $reviews[$key]->user_avatar = $user_avatar;
            $reviews[$key]->user_name = $user_name;
            $reviews[$key]->rating = $rating;
            $reviews[$key]->title = $title;
        }

        $rating_distribution = maybe_unserialize( get_post_meta( $post_id, 'ecm_rating_distribution', true ) );
        $average_rating = (float) get_post_meta( $post_id, 'ecm_average_rating', true );
        $average_rating = number_format( $average_rating, 2 );
        $review_count   = (int) get_post_meta( $post_id, 'ecm_review_count', true );

        do_action( 'ecm_shortcode_loaded');

        if( in_array( $atts['rating_layout'], [ 'carded-overview', 'classic-overview', 'minimal-breakdown' ] ) ) {
            ob_start();
            Helper::get_template('addons/review-and-ratings/review-list-' . $atts['rating_layout'] . '.php', [
                'reviews' => $reviews,
                'rating_distribution' => $rating_distribution,
                'average_rating' => $average_rating,
                'total_reviews' => $review_count,
            ]);
            return ob_get_clean();
        }

        ob_start();
        Helper::get_template('addons/review-and-ratings/review-list-default.php', [
            'reviews' => $reviews,
            'rating_distribution' => $rating_distribution,
            'average_rating' => $average_rating,
            'total_reviews' => $review_count,
        ]);
        return ob_get_clean();
    }

    public function render_user_reviews_shortcode( $atts = [], $content = '' ) {
        $atts = shortcode_atts(
            [
                'user_id' => get_current_user_id(),
                'status'  => 'approve',
                'limit'   => -1,
                'orderby' => 'comment_date',
                'order'   => 'DESC',
            ],
            $atts,
            'ecm_user_reviews'
        );

        $user_id = intval( $atts['user_id'] );

        if ( ! $user_id ) {
            return '<p>' . esc_html__( 'No user specified.', 'easy-content-manager' ) . '</p>';
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return '<p>' . esc_html__( 'User not found.', 'easy-content-manager' ) . '</p>';
        }

        $query_args = [
            'user_id' => $user_id,
            'type'    => 'ecm_review',
            'orderby' => $atts['orderby'],
            'order'   => $atts['order'],
        ];

        if ( $atts['limit'] > 0 ) {
            $query_args['number'] = intval( $atts['limit'] );
        }

        if ( $atts['status'] !== 'all' ) {
            $query_args['status'] = $atts['status'];
        }

        $reviews = get_comments( $query_args );

        if ( empty( $reviews ) ) {
            return '<p>' . esc_html__( 'No reviews found.', 'easy-content-manager' ) . '</p>';
        }

        $reviewed_posts = [];
        
        foreach ( $reviews as $review ) {
            $post = get_post( $review->comment_post_ID );
            
            if ( ! $post ) {
                continue;
            }

            $rating = intval( get_comment_meta( $review->comment_ID, '_ecm_rating', true ) );
            $title  = get_comment_meta( $review->comment_ID, '_ecm_review_title', true );

            $reviewed_posts[] = [
                'review_id'       => $review->comment_ID,
                'post_id'         => $post->ID,
                'post_title'      => $post->post_title,
                'post_url'        => get_permalink( $post->ID ),
                'post_thumbnail'  => get_the_post_thumbnail_url( $post->ID, 'medium' ),
                'rating'          => $rating,
                'review_title'    => $title,
                'review_content'  => $review->comment_content,
                'review_date'     => $review->comment_date,
                'is_approved'     => (bool) $review->comment_approved,
            ];
        }

        do_action( 'ecm_shortcode_loaded' );

        ob_start();
        Helper::get_template( 'addons/review-and-ratings/user-reviews-list.php', [
            'user'           => $user,
            'reviewed_posts' => $reviewed_posts,
            'total_reviews'  => count( $reviewed_posts ),
        ]);
        
        return ob_get_clean();
    }
}
