<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $is_claimed ) :
	?>
	<span class="ecm-shortcode ecm-shortcode--claim-badge">
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'check-circle-white', '#C73838' ) ); ?>
		<?php if ( ! empty( $text ) ) : ?>
			<p class="ecm-shortcode--claim-badge__badge-text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</span>
<?php endif; ?>
