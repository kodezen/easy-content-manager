<?php

namespace EasyContentManager\Utils\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait Fields {


	public static function build_field_registry_cache( $fields ) {
		foreach ( $fields as $field ) {
			if ( empty( $field['meta_key'] ) || empty( $field['post_meta_args'] ) || empty( $field['object_type'] ) ) {
				continue;
			}

			$subtype = $field['post_meta_args']['object_subtype'] ?? '';
			$key     = "{$field['object_type']}_{$subtype}_{$field['meta_key']}";

			$GLOBALS['ecm_field_registry'][ $key ] = $field['post_meta_args'];
		}
	}
}
