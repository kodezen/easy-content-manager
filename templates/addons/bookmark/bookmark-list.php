<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $user_id ) ) {
	$user_id = get_current_user_id();
}

if ( empty( $user_bookmarks ) ) {
	$user_bookmarks = maybe_unserialize( get_user_meta( $user_id, 'ecm_user_bookmarks', true ) );
}

if ( ! isset( $query ) ) {
	$args = [
		'post_type' => 'any',
		'posts_per_page' => -1,
		'post__in' => $user_bookmarks,
		'orderby' => 'post__in' // Maintain order if desired
	];

	$query = new \WP_Query( $args );
}

$external_link_icon = \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'external-link-dark' ) );

?>

<div class="ecm-shortcode ecm-shortcode--bookmark">
	<?php if ( $query && $query->have_posts() ) : ?>
		<ul class="ecm-shortcode--bookmark__list">
			<?php while ( $query->have_posts() ) :
				$query->the_post(); ?>
				<li class="ecm-shortcode--bookmark__list-item" data-post-id="<?php the_ID(); ?>" data-link="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<img class="ecm-shortcode--bookmark__list-image" src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>">
					<?php endif; ?>
					<div class="ecm-shortcode--bookmark__list-info">
						<p class="ecm-shortcode--bookmark__list-title">
							<?php the_title(); ?>
							<?php echo $external_link_icon; ?>
						</p>
						<p class="ecm-shortcode--bookmark__list-date">
							Bookmarked at: <?php the_time( 'F j, Y' ); ?>
						</p>
					</div>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<p><?php echo esc_html( $not_found_text ); ?></p>
	<?php endif; ?>
</div>
