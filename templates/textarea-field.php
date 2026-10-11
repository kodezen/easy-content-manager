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
		<h3 class="ecm-richtext__heading"><?php echo esc_html( $label ); ?></h3>
	<?php endif; ?>

	<div class="ecm-richtext__content">
		<?php
		// Allow basic HTML formatting while filtering unsafe tags
		echo wp_kses_post( $value );
		?>
	</div>
</div>
