<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Handle both array and plain string formats
$selected_value = is_array( $value ) ? ( $value['value'] ?? '' ) : (string) $value;

if ( $selected_value === '' ) {
	return;
}
?>

<div class="ecm-radio ecm-radio--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-radio__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-radio__option">
		<input
			type="radio"
			name="<?php echo esc_attr( $name ); ?>"
			checked
			disabled
		/>
		<label class="ecm-radio__value">
			<?php echo esc_html( $selected_value ); ?>
		</label>
	</div>
</div>
