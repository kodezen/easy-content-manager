<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $value ) || empty( $value ) ) {
	return;
}

$name   = $field['name'] ?? '';
$label  = $field['label'] ?? $name;

// Normalize into an array (works for single ID or multiple)
$page_ids = is_array( $value ) ? $value : [ $value ];

?>

<div class="ecm-page-link ecm-page-link--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-page-link__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-page-link__value">
		<?php foreach ( $page_ids as $id ) : ?>
			<?php
			$page_id = (int) $id;
			if ( ! $page_id ) {
				continue;
			}

			$page_url   = get_permalink( $page_id );
			$page_title = get_the_title( $page_id );

			if ( ! $page_url || ! $page_title ) {
				continue;
			}
			?>
			<a href="<?php echo esc_url( $page_url ); ?>" class="ecm-page-link__anchor">
				<?php echo esc_html( $page_title ); ?>
			</a><br>
		<?php endforeach; ?>
	</div>
</div>
