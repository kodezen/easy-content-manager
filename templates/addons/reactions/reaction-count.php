<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<span class="<?php echo esc_attr( $wrapper_class ); ?>" data-reaction-count="<?php echo esc_attr( $count ); ?>">
    <?php echo esc_html( $count ); ?>
    <?php if ( $show_label ) : ?>
        <?php echo esc_html( $count === 1 ? $label_single : $label_plural ); ?>
    <?php endif; ?>
</span>