<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view_count = $view_count ?? 0;
?>
<div class="ecm-shortcode ecm-shortcode--view-count">
   <span class="ecm-shortcode--view-count__number"> <?php echo esc_html( $view_count ); ?></span>
</div>

