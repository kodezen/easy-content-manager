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
			<h5 class="ecm-shortcode--review__ratings-overview-stat-heading ecm-shortcode--review__ratings-overview-stat-heading--orange">
				<span class="ecm-shortcode--review__orange"><?php echo esc_html( $average_rating ); ?></span>
				<span>out of 5 stars</span>
			</h5>
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
								style="width: <?php echo esc_attr( $percent ); ?>%;">
							</span>
						</div>
					</div>

					<p class="ecm-shortcode--review__rating-bar-percent">
						<?php echo esc_html( round( $percent ) ); ?>%
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ecm-shortcode--review__lists ecm-shortcode--review__lists--border-gap-6">
		<?php foreach ( $reviews as $index => $review ) : ?>
			<div class="ecm-shortcode--review__list-item ecm-shortcode--review__list-item--border-horizontal">
				<img src="<?php echo esc_url( $review->user_avatar ); ?>" alt="<?php echo esc_attr( $review->user_name ); ?>" class="ecm-shortcode--review__list-item-user-avatar" />
				<div class="ecm-shortcode--review__list-item-container content-manager-flex-12">
					<div class="ecm-shortcode--review__list-item-container-name-date">
						<p class="ecm-shortcode--review__list-item-username">
							<?php echo esc_html( $review->user_name ); ?>
						</p>
						<p class="ecm-shortcode--review__list-date">
							.
						</p>
						<p class="ecm-shortcode--review__list-date">
							<?php echo esc_html( date_i18n( 'F j, Y', strtotime( $review->comment_date ) ) ); ?>
						</p>
					</div>
					<p class="ecm-shortcode--review__list-content"><?php echo esc_html( $review->comment_content ); ?></p>
				</div>
			</div>
	</div>
<?php endforeach; ?>
</div>
