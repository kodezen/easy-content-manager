<?php
// claim-count.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<span class="<?php echo esc_attr( $wrapper_class ); ?>" data-claim-count="<?php echo esc_attr( $count ); ?>">
    <?php if ( $show_icon ) : ?>
        <?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'check-circle-dark' ) ); ?>
    <?php endif; ?>
    <?php echo esc_html( $count ); ?>
    <?php if ( $show_label ) : ?>
        <?php echo esc_html( $count === 1 ? $label_single : $label_plural ); ?>
    <?php endif; ?>
</span>