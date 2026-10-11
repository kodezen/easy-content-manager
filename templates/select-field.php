<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Handle both array format and plain value
if ( is_array( $value ) ) {
	$option_value = $value['value'] ?? '';
	$option_label = $value['label'] ?? $option_value;
} else {
	$option_value = (string) $value;
	$option_label = $option_value;
}

if ( empty( $option_label ) ) {
	return;
}
?>

<div class="ecm-select ecm-select--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-select__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-select__value">
		<?php echo esc_html( $option_label ); ?>
	</span>
</div>
