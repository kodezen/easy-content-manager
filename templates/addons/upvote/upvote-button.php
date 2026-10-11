<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$upvote_post_id = $upvote_post_id ?? get_the_ID();
$count = $count ?? 0;

?>

<div class="ecm-shortcode ecm-shortcode--upvote">

	<button
		id="ecm-upvote-btn-<?php echo esc_attr( $is_upvoted ); ?>"
		class="ecm-shortcode--upvote__btn content-manager-btn <?php echo esc_attr( $is_upvoted ? 'content-manager-btn--upvote-btn-active' : 'content-manager-btn--upvote-btn' ); ?>"
		type="submit">
		<?php if ( $is_upvoted ) : ?>
			<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'upvote-up-filled', '#ffffff' ) ); ?>
		<?php else : ?>
			<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'upvote-up-outline' ) ); ?>
		<?php endif; ?>
		<p class="ecm-shortcode--upvote__btn-text <?php echo esc_attr( $is_upvoted ? 'ecm-shortcode--upvote__btn-text--active' : '' ); ?>"> <?php echo esc_html( $text ); ?> <span class="ecm-shortcode--upvote__count"><?php echo esc_html( $count ); ?></span></p>
	</button>
</div>
