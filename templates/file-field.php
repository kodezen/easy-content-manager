<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Handle both direct URL and attachment array
$file_url = is_array( $value ) ? ( $value['url'] ?? '' ) : $value;
$file_name = is_array( $value ) ? ( $value['filename'] ?? basename( $file_url ) ) : basename( $file_url );

if ( empty( $file_url ) ) {
	return;
}
?>

<div class="ecm-file ecm-file--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-file__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<a href="<?php echo esc_url( $file_url ); ?>"
	   class="ecm-file__link"
	   download
	   target="_blank"
	   rel="noopener noreferrer">
		<?php echo esc_html( $file_name ); ?>
	</a>
</div>
