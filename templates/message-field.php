<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || $value === '' ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

?>

<div class="ecm-message ecm-message--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-message__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<p class="ecm-message__text">
		<?php echo esc_html( $value ); ?>
	</p>
</div>
