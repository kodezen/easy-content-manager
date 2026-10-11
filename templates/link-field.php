<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) || ! is_array( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

$url    = $value['url'] ?? '';
$title  = $value['title'] ?? $url;
$target = $value['target'] ?? '_self';

if ( empty( $url ) ) {
	return;
}
?>

<div class="ecm-link ecm-link--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-link__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<a href="<?php echo esc_url( $url ); ?>"
	   target="<?php echo esc_attr( $target ); ?>"
	   rel="noopener noreferrer"
	   class="ecm-link__anchor">
		<?php echo esc_html( $title ); ?>
	</a>
</div>
