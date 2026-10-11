<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $value ) ) {
	return;
}

$name  = $field['name'] ?? '';
$label = $field['label'] ?? $name;

// Normalize to array safely
if ( is_array( $value ) && isset( $value[0] ) ) {
	$users = $value; // Already multiple
} else {
	$users = [ $value ]; // Single item
}
?>

<div class="ecm-user ecm-user--<?php echo esc_attr( $name ); ?>">
	<?php if ( ! empty( $with_label ) ) : ?>
		<strong class="ecm-user__label"><?php echo esc_html( $label ); ?>:</strong>
	<?php endif; ?>

	<div class="ecm-user__list">
		<?php foreach ( $users as $user_item ) : ?>
			<?php
			$user_id     = 0;
			$user_name   = '';
			$user_bio    = '';
			$user_avatar = '';

			// Case 1: numeric ID
			if ( is_numeric( $user_item ) ) {
				$user_id   = (int) $user_item;
				$user_data = get_userdata( $user_id );
				if ( $user_data ) {
					$user_name   = $user_data->display_name;
					$user_bio    = get_user_meta( $user_id, 'description', true );
					$user_avatar = get_avatar_url( $user_id );
				}
			}
			// Case 2: array or object with user data
			elseif ( is_array( $user_item ) || is_object( $user_item ) ) {
				$user_id     = (int) ( $user_item['ID'] ?? ( $user_item->ID ?? 0 ) );
				$user_name   = $user_item['title'] ?? ( $user_item->title ?? '' );
				$user_bio    = $user_item['user_description'] ?? ( $user_item->user_description ?? '' );
				$user_avatar = $user_item['user_avatar'] ?? ( $user_item->user_avatar ?? get_avatar_url( $user_id ) );
			}

			// Skip invalid entries
			if ( ! $user_id || empty( $user_name ) ) {
				continue;
			}
			?>

			<div class="ecm-user__box author-box" data-user-id="<?php echo esc_attr( $user_id ); ?>">
				<?php if ( ! empty( $user_avatar ) ) : ?>
					<img src="<?php echo esc_url( $user_avatar ); ?>"
						 alt="<?php echo esc_attr( $user_name ); ?>"
						 class="ecm-user__avatar" />
				<?php endif; ?>

				<div class="ecm-user__info">
					<h3 class="ecm-user__name"><?php echo esc_html( $user_name ); ?></h3>
					<?php if ( ! empty( $user_bio ) ) : ?>
						<p class="ecm-user__description"><?php echo esc_html( $user_bio ); ?></p>
					<?php endif; ?>
				</div>
			</div>

		<?php endforeach; ?>
	</div>
</div>
