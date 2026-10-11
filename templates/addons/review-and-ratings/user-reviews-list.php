<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="ecm-shortcode ecm-shortcode--user-reviews">
    <?php if ( ! empty( $reviewed_posts ) ) : ?>
        <div class="ecm-shortcode--user-reviews__header">
            <h3 class="ecm-shortcode--user-reviews__title">
                <?php
                /* translators: %s: display name of the reviewer. */
                printf(
                    esc_html__( 'Reviews by %s', 'easy-content-manager' ),
                    esc_html( $user->display_name )
                );
                ?>
            </h3>
            <p class="ecm-shortcode--user-reviews__count">
                <?php
                /* translators: %d: number of reviews the user has written. */
                printf(
                    esc_html( _n( '%d review', '%d reviews', $total_reviews, 'easy-content-manager' ) ),
                    (int) $total_reviews
                );
                ?>
            </p>
        </div>

        <ul class="ecm-shortcode--user-reviews__list">
            <?php foreach ( $reviewed_posts as $item ) : 
                $post = get_post( $item['post_id'] );
                if ( ! $post ) continue;
                ?>
                <li class="ecm-shortcode--user-reviews__list-item <?php echo $item['is_approved'] ? 'is-approved' : 'is-pending'; ?>" 
                    data-post-id="<?php echo esc_attr( $post->ID ); ?>" 
                    data-review-id="<?php echo esc_attr( $item['review_id'] ); ?>"
                    data-link="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                    
                    <?php if ( $item['post_thumbnail'] ) : ?>
                        <img class="ecm-shortcode--user-reviews__list-image" 
                             src="<?php echo esc_url( $item['post_thumbnail'] ); ?>" 
                             alt="<?php echo esc_attr( $item['post_title'] ); ?>">
                    <?php endif; ?>
                    
                    <div class="ecm-shortcode--user-reviews__list-info">
                        <p class="ecm-shortcode--user-reviews__list-title">
                            <?php echo esc_html( $item['post_title'] ); ?>
                            <?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'external-link-dark' ) ); ?>
                            <?php if ( ! $item['is_approved'] ) : ?>
                                <span class="ecm-shortcode--user-reviews__list-badge">
                                    <?php esc_html_e( 'Pending', 'easy-content-manager' ); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        
                        <div class="ecm-shortcode--user-reviews__list-rating">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <span class="ecm-star <?php echo $i <= $item['rating'] ? 'filled' : 'empty'; ?>">
                                    <?php echo $i <= $item['rating'] ? '★' : '☆'; ?>
                                </span>
                            <?php endfor; ?>
                            <span class="ecm-shortcode--user-reviews__list-rating-number"><?php echo esc_html( $item['rating'] ); ?>/5</span>
                        </div>

                        <?php if ( $item['review_title'] ) : ?>
                            <p class="ecm-shortcode--user-reviews__list-review-title">
                                <?php echo esc_html( $item['review_title'] ); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $item['review_content'] ) : ?>
                            <p class="ecm-shortcode--user-reviews__list-content">
                                <?php echo esc_html( wp_trim_words( $item['review_content'], 30, '...' ) ); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="ecm-shortcode--user-reviews__list-meta">
                            <span class="ecm-shortcode--user-reviews__list-date">
                                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['review_date'] ) ) ); ?>
                            </span>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php esc_html_e( 'No reviews found.', 'easy-content-manager' ); ?></p>
    <?php endif; ?>
</div>