<?php
// claim-list.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$external_link_icon = \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'external-link-dark' ) );
?>
<div class="ecm-shortcode ecm-shortcode--claim">
    <?php if ( ! empty( $claims ) ) : ?>
        <ul class="ecm-shortcode--claim__list">
            <?php foreach ( $claims as $claim ) :
                $post = get_post( $claim->post_id );
                if ( ! $post ) continue;
                ?>
                <li class="ecm-shortcode--claim__list-item" data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-link="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                    <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                        <img class="ecm-shortcode--claim__list-image" src="<?php echo esc_url( get_the_post_thumbnail_url( $post->ID ) ); ?>">
                    <?php endif; ?>
                    <div class="ecm-shortcode--claim__list-info">
                        <p class="ecm-shortcode--claim__list-title">
                            <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                            <?php echo $external_link_icon; ?>
                        </p>
                        <p class="ecm-shortcode--claim__list-date">
                            <?php
                            /* translators: %s: date the listing was claimed. */
                            printf( esc_html__( 'Claimed at: %s', 'easy-content-manager' ), esc_html( gmdate( 'F j, Y', strtotime( $claim->created_at ) ) ) );
                            ?>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php echo esc_html( $not_found_text ); ?></p>
    <?php endif; ?>
</div>