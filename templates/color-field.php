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

<div class="ecm-color ecm-color--<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $label ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-color__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<span class="ecm-color__swatch"
		  style="display:inline-block;width:1em;height:1em;border:1px solid #ccc;background-color:<?php echo esc_attr( $value ); ?>;">
	</span>

	<span class="ecm-color__value"><?php echo esc_html( $value ); ?></span>
</div>
