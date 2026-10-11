<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $reviews ) ) {
	return;
}

?>

<div class="ecm-shortcode--review__list-wrapper content-manager-flex-16 ecm-width-full">
	<h3 class="ecm-shortcode--review__heading"><?php echo esc_html__( 'All Reviews', 'easy-content-manager' ); ?></h3>
	<div class="ecm-shortcode--review__ratings-overview-wrapper">
		<div class="ecm-shortcode--review__ratings-overview-stat content-manager-flex-12">
			<h5 class="ecm-shortcode--review__ratings-overview-stat-heading"><?php echo esc_html( $average_rating ); ?></h5>
			<p class="ecm-shortcode--review__list-rating">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<?php if ( $i <= $average_rating ) : ?>
						<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
					<?php else : ?>
						<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
					<?php endif; ?>
				<?php endfor; ?>
			</p>
			<p class="ecm-shortcode--review__total-reviews">
				<?php echo esc_html( sprintf( __( '%d Reviews', 'easy-content-manager' ), $total_reviews ) ); ?>
			</p>
		</div>

		<div class="ecm-shortcode--review__ratings-overview-progress-bars content-manager-flex-12 ecm-width-full">
			<?php foreach ( $rating_distribution as $star => $percent ) : ?>
				<div class="ecm-shortcode--review__rating-bar-wrapper">
					<div class="ecm-shortcode--review__rating-bar">
						<div class="ecm-shortcode--review__rating-bar-label">
							<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
							<?php echo esc_html( $star + 1 ); ?>
						</div>

						<div class="ecm-shortcode--review__rating-bar-track">
							<span
								class="ecm-shortcode--review__rating-bar-fill"
								style="width: <?php echo esc_attr( $percent ); ?>%;"></span>
						</div>
					</div>

					<p class="ecm-shortcode--review__rating-bar-percent">
						<?php echo esc_html( round( $percent ) ); ?>%
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ecm-shortcode--review__lists">
		<?php foreach ( $reviews as $review ) : ?>
			<div class="ecm-shortcode--review__list-item content-manager-flex-12">
				<div class="ecm-shortcode--review__list-item-user">
					<img src="<?php echo esc_url( $review->user_avatar ); ?>" alt="<?php echo esc_attr( $review->user_name ); ?>" class="ecm-shortcode--review__list-item-user-avatar" />
					<div class="ecm-shortcode--review__list-item-user-date">
						<p class="ecm-shortcode--review__list-item-username">
							<?php echo esc_html( $review->user_name ); ?>
						</p>
						<p class="ecm-shortcode--review__list-date">
							<?php echo esc_html( date_i18n( 'F j, Y', strtotime( $review->comment_date ) ) ); ?>
						</p>
					</div>
				</div>

				<div class="ecm-shortcode--review__list-item-rating-review">
					<p class="ecm-shortcode--review__list-item-rating">
						<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
						<span><?php echo esc_html( $review->rating ); ?></span>
					</p>
					<p class="ecm-shortcode--review__list-content"><?php echo esc_html( $review->comment_content ); ?></p>
				</div>
			<?php endforeach; ?>
			</div>
	</div>
