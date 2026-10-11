<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Format time (assuming $value is a valid time string like "14:30" or "2:30 PM")
$timestamp = strtotime( $value );
$formatted_time = $timestamp ? date_i18n( get_option( 'time_format' ), $timestamp ) : esc_html( $value );
?>

<div class="ecm-time ecm-time--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-time__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-time__value">
		<?php echo esc_html( $formatted_time ); ?>
	</span>
</div>
