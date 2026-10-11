<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<span class="<?php echo esc_attr( $wrapper_class ); ?>" data-bookmark-count="<?php echo esc_attr( $count ); ?>">
    <?php echo esc_html( $count ); ?>
</span>