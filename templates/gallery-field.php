<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) || ! is_array( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;
?>

<div class="ecm-gallery ecm-gallery--<?php echo esc_attr( $name ); ?>" style="display:flex;flex-wrap:wrap;gap:10px;">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-gallery__label" style="width:100%;display:block;margin-bottom:5px;">
			<?php echo esc_html( $label ); ?>:
		</strong>
	<?php endif; ?>

	<?php foreach ( $value as $image ) : ?>
		<?php
		$image_url    = $image[0] ?? '';
		$image_width  = $image[1] ?? '';
		$image_height = $image[2] ?? '';
		if ( empty( $image_url ) ) {
			continue;
		}
		?>
		<figure class="ecm-gallery__item"
			style="min-width:150px;max-width:200px;flex:1 1 150px;margin:0;">
			<img
				src="<?php echo esc_url( $image_url ); ?>"
				width="<?php echo esc_attr( $image_width ); ?>"
				height="<?php echo esc_attr( $image_height ); ?>"
				alt=""
				style="width:100%;height:auto;border-radius:6px;display:block;"
				loading="lazy"
			/>
		</figure>
	<?php endforeach; ?>
</div>
