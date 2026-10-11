<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $user_bookmarks ) ) {
	$user_bookmarks = [];
}

if ( ! isset( $post_id ) ) {
	// @phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$post_id = get_the_ID();
}
?>
<div class="ecm-shortcode ecm-shortcode--bookmark">
	<?php
	// @phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	if ( is_array( $user_bookmarks ) && in_array( $post_id, $user_bookmarks ) ) :
		?>
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'bookmark-filled' ) ); ?>
	<?php else : ?>
		<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'bookmark-outline' ) ); ?>
	<?php endif; ?>
</div>
