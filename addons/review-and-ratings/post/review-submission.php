<?php

namespace ECMReviewAndRatings\Post;

use EasyContentManager\Classes\AbstractPostHandler;
use EasyContentManager\Utils\Helper;
use ECMReviewAndRatings\Admin\ReviewDisplay;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ReviewSubmission extends AbstractPostHandler {

    public function __construct() {

        $this->actions = [
            'ecm_submit_review' => [
                'callback'             => [ $this, 'submit_review' ],
                'fields'               => [
                    'post_id'       => 'absint',
                    'rating'        => 'absint',
                    'review_title'  => 'text',
                    'comment'       => 'post',
                    'author'        => 'text',
                    'author_email'  => 'email',
                ],
                'capability'           => 'only_logged_in',
            ],
        ];

        $this->dispatch_actions();
    }

    public function submit_review( array $payload ) {

        $post_id = intval( $payload['post_id'] ?? 0 );
        if ( ! $post_id ) {
            return new WP_Error( 'invalid_post', __( 'Invalid post.', 'easy-content-manager' ) );
        }

        if ( ! Helper::is_addon_enabled_for_post_type( 'review_ratings', get_post_type( $post_id ) ) ) {
            return new WP_Error('addon_disabled', __( 'Reviews are not enabled for this post type.', 'easy-content-manager' ) );
        }

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;

            // Check if this user already reviewed this post
            $existing = get_comments([
                'post_id' => $post_id,
                'user_id' => $user_id,
                'type'    => 'ecm_review',
                'count'   => true,
            ]);

            if ( $existing > 0 ) {
                return new WP_Error(
                    'review_exists',
                    __( 'You have already submitted a review for this item.', 'easy-content-manager' )
                );
            }

            // Logged-in author info
            $author       = $user->display_name ?: $user->user_login;
            $author_email = $user->user_email;

        } else {
            $author       = sanitize_text_field( $payload['author'] ?? '' );
            $author_email = sanitize_email( $payload['author_email'] ?? '' );

            if ( empty($author) || empty($author_email) ) {
                return new WP_Error(
                    'missing_fields',
                    __( 'Name and email are required.', 'easy-content-manager' )
                );
            }

            $existing_guest = get_comments([
                'post_id' => $post_id,
                'author_email' => $author_email,
                'type'    => 'ecm_review',
                'count'   => true,
            ]);

            if ( $existing_guest > 0 ) {
                return new WP_Error(
                    'review_exists',
                    __( 'You have already submitted a review for this item.', 'easy-content-manager' )
                );
            }

            $cookie_name = 'ecm_reviewed_posts';
            $reviewed = isset($_COOKIE[$cookie_name])
                ? json_decode( wp_unslash( $_COOKIE[$cookie_name] ), true )
                : [];

            $reviewed = is_array($reviewed) ? $reviewed : [];

            if ( in_array( $post_id, $reviewed, true ) ) {
                return new WP_Error(
                    'review_exists',
                    __( 'You have already submitted a review for this item.', 'easy-content-manager' )
                );
            }
        }

        $rating = intval( $payload['rating'] ?? 0 );
        $title  = sanitize_text_field( $payload['review_title'] ?? '' );
        $text   = wp_kses_post( $payload['comment'] ?? '' );

        if ( $rating < 1 || $rating > 5 ) {
            return new WP_Error( 'invalid_rating', __( 'Invalid rating value.', 'easy-content-manager' ) );
        }
        if ( empty( $text ) ) {
            return new WP_Error( 'empty_comment', __( 'Review text is required.', 'easy-content-manager' ) );
        }

        // Insert the comment
        $comment_id = wp_insert_comment([
            'comment_post_ID'      => $post_id,
            'comment_content'      => $text,
            'comment_type'         => ECM_REVIEWS_TYPE,
            'comment_author'       => $author,
            'comment_author_email' => $author_email,
            'user_id'              => is_user_logged_in() ? $user_id : 0,
            'comment_approved'     => 0,
        ]);

        if ( ! $comment_id ) {
            return new WP_Error( 'insert_failed', __( 'Could not save review.', 'easy-content-manager' ) );
        }

        add_comment_meta( $comment_id, '_ecm_rating', $rating );
        if ( $title ) {
            add_comment_meta( $comment_id, '_ecm_review_title', $title );
        }

        ReviewDisplay::recalculate_post_rating( $post_id );

        if ( ! is_user_logged_in() ) {
            $reviewed[] = $post_id;

            setcookie(
                $cookie_name,
                wp_json_encode($reviewed),
                time() + YEAR_IN_SECONDS,
                COOKIEPATH,
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }

        return get_permalink( $post_id ) . '?ecm_review_submitted=1#ecm-review-form';
    }
}
