<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="ecm-shortcode ecm-shortcode--upvote">
    <?php if ( ! empty( $upvoted_posts ) ) : ?>
        <ul class="ecm-shortcode--upvote__list">
            <?php foreach ( $upvoted_posts as $post_id ) : 
                $post = get_post( $post_id );
                if ( ! $post ) continue;
                ?>
                <li class="ecm-shortcode--upvote__list-item" data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-link="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                    <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                        <img class="ecm-shortcode--upvote__list-image" src="<?php echo esc_url( get_the_post_thumbnail_url( $post->ID ) ); ?>">
                    <?php endif; ?>
                    <div class="ecm-shortcode--upvote__list-info">
                        <p class="ecm-shortcode--upvote__list-title">
                            <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                            <?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'external-link-dark' ) ); ?>
                        </p>
                        <p class="ecm-shortcode--upvote__list-date">
                            Upvoted at: <?php echo esc_html( get_the_date( 'F j, Y', $post->ID ) ); ?>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php echo esc_html( $not_found_text ); ?></p>
    <?php endif; ?>
</div>