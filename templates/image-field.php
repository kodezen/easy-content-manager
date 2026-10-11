<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) || ! is_array( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Expected structure of $value:
// [
// 'id'  => (int),
// 'url' => (string),
// 'alt' => (string),
// ]
$image_id  = $value['id'] ?? 0;
$image_url = $value['url'] ?? '';
$image_alt = $value['alt'] ?? $label;
?>

<div class="ecm-image ecm-image--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-image__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-image__wrapper">
		<?php if ( $image_id ) : ?>
			<?php echo wp_get_attachment_image( $image_id, 'full', false, [ 'class' => 'ecm-image__img' ] ); ?>
		<?php elseif ( ! empty( $image_url ) ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" 
				 alt="<?php echo esc_attr( $image_alt ); ?>" 
				 class="ecm-image__img" />
		<?php endif; ?>
	</div>
</div>
