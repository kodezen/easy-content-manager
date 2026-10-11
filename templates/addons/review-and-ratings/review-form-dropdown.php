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
			<input id="ecm-review-rating-input" type="hidden" name="rating" value="" />

			<div class="content-manager-flex-12 ecm-width-full">
				<label class="content-manager-title">Rating <span class="content-manager-required">*</span></label>
				<div class="ecm-shortcode--review__dropdown">
					<input type="checkbox" id="ecm-review-dropdown-toggle" class="ecm-shortcode--review__dropdown-state">

					<label for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__dropdown-trigger">
						<span class="ecm-shortcode--review__dropdown-trigger-text">Select Rating</span>
						<div class="ecm-shortcode--review__arrow"></div>
					</label>

					<ul class="ecm-shortcode--review__dropdown-menu">
						<li>
							<div for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__option-item" data-value="5">
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<span class="ecm-shortcode--review__option-item-text">(Excellent)</span>
							</div>
						</li>
						<li>
							<div for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__option-item" data-value="4">
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<span class="ecm-shortcode--review__option-item-text">(Good)</span>
							</div>
						</li>
						<li>
							<div for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__option-item" data-value="3">
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<span class="ecm-shortcode--review__option-item-text">(Average)</span>
							</div>
						</li>
						<li>
							<div for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__option-item" data-value="2">
								 <?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<span class="ecm-shortcode--review__option-item-text">(Poor)</span>
							</div>
						</li>
						<li>
							<div for="ecm-review-dropdown-toggle" class="ecm-shortcode--review__option-item" data-value="1">
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
								<span class="ecm-shortcode--review__option-item-text">(Very Poor)</span>
							</div>
						</li>
					</ul>
				</div>
			</div>

			<div class="content-manager-flex-12 ecm-width-full">
				<label class="content-manager-title">Review Title <span class="content-manager-required">*</span> </label>
				<input class="content-manager-input" type="text" name="review_title" required placeholder="Enter review title">
			</div>

			<div class="content-manager-flex-12 ecm-width-full">
				<label class="content-manager-title">Your Review <span class="content-manager-required">*</span></label>
				<textarea class="content-manager-textarea" name="comment" required rows="5" placeholder="Your review here..."></textarea>
			</div>

			<button class="content-manager-btn content-manager-btn--preset-purple content-manager-btn--review-submit" type="submit">Submit Review</button>

		</form>
	</div>
<?php elseif ( $is_reviewed && ! $is_approved ) : ?>
	<div class="ecm-shortcode--review__submission-message-box">
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
		<p class="ecm-shortcode--review__submission-text">
			<?php esc_html_e( 'You have already submitted a review for this post, Please wait for admin approval.', 'easy-content-manager' ); ?>
		</p>
	</div>
<?php endif; ?>
