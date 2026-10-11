<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $reviews ) ) {
	return;
}
$total_reviews = count( $reviews );
?>

<div class="ecm-shortcode--review__list-wrapper content-manager-flex-16 ecm-width-full">
	<h3 class="ecm-shortcode--review__heading"><?php echo esc_html__( 'All Reviews', 'easy-content-manager' ); ?></h3>
	<div class="ecm-shortcode--review__ratings-overview-wrapper ecm-shortcode--review__ratings-overview-wrapper--gray">
		<div class="ecm-shortcode--review__ratings-overview-stat ecm-shortcode--review__ratings-overview-stat--gray content-manager-flex-12">
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

		<div class="ecm-shortcode--review__ratings-overview-progress-bars ecm-shortcode--review__ratings-overview-progress-bars--gray content-manager-flex-12 ecm-width-full">
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
						<?php echo esc_html( round( $percent ) ); ?> Ratings
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ecm-shortcode--review__lists ecm-shortcode--review__lists--border">
		<?php foreach ( $reviews as $index => $review ) : ?>
			<div class="ecm-shortcode--review__list-item ecm-shortcode--review__list-item--border content-manager-flex-12">
				<div class="ecm-shortcode--review__list-item-user-date-container">
					<div class="ecm-shortcode--review__list-item-user">
						<img src="<?php echo esc_url( $review->user_avatar ); ?>" alt="<?php echo esc_attr( $review->user_name ); ?>" class="ecm-shortcode--review__list-item-user-avatar" />
						<div class="ecm-shortcode--review__list-item-user-date">
							<?php if ( $review->rating ) : ?>
								<p class="ecm-shortcode--review__list-rating">
									<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
										<?php if ( $i <= $average_rating ) : ?>
											<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-filled' ) ); ?>
										<?php else : ?>
											<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'star-outline' ) ); ?>
										<?php endif; ?>
									<?php endfor; ?>
								</p>
							<?php endif; ?>
							<p class="ecm-shortcode--review__list-item-username">
								<?php echo esc_html( $review->user_name ); ?>
							</p>
						</div>
					</div>
					<p class="ecm-shortcode--review__list-date">
						<?php echo esc_html( date_i18n( 'F j, Y', strtotime( $review->comment_date ) ) ); ?>
					</p>
				</div>

				<div class="ecm-shortcode--review__list-content-wrapper">
					<p class="ecm-shortcode--review__list-content"><?php echo esc_html( $review->comment_content ); ?></p>
				</div>

			</div>
				<?php
				if ( $index < ( $total_reviews - 1 ) ) : ?>
					<div class="content-manager-separator"></div>
				<?php endif; ?>
			<?php endforeach; ?>
	</div>
</div>
