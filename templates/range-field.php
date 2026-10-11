<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || $value === '' ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Ensure numeric value and limit range safely (0–100 by default)
$numeric_value = is_numeric( $value ) ? floatval( $value ) : 0;
$percentage    = min( max( $numeric_value, 0 ), 100 );
?>

<div class="ecm-range ecm-range--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-range__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-range__wrapper">
		<div class="ecm-range__bar" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
		<span class="ecm-range__value"><?php echo esc_html( $percentage ); ?>%</span>
	</div>
</div>
