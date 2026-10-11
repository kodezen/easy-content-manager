<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$external_link_icon = \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'external-link-dark' ) );
?>
<div class="ecm-shortcode ecm-shortcode--reaction">
    <?php if ( ! empty( $posts_with_reactions ) ) : ?>
        <ul class="ecm-shortcode--reaction__list">
            <?php foreach ( $posts_with_reactions as $item ) :
                $post = get_post( $item['post_id'] );
                if ( ! $post ) continue;
                ?>
                <li class="ecm-shortcode--reaction__list-item" data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-link="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                    <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                        <img class="ecm-shortcode--reaction__list-image" src="<?php echo esc_url( get_the_post_thumbnail_url( $post->ID ) ); ?>">
                    <?php endif; ?>
                    <div class="ecm-shortcode--reaction__list-info">
                        <p class="ecm-shortcode--reaction__list-title">
                            <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                            <?php echo $external_link_icon; ?>
                        </p>
                        <p class="ecm-shortcode--reaction__list-meta">
                            <span class="ecm-shortcode--reaction__list-reaction"><?php
                                /* translators: %s: name of the reaction the user left. */
                                printf( esc_html__( 'Reacted: %s', 'easy-content-manager' ), esc_html( $item['reaction'] ) );
                            ?></span>
                            <span class="ecm-shortcode--reaction__list-date"><?php echo esc_html( get_the_date( 'F j, Y', $post->ID ) ); ?></span>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php echo esc_html( $not_found_text ); ?></p>
    <?php endif; ?>
</div>