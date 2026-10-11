<?php

namespace EasyContentManager\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EcmFields {


	public function __construct() {
		add_shortcode( 'ecm_field', [ $this, 'render_ecm_field' ] );
	}

	/**
	 * Whether the current visitor may read this post.
	 *
	 * `current_user_can( 'read_post', $id )` is NOT the right question on the
	 * front end: for a public post map_meta_cap() reduces it to the primitive
	 * `read` capability, which a logged-out visitor does not hold — WP_User with
	 * ID 0 has no capabilities at all. Checking it alone made every
	 * `[ecm_field post_id="…"]` render an empty string for the entire logged-out
	 * audience. Public statuses are readable by everyone; anything else falls
	 * back to the real capability check.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	private static function is_post_readable( int $post_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		if ( ! empty( $post->post_password ) ) {
			return ! post_password_required( $post );
		}

		$status = get_post_status_object( get_post_status( $post ) );

		if ( $status && $status->public ) {
			return true;
		}

		return current_user_can( 'read_post', $post_id );
	}

	public function render_ecm_field( $atts ) {
		$atts = shortcode_atts([
			'key' => '',
			'post_id' => '',
			'format' => true,
			'label' => false,
		], $atts, 'ecm_field');

		if ( empty( $atts['key'] ) ) {
			return '';
		}

		// Never expose protected (underscore-prefixed) meta through the shortcode.
		if ( is_protected_meta( $atts['key'], 'post' ) ) {
			return '';
		}

		$has_explicit_post = ! empty( $atts['post_id'] );
		$post_id           = $has_explicit_post ? absint( $atts['post_id'] ) : get_the_ID();

		// When a caller targets an arbitrary post, require read access to it so a
		// low-privilege author can't pull another post's field values by ID.
		if ( $has_explicit_post && ! self::is_post_readable( $post_id ) ) {
			return '';
		}

		$value = ecm_get_field( $atts['key'], $post_id, (bool) $atts['format'] );
		if ( empty( $value ) ) {
			return '';
		}

		$field_obj = ecm_get_field_object( $atts['key'], $post_id );
		$field_type = $field_obj['field_type'] ?? 'text';

		$template = locate_template( "ecm/{$field_type}-field.php" );
		if ( ! $template ) {
			$template = EASY_CONTENT_MANAGER_ROOT_DIR_PATH . "templates/{$field_type}-field.php";
		}

		$template = apply_filters( 'ecm_shortcode_field_template_path', $template, $field_type, $field_obj, $post_id );

		ob_start();
		if ( file_exists( $template ) ) {
			$field       = $field_obj;
			$value       = $field['value'];
			$with_label  = filter_var( $atts['label'], FILTER_VALIDATE_BOOLEAN );
			include $template;
		} else {
			echo esc_html( is_array( $value ) ? implode( ', ', $value ) : $value );
		}
		return ob_get_clean();
	}
}
