<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize option name by adding ecm_ prefix if not present
 *
 * @param string $option_name The option name
 * @return string Normalized option name with ecm_ prefix
 */
if ( ! function_exists( 'ecm_normalize_option_name' ) ) {
	function ecm_normalize_option_name( $option_name ) {
		if ( empty( $option_name ) ) {
			return '';
		}
	
		// If option name doesn't start with 'ecm_', add it
		if ( strpos( $option_name, 'ecm_' ) !== 0 ) {
			return 'ecm_' . $option_name;
		}
	
		return $option_name;
	}
}

/**
 * Get the settings schema from registry
 *
 * @param string $option_name The option name
 * @param string $route The route name
 * @param string $field_name The field name (optional)
 * @return array Field schema or empty array
 */
if ( ! function_exists( 'ecm_get_setting_schema' ) ) {
	function ecm_get_setting_schema( $option_name, $route, $field_name = null ) {
		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );
	
		// Build registry key
		$key = "setting_{$option_name}_{$route}";
	
		if ( $field_name ) {
			$key .= "_{$field_name}";
		}
	
		return $GLOBALS['ecm_settings_registry'][ $key ] ?? [];
	}
}

/**
 * Format setting value based on field type
 *
 * @param mixed $value The raw value
 * @param array $schema The field schema
 * @return mixed Formatted value
 */
if ( ! function_exists( 'ecm_format_setting_value' ) ) {
	function ecm_format_setting_value( $value, array $schema ) {
		if ( empty( $schema['type'] ) && empty( $schema['field_type'] ) ) {
			return $value;
		}

		// Use 'field_type' if provided, fallback to 'type'
		$field_type = $schema['field_type'] ?? $schema['type'];

		switch ( $field_type ) {
			case 'image':
				if ( empty( $value ) ) {
					return null;
				}
				$id = is_array( $value ) ? (int) $value[0] : (int) $value;
				return [
					'id'      => $id,
					'url'     => wp_get_attachment_url( $id ),
					'alt'     => get_post_meta( $id, '_wp_attachment_image_alt', true ),
					'title'   => get_the_title( $id ),
					'caption' => wp_get_attachment_caption( $id ),
					'sizes'   => wp_get_attachment_metadata( $id )['sizes'] ?? [],
				];

			case 'gallery':
				if ( empty( $value ) ) {
					return [];
				}
				$ids = is_array( $value ) ? $value : explode( ',', $value );
				return array_map(
					function( $id ) {
						$id = (int) $id;
						return [
							'id'  => $id,
							'url' => wp_get_attachment_url( $id ),
							'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
						];
					},
					array_filter( $ids )
				);

			case 'file':
				if ( empty( $value ) ) {
					return null;
				}
				$id = is_array( $value ) ? (int) $value[0] : (int) $value;
				return [
					'id'   => $id,
					'url'  => wp_get_attachment_url( $id ),
					'filename' => basename( get_attached_file( $id ) ),
				];

			case 'wysiwyg':
			case 'rich_text':
				return wpautop( $value );

			case 'true_false':
			case 'checkbox':
				return (bool) $value;

			case 'number':
				return is_numeric( $value ) ? (float) $value : $value;

			case 'select':
			case 'radio':
			case 'text':
			case 'textarea':
			case 'email':
			case 'url':
			default:
				return $value;
		}
	}
}

/**
 * Get a specific setting value
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string|null $key The specific key to retrieve (supports dot notation for nested values)
 * @param bool $format Whether to format the value based on field type
 * @return mixed The setting value or null if not found
 */
if ( ! function_exists( 'ecm_get_setting' ) ) {
	function ecm_get_setting( $option_name, $route, $key = null, $format = true ) {
		if ( empty( $option_name ) || empty( $route ) ) {
			return null;
		}

		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );

		// Get the option data
		$option_data = get_option( $option_name, [] );
		$option_data = maybe_unserialize( $option_data );

		// Check if route exists
		if ( ! isset( $option_data[ $route ] ) ) {
			return null;
		}

		$route_data = $option_data[ $route ];

		// If no key specified, return entire route data
		if ( $key === null ) {
			return $route_data;
		}

		// Support dot notation for nested values (e.g., 'group.field')
		$keys = array_map( 'trim', explode( '.', $key ) );
		$value = $route_data;

		foreach ( $keys as $k ) {
			if ( ! is_array( $value ) || ! array_key_exists( $k, $value ) ) {
				return null;
			}
			$value = $value[ $k ];
		}

		// Format the value if requested
		if ( $format ) {
			$schema = ecm_get_setting_schema( $option_name, $route, $key );
			$value = ecm_format_setting_value( $value, $schema );
		}

		return $value;
	}
}

/**
 * Echo a setting value
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string|null $key The specific key to retrieve
 * @param bool $format Whether to format the value
 */
if ( ! function_exists( 'ecm_the_setting' ) ) {
	function ecm_the_setting( $option_name, $route, $key = null, $format = true ) {
		$value = ecm_get_setting( $option_name, $route, $key, $format );
	
		// Handle different value types for output
		if ( is_array( $value ) ) {
			echo esc_html( wp_json_encode( $value ) );
		} elseif ( is_bool( $value ) ) {
			echo $value ? 'true' : 'false';
		} else {
			echo esc_html( $value );
		}
	}
}

/**
 * Get all settings for a route
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string|null $field_name Optional field name to get specific field from meta
 * @param bool $format Whether to format values
 * @return array All settings for the route or specific field data
 */
