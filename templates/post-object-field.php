<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Normalize into an array (supports single ID or multiple)
$posts = is_array( $value ) ? $value : [ $value ];

?>

<div class="ecm-post-object ecm-post-object--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-post-object__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-post-object__value">
		<?php foreach ( $posts as $post_item ) : ?>
			<?php
			// Handle multiple data formats
			if ( is_numeric( $post_item ) ) {
				$post_id    = (int) $post_item;
				$post_title = get_the_title( $post_id );
			} elseif ( is_array( $post_item ) ) {
				$post_id    = isset( $post_item['id'] ) ? (int) $post_item['id'] : 0;
				$post_title = $post_item['title'] ?? get_the_title( $post_id );
			} else {
				continue;
			}

			if ( ! $post_id ) {
				continue;
			}
			?>

			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="ecm-post-object__link">
				<?php echo esc_html( $post_title ); ?>
			</a><br>
		<?php endforeach; ?>
	</div>
</div>
