<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// True/False fields usually return boolean or 1/0
if ( ! isset( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Normalize truthy values
$is_true = in_array( $value, [ true, 1, '1', 'true', 'yes', 'on' ], true );
?>

<div class="ecm-truefalse ecm-truefalse--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-truefalse__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-truefalse__value ecm-truefalse__value--<?php echo $is_true ? 'true' : 'false'; ?>">
		<?php echo $is_true ? esc_html__( 'True', 'easy-content-manager') : esc_html__( 'False', 'easy-content-manager'); ?>
	</span>
</div>
