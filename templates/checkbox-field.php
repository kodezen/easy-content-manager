<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) || ! is_array( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;
?>

<div class="ecm-checkbox ecm-checkbox--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-checkbox__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-checkbox__options">
		<?php foreach ( $value as $option ) : ?>
			<?php
			$option_value = $option['value'] ?? '';
			$is_checked   = ! empty( $option['isChecked'] ) && $option['isChecked'];
			?>
			
			<div class="ecm-checkbox__item ecm-checkbox__item--<?php echo $is_checked ? 'checked' : 'unchecked'; ?>">
				<input 
					type="checkbox"
					disabled
					<?php checked( $is_checked ); ?>
				/>
				<label>
					<?php echo esc_html( $option_value ); ?>
				</label>
				<span class="ecm-checkbox__status">
					<?php echo $is_checked ? 'true' : 'false'; ?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
