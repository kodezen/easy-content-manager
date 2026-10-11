<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! $is_reviewed ) :

	$nonce  = wp_create_nonce( 'easy_content_manager_nonce' );
	$action = EASY_CONTENT_MANAGER_PLUGIN_SLUG . '/ecm_submit_review';
	?>
	<div class="ecm-shortcode ecm-shortcode--review ecm-width-full" id="ecm-review-form">
		<form class="ecm-shortcode--review__form content-manager-flex-16 ecm-width-full" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>" />
			<input type="hidden" name="security" value="<?php echo esc_attr( $nonce ); ?>" />
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $form_post_id ); ?>" />

			<div class="content-manager-flex-12 ecm-width-full">
				 <label class="content-manager-title">Rating<span class="content-manager-required">*</span> </label>
				<div class="ecm-shortcode--review__stars-container content-manager-flex-rows ecm--pointer">
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<label class="ecm-shortcode--review__star ecm--pointer" data-rating="<?php echo esc_attr( $i ); ?>">
							<input type="radio" name="rating" value="<?php echo esc_attr( $i ); ?>" class="ecm-shortcode--review__star-rating-input" required>
							<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
						</label>
					<?php endfor; ?>
				</div>
			</div>

			<div class="content-manager-flex-12 ecm-width-full">
				<label class="content-manager-title">Review Title <span class="content-manager-required">*</span> </label>
				<input class="content-manager-input content-manager-input--gray" type="text" name="review_title" required placeholder="Enter review title">
			</div>

			<div class="content-manager-flex-12 ecm-width-full">
				<label class="content-manager-title">Your Review <span class="content-manager-required">*</span></label>
				<textarea class="content-manager-textarea content-manager-textarea--gray" name="comment" required rows="5" placeholder="Your review here..."></textarea>
			</div>

			<button class="content-manager-btn content-manager-btn--preset-purple content-manager-btn--review-submit" type="submit">Submit Review</button>

		</form>
	</div>
<?php elseif ( $is_reviewed && ! $is_approved ) : ?>
	<div class="ecm-shortcode--review__submission-message-box">
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
		<p class="ecm-shortcode--review__submission-text">
			<?php esc_html_e( 'You have already submitted a review for this post, Please wait for admin approval.', 'easy-content-manager' ); ?>
		</p>
	</div>
<?php endif; ?>
