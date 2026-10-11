<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Normalize: ensure $terms is an array.
$terms = is_array( $value ) ? $value : [ $value ];

?>

<div class="ecm-taxonomy ecm-taxonomy--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-taxonomy__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-taxonomy__value">
		<?php foreach ( $terms as $term_item ) : ?>
			<?php
			$term_id   = 0;
			$term_name = '';
			$term_link = '';

			if ( is_numeric( $term_item ) ) {
				$term = get_term( (int) $term_item );
				if ( $term && ! is_wp_error( $term ) ) {
					$term_id   = $term->term_id;
					$term_name = $term->name;
					$term_link = get_term_link( $term );
				}
			} elseif ( is_array( $term_item ) || is_object( $term_item ) ) {
				$term_id   = (int) ( $term_item['id'] ?? ( $term_item->id ?? 0 ) );
				$term_name = $term_item['name'] ?? ( $term_item->name ?? '' );
				$term_link = $term_item['link'] ?? ( $term_item->link ?? '' );

				if ( empty( $term_link ) && $term_id ) {
					$link = get_term_link( $term_id );
					if ( ! is_wp_error( $link ) ) {
						$term_link = $link;
					}
				}
			}

			if ( empty( $term_name ) || empty( $term_link ) ) {
				continue;
			}
			?>

			<a href="<?php echo esc_url( $term_link ); ?>"
			   class="ecm-taxonomy__term"
			   data-term-id="<?php echo esc_attr( $term_id ); ?>">
				<?php echo esc_html( $term_name ); ?>
			</a><br>

		<?php endforeach; ?>
	</div>
</div>
