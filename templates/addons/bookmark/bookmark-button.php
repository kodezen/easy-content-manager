<?php
// Variables available: get_the_ID(), $user_id, $is_bookmarked

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bookmarks = maybe_unserialize( get_user_meta( get_current_user_id(), 'ecm_user_bookmarks', true ) );

if ( is_string( $bookmarks ) ) {
	$bookmarks = [];
}

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();

$is_bookmarked = in_array( $post_id, $bookmarks, true );

?>

<div class="ecm-shortcode ecm-shortcode--bookmark" id="ecm-bookmark-wrapper-<?php echo esc_attr( get_the_ID() ); ?>">
	<button class="ecm-shortcode--bookmark__btn content-manager-btn <?php echo $is_bookmarked ? 'content-manager-btn--bookmark-active' : 'content-manager-btn--bookmark'; ?>">
		<?php if ( $is_bookmarked ) : ?>
			<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'bookmark-filled', '#ffffff' ) ); ?>
		<?php else : ?>
			<?php echo \EasyContentManager\Utils\Helper::kses_svg( \EasyContentManager\Utils\Helper::get_svg_icon( 'bookmark-outline' ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $bookmark_text ) || ! empty( $bookmarked_text ) ) : ?>
		   
			<span class="ecm-shortcode--bookmark__btn-text"><?php echo esc_html( $is_bookmarked ? $bookmarked_text : $bookmark_text ); ?></span>
			<span class="ecm-shortcode--bookmark__bookmarked-text"><?php echo esc_html( $bookmarked_text ); ?></span>
			<span class="ecm-shortcode--bookmark__bookmark-text"><?php echo esc_html( $bookmark_text ); ?></span>
		<?php endif; ?>
	</button>
</div>
