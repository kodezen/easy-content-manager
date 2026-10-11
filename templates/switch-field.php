<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Normalize truthy values (for better compatibility)
$is_true = in_array( $value, [ true, 1, '1', 'on', 'yes' ], true );
$status_text = $is_true ? 'true' : 'false';
?>

<div class="ecm-switch ecm-switch--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-switch__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-switch__status ecm-switch__status--<?php echo $is_true ? 'true' : 'false'; ?>">
		<?php echo esc_html( $status_text ); ?>
	</span>
</div>
