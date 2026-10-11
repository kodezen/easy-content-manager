<?php

/**
 * Easy Content Manager - Frontend User Meta API
 *
 * User meta field retrieval functions following ECM patterns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['ecm_user_repeater_loop'] = [];

/**
 * Get a valid user ID
 *
 * @param int|null $user_id User ID or null for current user
 * @return int Valid user ID
 */
if ( ! function_exists( 'ecm_get_valid_user_id' ) ) {
	function ecm_get_valid_user_id( $user_id = null ) {
		if ( $user_id ) {
			return (int) $user_id;
		}
		return get_current_user_id();
	}
}

/**
 * Get user field schema from registry
 *
 * @param string $field_name Field name
 * @return array Field schema
 */
if ( ! function_exists( 'ecm_get_user_field_schema' ) ) {
	function ecm_get_user_field_schema( string $field_name ) {
		$key = "user_{$field_name}";
		return $GLOBALS['ecm_field_registry'][ $key ] ?? [];
	}
}

/**
 * Format user meta value based on field type
 *
 * @param mixed $value Raw value
 * @param array $schema Field schema
 * @return mixed Formatted value
 */
if ( ! function_exists( 'ecm_format_user_value' ) ) {
	function ecm_format_user_value( $value, array $schema ) {
		if ( empty( $schema['type'] ) ) {
			return $value;
		}

		$field_type = $schema['field_type'] ?? $schema['type'];

		switch ( $field_type ) {
			case 'image':
				// A single-image value may arrive as an array, an ID or an empty
				// string; `$value[0]` on a string reads its first character and on an
				// empty value emits a warning.
				$id = (int) ( is_array( $value ) ? ( $value[0] ?? 0 ) : $value );

				if ( ! $id ) {
					return null;
				}

				return [
					'id'      => $id,
					'url'     => wp_get_attachment_url( $id ),
					'alt'     => get_post_meta( $id, '_wp_attachment_image_alt', true ),
					'title'   => get_the_title( $id ),
					'caption' => wp_get_attachment_caption( $id ),
					'sizes'   => wp_get_attachment_metadata( $id )['sizes'] ?? [],
				];

			case 'gallery':
				$ids = is_array( $value ) ? $value : explode( ',', $value );
				return array_map(
					fn( $id ) => wp_get_attachment_image_src( (int) $id, 'full' ),
					array_filter( $ids )
				);

			case 'file':
				return wp_get_attachment_url( (int) $value );

			default:
				return $value;
		}
	}
}

/**
 * Get user meta field value
 *
 * @param string $field_name Field name
 * @param int|null $user_id User ID
 * @param bool $format Whether to format the value
 * @return mixed Field value
 */
if ( ! function_exists( 'ecm_get_user_field' ) ) {
	function ecm_get_user_field( $field_name, $user_id = null, $format = true ) {
		$user_id = ecm_get_valid_user_id( $user_id );
		$value = get_user_meta( $user_id, $field_name, true );
	
		if ( $format ) {
			$schema = ecm_get_user_field_schema( $field_name );
			$value = ecm_format_user_value( $value, $schema );
		}
	
		return $value;
	}
}

/**
 * Echo user meta field value
 *
 * @param string $field_name Field name
 * @param int|null $user_id User ID
 * @param bool $format Whether to format the value
 */
if ( ! function_exists( 'ecm_the_user_field' ) ) {
	function ecm_the_user_field( $field_name, $user_id = null, $format = true ) {
		echo esc_html( ecm_get_user_field( $field_name, $user_id, $format ) );
	}
}

/**
 * Get every ECM user field for a user.
 *
 * Scoped to the meta keys belonging to a user-located ECM field group — the
 * same allowlist the read and write paths use (Ajax\User, Hooks). It previously
 * returned raw get_user_meta(), i.e. `wp_capabilities`, `session_tokens` and
 * every other plugin's private user data along with it.
 *
 * @param int|null $user_id User ID
 * @return array<string,mixed> meta key => value
 */
if ( ! function_exists( 'ecm_get_user_fields' ) ) {
	function ecm_get_user_fields( $user_id = null ) {
		$user_id = ecm_get_valid_user_id( $user_id );
		$allowed = \EasyContentManager\Utils\Helper::get_user_scoped_custom_field_meta_keys();
		$meta    = get_user_meta( $user_id );
		$fields  = [];

		foreach ( $allowed as $key ) {
			if ( isset( $meta[ $key ][0] ) ) {
				$fields[ $key ] = maybe_unserialize( $meta[ $key ][0] );
			}
		}

		return $fields;
	}
}

/**
 * Get user field object with schema and formatted value
 *
 * @param string $field_name Field name
 * @param int|null $user_id User ID
 * @return array|false Field object or false
 */
if ( ! function_exists( 'ecm_get_user_field_object' ) ) {
	function ecm_get_user_field_object( $field_name, $user_id = null ) {
		$user_id = ecm_get_valid_user_id( $user_id );
		$schema = ecm_get_user_field_schema( $field_name );
	
		if ( ! $schema ) {
			return false;
		}
	
		$value = get_user_meta( $user_id, $field_name, true );
	
		return [
			'key'        => $field_name,
			'label'      => $schema['description'] ?? $field_name,
			'name'       => $field_name,
			'type'       => $schema['type'] ?? 'text',
			'field_type' => $schema['field_type'] ?? 'text',
			'value'      => ecm_format_user_value( $value, $schema ),
			'raw'        => $value,
			'schema'     => $schema
		];
	}
}

/**
 * Get all user field objects with schema
 *
 * @param int|null $user_id User ID
 * @return array Field objects
 */
