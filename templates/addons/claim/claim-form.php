<?php if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly. ?>
<div id="ecm-claim-form-wrapper-<?php echo esc_attr( get_the_ID() ); ?>" class="ecm-shortcode ecm-shortcode--claim-form">
	<form class="ecm-shortcode--claim-form__form content-manager-flex-16" id="ecm-claim-form-<?php echo esc_attr( get_the_ID() ); ?>">
		<?php if ( ! is_user_logged_in() ) : ?>
			<div class="ecm-shortcode--claim-form__form-group">
				<label class="content-manager-title">Name <span class="content-manager-required">*</span> </label>
				<input class="content-manager-input" type="text" name="ecm_name" required>
			</div>
			<div class="ecm-shortcode--claim-form__form-group">
				<label class="content-manager-title">Email <span class="content-manager-required">*</span> </label>
				<input class="content-manager-input" type="email" name="ecm_email" required>
			</div>
			<div class="ecm-shortcode--claim-form__form-group">
				<label class="content-manager-title">Phone <span class="content-manager-required">*</span> </label>
				<input class="content-manager-input" type="text" name="ecm_phone" required>
			</div>
		<?php endif; ?>

		<div class="ecm-shortcode--claim-form__form-group">
			<label class="content-manager-title">Proof of Claim (Details or URL) <span class="content-manager-required">*</span> </label>
			<textarea class="content-manager-textarea" name="ecm_proof" required></textarea>
		</div>
		
		<div class="content-manager-flex-row">
			<button class="content-manager-btn content-manager-btn--preset-purple content-manager-btn--claim-btn ecm-shortcode--claim-form__submit-claim" type="submit">Submit Claim</button>
			<button class="content-manager-btn content-manager-btn--preset-error content-manager-btn--claim-btn" type="button" onclick="document.getElementById('ecm-claim-form-<?php echo esc_attr( get_the_ID() ); ?>').style.display = 'none'; document.querySelector('#ecm-claim-wrapper-<?php echo esc_attr( get_the_ID() ); ?> .ecm-claim-btn').style.display = 'inline-block';">Cancel</button>
		</div>
	</form>
	<div class="ecm-shortcode--claim-form__response content-manager-flex"></div>
</div>
