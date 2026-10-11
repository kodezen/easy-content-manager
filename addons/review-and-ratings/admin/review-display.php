<?php
namespace ECMReviewAndRatings\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class ReviewDisplay {

    public function __construct() {
        // Auto-insert rating summary above the content, only where enabled.
        add_filter( 'the_content', array( $this, 'prepend_rating_summary_to_content' ) );
    }

    public function prepend_rating_summary_to_content( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }

        $post_id = get_the_ID();

        if ( ! get_post_meta( $post_id, 'ecm_review_ratings_enabled', true ) ) {
            return $content;
        }

        $summary_html = $this->get_rating_summary_html( $post_id );

        // If there is no rating yet, don't show anything.
        if ( empty( $summary_html ) ) {
            return $content;
        }

        return $summary_html . $content;
    }

    public function get_rating_summary_html( $post_id ) {
        $average = get_post_meta( $post_id, 'ecm_average_rating', true );
        $count   = get_post_meta( $post_id, 'ecm_review_count', true );

        if ( ! $average || ! $count ) {
            return '';
        }

        $average = round( floatval( $average ), 1 );
        $count   = intval( $count );

        ob_start();
        ?>
        <div class="ecm-review-summary" style="margin: 1em 0; padding: 0.75em 1em; border: 1px solid #eee; border-radius: 4px;">
            <strong><?php echo esc_html( $average ); ?>/5</strong>
            <span>
                <?php
                printf(
                    /* translators: %d number of reviews */
                    esc_html__( ' based on %d review(s)', 'easy-content-manager' ),
                    $count
                );
                ?>
            </span>
        </div>
        <?php

        return ob_get_clean();
    }

    public static function recalculate_post_rating( $post_id ) {
        $args = array(
            'post_id' => $post_id,
            'status'  => 'approve',
            'type'    => 'ecm_review',
            'number'  => 0, // no limit
        );

        $comments = get_comments( $args );

        if ( empty( $comments ) ) {
            delete_post_meta( $post_id, 'ecm_average_rating' );
            delete_post_meta( $post_id, 'ecm_review_count' );
            return;
        }

        $sum   = 0;
        $count = 0;

        $rating_counts = array_fill(0, 5, 0); // For 1 to 5 stars

        foreach ( $comments as $comment ) {
            $rating = get_comment_meta( $comment->comment_ID, '_ecm_rating', true );
            $rating = intval( $rating );

            $rating_counts[$rating-1]+=1;

            if ( $rating > 0 && $rating <= 5 ) {
                $sum   += $rating;
                $count++;
            }
        }

        if ( $count > 0 ) {
            $average = $sum / $count;
            update_post_meta( $post_id, 'ecm_average_rating', $average );
            update_post_meta( $post_id, 'ecm_review_count', $count );
            update_post_meta( $post_id, 'ecm_rating_distribution', maybe_serialize( $rating_counts ) );
        } else {
            delete_post_meta( $post_id, 'ecm_average_rating' );
            delete_post_meta( $post_id, 'ecm_review_count' );
            delete_post_meta( $post_id, 'ecm_rating_distribution' );
        }
    }
}
