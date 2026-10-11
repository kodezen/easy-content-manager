<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;
$video_url = trim( $value );

?>

<div class="ecm-video ecm-video--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-video__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-video__wrapper">
		<iframe
			class="ecm-video__iframe"
			src="<?php echo esc_url( $video_url ); ?>"
			width="640"
			height="360"
			frameborder="0"
			allowfullscreen
			loading="lazy"
			referrerpolicy="no-referrer-when-downgrade"
			title="<?php echo esc_attr( $label ); ?>"
		></iframe>
	</div>
</div>
