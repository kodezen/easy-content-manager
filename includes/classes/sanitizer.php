<?php

namespace EasyContentManager\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sanitizer {



	public static function sanitize_payload( $schema, $payload ) {
		$sanitized_payload = [];
		foreach ( $schema as $key => $type ) {
			if ( isset( $payload[ $key ] ) ) {
				switch ( $type ) {
					case 'integer':
						$sanitized_payload[ $key ] = (int) absint( sanitize_text_field( $payload[ $key ] ) );
						break;
					case 'float':
						$sanitized_payload[ $key ] = (float) floatval( sanitize_text_field( $payload[ $key ] ) );
						break;
					case 'string':
						$sanitized_payload[ $key ] = (string) sanitize_text_field( $payload[ $key ] );
						break;
					case 'url':
						$sanitized_payload[ $key ] = esc_url_raw( $payload[ $key ] );
						break;
					case 'boolean':
						$sanitized_payload[ $key ] = self::sanitize_checkbox_field( $payload[ $key ] );
						break;
					case 'array':
						$sanitized_payload[ $key ] = self::sanitize_array_field( $payload[ $key ] );
						break;
					case 'json':
						$sanitized_payload[ $key ] = self::sanitize_json_form_data( $payload[ $key ] );
						break;
					case 'post':
						$sanitized_payload[ $key ] = wp_kses_post( $payload[ $key ] );
						break;
					default:
						$sanitized_payload[ $key ] = sanitize_text_field( $payload[ $key ] );
						break;
				}//end switch
			}//end if
		}//end foreach

		return $sanitized_payload;
	}

	public static function sanitize_json_form_data( $data, $schema = [] ) {
		$data = is_array( $data ) ? $data : json_decode( $data, true );

		if ( is_array( $data ) ) {
			$results = [];
			$has_schema = ! empty( $schema );

			foreach ( $data as $key => $value ) {
				if ( $has_schema && ! isset( $schema[ $key ] ) ) {
					continue;
				}

				$sanitized_key = sanitize_key( $key );

				if ( is_array( $value ) ) {
					$results[ $sanitized_key ] = self::sanitize_json_form_data( $value, $schema[ $key ] ?? [] );
				} elseif ( is_int( $value ) || is_float( $value ) ) {
					$results[ $sanitized_key ] = $value;
				} elseif ( is_bool( $value ) ) {
					$results[ $sanitized_key ] = self::sanitize_checkbox_field( $value );
				} else {
					$results[ $sanitized_key ] = sanitize_text_field( $value );
				}
			}

			return $results;
		}//end if

		return is_bool( $data ) ? self::sanitize_checkbox_field( $data ) : sanitize_text_field( $data );
	}

	public static function sanitize_array_field( $array_data ) {
		$array_data = is_array( $array_data ) ? $array_data : json_decode( $array_data );
		$boolean = [ 'true', 'false', '1', '0' ];
		if ( is_array( $array_data ) ) {
			foreach ( $array_data as $key => &$value ) {
				if ( is_array( $value ) ) {
					$value = self::sanitize_array_field( $value );
				} else {
					$value = in_array( $value, $boolean, true ) || is_bool( $value ) ? self::sanitize_checkbox_field( $value ) : sanitize_text_field( $value );
				}
			}
		}

		return $array_data;
	}

	public static function sanitize_checkbox_field( $boolean ) {
		return (bool) filter_var( sanitize_text_field( $boolean ), FILTER_VALIDATE_BOOLEAN );
	}
}
