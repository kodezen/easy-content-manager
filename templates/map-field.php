<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) || ! is_array( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Expected structure:
// [
// 'lat'        => 23.780573,
// 'lng'        => 90.279239,
// 'zoomLevel'  => 14,
// 'address'    => 'Dhaka, Bangladesh' (optional)
// ]

$lat        = $value['lat'] ?? '';
$lng        = $value['lng'] ?? '';
$zoomLevel  = $value['zoomLevel'] ?? '';
$address    = $value['address'] ?? '';
?>

<div class="ecm-map ecm-map--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-map__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-map__details">
		<?php if ( ! empty( $address ) ) : ?>
			<p class="ecm-map__address">
				<strong><?php esc_html_e( 'Address:', 'easy-content-manager'); ?></strong>
				<?php echo esc_html( $address ); ?>
			</p>
		<?php endif; ?>
	</div>

	<!-- Optional static map preview -->
	<div class="ecm-map__preview">
		<iframe
			width="100%"
			height="250"
			frameborder="0"
			style="border:0"
			src="https://www.google.com/maps?q=<?php echo esc_attr( $lat ); ?>,<?php echo esc_attr( $lng ); ?>&z=<?php echo esc_attr( $zoomLevel ?: 14 ); ?>&output=embed"
			allowfullscreen>
		</iframe>
	</div>
</div>
