<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ecm-shortcode ecm-shortcode--reaction">

	<?php foreach ( $available_reactions as $reaction ) :
		// Normalize
		$value  = sanitize_key( $reaction->value ?? '' );
		if ( ! $value ) {
			continue;
		}

		$label   = $reaction->label ?? ucfirst( $value );
		$outline = $reaction->outline ?? '';
		$filled  = $reaction->filled ?? '';

		$count  = count( $reactions_data[ $value ] ?? [] );
		$active = ( $user_reaction === $value );
		?>
		<button id="ecm-reaction-<?php echo esc_attr( $value ); ?>" type="submit"
			class="ecm-shortcode--reaction__button <?php echo $active ? 'ecm-shortcode--reaction__button--active' : ''; ?>">

			<div class="ecm-shortcode--reaction__icon <?php echo esc_attr( $active ? 'ecm--hidden' : '' ); ?>" id="ecm-reaction-outline-icon">
				<?php echo \EasyContentManager\Utils\Helper::kses_svg( $outline ); ?>
			</div>
			<div class="ecm-shortcode--reaction__icon <?php echo esc_attr( $active ? '' : 'ecm--hidden' ); ?>" id="ecm-reaction-filled-icon">
				<?php echo \EasyContentManager\Utils\Helper::kses_svg( $filled ); ?>
			</div>

			<span class="ecm-shortcode--reaction__label <?php echo $labels ? '' : 'ecm--hidden'; ?>"><?php echo esc_html( $label ); ?></span>
			<span class="ecm-shortcode--reaction__count"><?php echo intval( $count ); ?></span>

		</button>
	<?php endforeach; ?>

</div>