if ( ! function_exists( 'ecm_get_settings' ) ) {
	function ecm_get_settings( $option_name, $route, $field_name = null, $format = true ) {
		if ( empty( $option_name ) || empty( $route ) ) {
			return [];
		}

		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );

		$option_data = get_option( $option_name, [] );
		$option_data = maybe_unserialize( $option_data );

		if ( ! isset( $option_data[ $route ] ) ) {
			return [];
		}

		$route_data = $option_data[ $route ];

		// If field_name is provided, get data from meta first, then direct field
		if ( $field_name !== null ) {
			// First check if 'meta' exists and contains the field
			if ( isset( $route_data['meta'][ $field_name ] ) ) {
				$value = $route_data['meta'][ $field_name ];
			} 
			// Otherwise check direct field
			elseif ( isset( $route_data[ $field_name ] ) ) {
				$value = $route_data[ $field_name ];
			} else {
				return null;
			}

			// Format the value if requested
			if ( $format ) {
				$schema = ecm_get_setting_schema( $option_name, $route, $field_name );
				$value = ecm_format_setting_value( $value, $schema );
			}

			return $value;
		}

		// If no field_name, return all settings (but remove 'meta' wrapper duplication)
		// Check if 'meta' key exists and contains the actual fields
		if ( isset( $route_data['meta'] ) && is_array( $route_data['meta'] ) ) {
			$route_data = $route_data['meta'];
		}

		// If format is true, format each field value
		if ( $format && is_array( $route_data ) ) {
			foreach ( $route_data as $key => $value ) {
				$schema = ecm_get_setting_schema( $option_name, $route, $key );
				$route_data[ $key ] = ecm_format_setting_value( $value, $schema );
			}
		}

		return $route_data;
	}
}

/**
 * Get setting field object with metadata
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string $key The field key
 * @return array|false Field object or false if not found
 */
if ( ! function_exists( 'ecm_get_setting_object' ) ) {
	function ecm_get_setting_object( $option_name, $route, $key ) {
		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );
	
		$schema = ecm_get_setting_schema( $option_name, $route, $key );
	
		if ( ! $schema ) {
			return false;
		}

		$value = ecm_get_settings( $option_name, $route, $key, false );

		return [
			'key'        => $key,
			'label'      => $schema['label'] ?? $schema['description'] ?? $key,
			'name'       => $key,
			'type'       => $schema['type'] ?? 'text',
			'field_type' => $schema['field_type'] ?? $schema['type'] ?? 'text',
			'value'      => ecm_format_setting_value( $value, $schema ),
			'raw'        => $value,
			'schema'     => $schema,
		];
	}
}

/**
 * Get all setting objects for a route with metadata
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @return array Array of setting objects
 */
if ( ! function_exists( 'ecm_get_setting_objects' ) ) {
	function ecm_get_setting_objects( $option_name, $route ) {
		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );
	
		$route_data = ecm_get_settings( $option_name, $route, null, false );
		$objects = [];

		foreach ( $route_data as $key => $value ) {
			$objects[ $key ] = ecm_get_setting_object( $option_name, $route, $key );
		}

		return $objects;
	}
}

/**
 * Update a setting value
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string $key The field key
 * @param mixed $value The new value
 * @return bool True on success, false on failure
 */
if ( ! function_exists( 'ecm_update_setting' ) ) {
	function ecm_update_setting( $option_name, $route, $key, $value ) {
		if ( empty( $option_name ) || empty( $route ) || empty( $key ) ) {
			return false;
		}

		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );

		$option_data = get_option( $option_name, [] );
		$option_data = maybe_unserialize( $option_data );

		if ( ! isset( $option_data[ $route ] ) ) {
			$option_data[ $route ] = [];
		}

		// Update in meta if it exists there, otherwise update directly
		if ( isset( $option_data[ $route ]['meta'] ) ) {
			$option_data[ $route ]['meta'][ $key ] = $value;
			// Also update direct field for backward compatibility
			$option_data[ $route ][ $key ] = $value;
		} else {
			$option_data[ $route ][ $key ] = $value;
		}

		return update_option( $option_name, maybe_serialize( $option_data ) );
	}
}

/**
 * Delete a setting value
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string $key The field key
 * @return bool True on success, false on failure
 */
if ( ! function_exists( 'ecm_delete_setting' ) ) {
	function ecm_delete_setting( $option_name, $route, $key ) {
		if ( empty( $option_name ) || empty( $route ) || empty( $key ) ) {
			return false;
		}

		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );

		$option_data = get_option( $option_name, [] );
		$option_data = maybe_unserialize( $option_data );

		// Delete from meta if it exists there
		if ( isset( $option_data[ $route ]['meta'][ $key ] ) ) {
			unset( $option_data[ $route ]['meta'][ $key ] );
		}
	
		// Also delete from direct field
		if ( isset( $option_data[ $route ][ $key ] ) ) {
			unset( $option_data[ $route ][ $key ] );
		}

		return update_option( $option_name, maybe_serialize( $option_data ) );
	}
}

/**
 * Check if a setting exists
 *
 * @param string $option_name The option name (ecm_ prefix will be added if not present)
 * @param string $route The route name
 * @param string|null $key The field key (optional)
 * @return bool True if exists, false otherwise
 */
if ( ! function_exists( 'ecm_setting_exists' ) ) {
	function ecm_setting_exists( $option_name, $route, $key = null ) {
		// Normalize option name
		$option_name = ecm_normalize_option_name( $option_name );
	
		$option_data = get_option( $option_name, [] );
		$option_data = maybe_unserialize( $option_data );

		if ( ! isset( $option_data[ $route ] ) ) {
			return false;
		}

		if ( $key === null ) {
			return true;
		}

		// Check in meta first, then direct
		return isset( $option_data[ $route ]['meta'][ $key ] ) || isset( $option_data[ $route ][ $key ] );
	}
}