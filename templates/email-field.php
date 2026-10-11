<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;
?>

<div class="ecm-email ecm-email--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-email__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<a href="mailto:<?php echo esc_attr( $value ); ?>"
	   class="ecm-email__link">
		<?php echo esc_html( $value ); ?>
	</a>
</div>
