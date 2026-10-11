<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Always prefix with "dashicons dashicons-"
$icon_class = 'dashicons dashicons-' . ltrim( $value, '-' );

?>

<span class="ecm-icon ecm-icon--<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $label ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-icon__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
</span>
