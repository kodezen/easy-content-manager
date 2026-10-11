<?php

namespace EasyContentManager\Classes\Factory;

use EasyContentManager\Admin\SeLicense;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class FieldFactory {




	/**
	 * Create a field instance based on the field type and post type.
	 *
	 * @param string $field_type The type of the field.
	 * @param string $post_type  The post type.
	 * @param string $meta_key   The meta key associated with the field (optional).
	 * @param string $object_type The object type, default is 'post' (optional).
	 * @throws \Exception If the field type is not registered.
	 * @return object|false
	 */
	public static function make( string $field_type, string $post_type = '', string $meta_key = '', string $object_type = 'post' ) {
		$premium_fields = [ 'gallery-field', 'repeater-field' ];

		if ( in_array( $field_type, $premium_fields, true ) && ! SeLicense::is_pro() ) {
			return false;
		}

		$class = self::get_field_class_name( $field_type, $meta_key );

		if ( ! class_exists( $class ) ) {
			throw new \Exception( "Class {$class} does not exist." );
		}

		return new $class( $post_type, $meta_key, $object_type );
	}

	protected static function get_field_class_name( string $field_type ): string {
		$field_type = str_replace( '_', '-', $field_type );
		$field_type = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $field_type ) ) );
		return "\\EasyContentManager\\Features\\Fields\\{$field_type}";
	}
}
