<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// For security: never display the real password directly.
// Instead, show masked characters or length indicator.
?>

<div class="ecm-password ecm-password--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-password__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-password__value">
		<?php echo esc_html( $value ); ?>
	</span>
</div>
