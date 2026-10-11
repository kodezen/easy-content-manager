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

<div class="ecm-richtext ecm-richtext--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-richtext__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-richtext__content content">
		<?php
		// Rich text may include HTML, so we use `wp_kses_post()` for safe output.
		echo wp_kses_post( $value );
		?>
	</div>
</div>
