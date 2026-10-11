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

<div class="ecm-url ecm-url--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-url__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<a href="<?php echo esc_url( $value ); ?>"
	   class="ecm-url__link"
	   target="_blank"
	   rel="noopener noreferrer">
		<?php echo esc_html( $value ); ?>
	</a>
</div>
