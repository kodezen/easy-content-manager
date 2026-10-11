<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;
?>

<p class="ecm-datetime ecm-datetime--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-datetime__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<?php echo esc_html( date_i18n( 'M j, Y g:i A', strtotime( $value ) ) ); ?>
</p>
