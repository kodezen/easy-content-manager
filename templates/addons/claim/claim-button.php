<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_claimed = $is_claimed ?? false;

if ( isset( $already_apply ) && $already_apply && ! $is_claimed ) :

	?>
	<div class="ecm-shortcode ecm-shortcode--tooltip"
		data-tooltip="You have already applied for claimed this listing."
	>
	<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'alert-dark' ) ); ?>
	</div>
	<?php

elseif ( ! $is_claimed ) :

	?>
	<div class="ecm-shortcode ecm-shortcode--claim" id="ecm-claim-wrapper-<?php echo esc_attr( get_the_ID() ); ?>">
		<button class="ecm-shortcode--claim__btn-active content-manager-btn content-manager-btn--preset-purple content-manager-btn--claim-btn"
			onclick="document.getElementById('ecm-claim-form-wrapper-<?php echo esc_attr( get_the_ID() ); ?>').classList.add('ecm-claim__form-wrapper-active'); 
	this.classList.add('ecm-shortcode--claim__btn-inactive'); this.classList.add('ecm-shortcode--claim__btn-active');">
			Claim Now
		</button>
	</div>
<?php else : ?>
	<div class="ecm-shortcode--claim__message">
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'check-circle-white', '#C73838' ) ); ?>

		<?php if ( ! empty( $text ) ) : ?>
			<?php echo esc_html( $text ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