if ( ! function_exists( 'ecm_get_user_field_objects' ) ) {
	function ecm_get_user_field_objects( $user_id = null ) {
		$user_id = ecm_get_valid_user_id( $user_id );
		$fields  = [];

		foreach ( array_keys( ecm_get_user_fields( $user_id ) ) as $key ) {
			$field_object = ecm_get_user_field_object( $key, $user_id );
			if ( $field_object ) {
				$fields[ $key ] = $field_object;
			}
		}

		return $fields;
	}
}

/**
 * Check if user repeater field has rows
 *
 * @param string $field_name Field name
 * @param int|null $user_id User ID
 * @return bool Whether rows exist
 */
if ( ! function_exists( 'ecm_user_have_rows' ) ) {
	function ecm_user_have_rows( $field_name, $user_id = null ) {
		$rows = ecm_get_user_field( $field_name, $user_id, false );
	
		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return false;
		}
	
		$GLOBALS['ecm_user_repeater_loop'] = [
			'field'   => $field_name,
			'user_id' => ecm_get_valid_user_id( $user_id ),
			'rows'    => $rows,
			'index'   => -1
		];
	
		return true;
	}
}

/**
 * Advance to next row in user repeater loop
 *
 * @return bool Whether row exists
 */
if ( ! function_exists( 'ecm_user_the_row' ) ) {
	function ecm_user_the_row() {
		$GLOBALS['ecm_user_repeater_loop']['index']++;
		return isset( $GLOBALS['ecm_user_repeater_loop']['rows'][ $GLOBALS['ecm_user_repeater_loop']['index'] ] );
	}
}

/**
 * Get current user repeater row index
 *
 * @return int Current row index
 */
if ( ! function_exists( 'ecm_get_user_row_index' ) ) {
	function ecm_get_user_row_index() {
		return $GLOBALS['ecm_user_repeater_loop']['index'] ?? -1;
	}
}

/**
 * Get current user repeater row data
 *
 * @return array Current row data
 */
if ( ! function_exists( 'ecm_get_user_row' ) ) {
	function ecm_get_user_row() {
		$loop = $GLOBALS['ecm_user_repeater_loop'] ?? [];
		return $loop['rows'][ $loop['index'] ] ?? [];
	}
}

/**
 * Get user sub field value from current repeater row
 *
 * @param string $sub_field_name Sub field name
 * @return mixed Sub field value
 */
if ( ! function_exists( 'ecm_get_user_sub_field' ) ) {
	function ecm_get_user_sub_field( $sub_field_name ) {
		$row = ecm_get_user_row();
		return $row[ $sub_field_name ] ?? null;
	}
}

/**
 * Echo user sub field value
 *
 * @param string $sub_field_name Sub field name
 */
if ( ! function_exists( 'ecm_the_user_sub_field' ) ) {
	function ecm_the_user_sub_field( $sub_field_name ) {
		echo esc_html( ecm_get_user_sub_field( $sub_field_name ) );
	}
}

/**
 * Get user sub field object
 *
 * @param string $sub_field_name Sub field name
 * @return array Sub field object
 */
if ( ! function_exists( 'ecm_get_user_sub_field_object' ) ) {
	function ecm_get_user_sub_field_object( $sub_field_name ) {
		$row = ecm_get_user_row();
		return [
			'name'  => $sub_field_name,
			'value' => $row[ $sub_field_name ] ?? null,
		];
	}
}

/**
 * Get user group field
 *
 * @param string $field_name Group field name
 * @param int|null $user_id User ID
 * @return array Group data
 */
if ( ! function_exists( 'ecm_get_user_group' ) ) {
	function ecm_get_user_group( $field_name, $user_id = null ) {
		$group = ecm_get_user_field( $field_name, $user_id, false );
		return is_array( $group ) ? $group : [];
	}
}

/**
 * Get nested value from user group field
 *
 * @param string $group_name Group field name
 * @param string $sub_field_path Dot-separated path (e.g., 'address.street')
 * @param int|null $user_id User ID
 * @return mixed Field value or null
 */
if ( ! function_exists( 'ecm_get_user_group_field' ) ) {
	function ecm_get_user_group_field( $group_name, $sub_field_path, $user_id = null ) {
		$group = ecm_get_user_group( $group_name, $user_id );

		$keys = explode( '.', $sub_field_path );
		$value = $group;
	
		foreach ( $keys as $key ) {
			if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
				return null;
			}
			$value = $value[ $key ];
		}
	
		return $value;
	}
}

/**
 * Echo user group field value
 *
 * @param string $group_name Group field name
 * @param string $sub_field_name Sub field name
 * @param int|null $user_id User ID
 */
if ( ! function_exists( 'ecm_the_user_group_field' ) ) {
	function ecm_the_user_group_field( $group_name, $sub_field_name, $user_id = null ) {
		echo esc_html( ecm_get_user_group_field( $group_name, $sub_field_name, $user_id ) );
	}
}

/**
 * Update user meta field
 *
 * @param string $field_name Field name
 * @param mixed $value Field value
 * @param int|null $user_id User ID
 * @return int|bool Meta ID on success, false on failure
 */
if ( ! function_exists( 'ecm_update_user_field' ) ) {
	function ecm_update_user_field( $field_name, $value, $user_id = null ) {
		return update_user_meta( ecm_get_valid_user_id( $user_id ), $field_name, $value );
	}
}

/**
 * Delete user meta field
 *
 * @param string $field_name Field name
 * @param int|null $user_id User ID
 * @return bool True on success, false on failure
 */
if ( ! function_exists( 'ecm_delete_user_field' ) ) {
	function ecm_delete_user_field( $field_name, $user_id = null ) {
		return delete_user_meta( ecm_get_valid_user_id( $user_id ), $field_name );
	}
}