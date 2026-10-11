<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || $value === '' ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Optional formatting (you can change decimals, separator, etc.)
$formatted_value = is_numeric( $value )
	? number_format_i18n( $value, 2 )
	: esc_html( $value );
?>

<div class="ecm-number ecm-number--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-number__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-number__value">
		<?php echo esc_html( $formatted_value ); ?>
	</span>
</div>
