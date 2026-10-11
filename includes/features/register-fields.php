<?php

namespace EasyContentManager\Features;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class RegisterFields {



	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'register_fields' ] );
		add_action( 'init', [ $self, 'register_field_group' ] );
	}

	public function register_fields() {
		$cached_fields = get_transient( 'ecm_cached_prepared_custom_fields' );

		if ( false === $cached_fields || empty( $cached_fields ) ) {
			$post_types         = Helper::get_all_custom_post_types();
			$taxonomies         = Helper::get_all_custom_taxonomies();

			$posts = array_merge( $post_types, $taxonomies );

			$cached_fields = self::prepare_field_for_register( $posts );

			// Cache without expiration (0 = no auto-expire)
			set_transient( 'ecm_cached_prepared_custom_fields', $cached_fields );
		}

		$local_post_type = ecm_get_json_from_files( ecm_get_plugins_post_type_path() ) ?? [];

		$theme_post_type = [];
		if ( Helper::has_theme_support() ) {
			$theme_post_type = ecm_load_json_files_from_folders( [ Helper::has_theme_support() . '/post-types' ] );
		}

		$all_fields = array_merge(
			$cached_fields,
			self::prepare_field_for_register( array_merge( $local_post_type, $theme_post_type ), true )
		);

		foreach ( $all_fields as $field ) {
			$args = $field['post_meta_args'];
			// Applied here (not in the cached array) because the sanitizer is a Closure
			// and would break transient serialization if cached.
			$args['sanitize_callback'] = self::get_sanitizer_for_field_type( $args['field_type'] ?? '' );
			register_meta(
				$field['object_type'],
				$field['meta_key'],
				$args
			);
		}
	}

	public function register_field_group() {
		$cached_field_groups = get_transient( 'ecm_cached_prepared_field_groups' );
		if ( false === $cached_field_groups ) {
			$field_groups        = Helper::get_all_ecm_field_groups();
			$cached_field_groups = [];
			foreach ( $field_groups as $field_group ) {
				$post_content   = maybe_unserialize( $field_group->post_content );
				$all_locations  = (array) maybe_unserialize(
					get_post_meta( $field_group->ID, 'ecm_field_group_location', true )
				);
				// post type location
				$locations = $all_locations['post_type'] ?? [];
				// taxonomy location
				$taxonomy_locations = $all_locations['taxonomies'] ?? [];
				// user location
				$current_user_location = $all_locations['current_user'] ?? [];
				// user role location
				$user_role_locations = $all_locations['current_user_role'] ?? [];

				if ( empty( $post_content['custom_fields'] ) || ! is_array( $post_content['custom_fields'] ) ) {
					continue;
				}
				foreach ( $post_content['custom_fields'] as $field ) {
					if ( ! is_array( $field ) || empty( $field['meta_key'] ) || empty( $field['object_type'] ) ) {
						continue;
					}
					$post_meta_args = isset( $field['post_meta_args'] ) && is_array( $field['post_meta_args'] )
					? $field['post_meta_args']
					: [];
					$post_meta_args = wp_parse_args(
						$post_meta_args,
						[
							'type'              => 'string',
							'single'            => true,
							'show_in_rest'      => true,
							'sanitize_callback' => 'sanitize_text_field',
							'auth_callback'     => '__return_true',
						]
					);
					if ( isset( $post_meta_args['type'], $post_meta_args['default'] ) ) {
						$type    = $post_meta_args['type'];
						$default = $post_meta_args['default'];
						switch ( $type ) {
							case 'integer':
								$post_meta_args['default'] = is_numeric( $default ) ? (int) $default : 0;
								break;
							case 'boolean':
								$post_meta_args['default'] = (bool) $default;
								break;
							case 'array':
								$post_meta_args['default'] = is_array( $default ) ? $default : [];
								break;
							case 'string':
							default:
								$post_meta_args['default'] = (string) $default;
								break;
						}
					}

					foreach ( $locations as $location ) {
						$args                   = $post_meta_args;
						$args['object_subtype'] = $location;
						$cached_field_groups[]  = [
							'object_type'    => $field['object_type'],
							'meta_key'       => $field['meta_key'],
							'post_meta_args' => $args,
						];
					}

					foreach ( $taxonomy_locations as $taxonomy ) {
						$args                   = $post_meta_args;
						$args['object_subtype'] = $taxonomy;
						$cached_field_groups[]  = [
							'object_type'    => 'term', // taxonomy meta
							'meta_key'       => $field['meta_key'],
							'post_meta_args' => $args,
						];
					}

					if ( in_array( 'logged_in', $current_user_location, true ) ) {
						$args                  = $post_meta_args;
						$cached_field_groups[] = [
							'object_type'    => 'user',
							'meta_key'       => $field['meta_key'],
							'post_meta_args' => $args,
							'user_condition' => 'logged_in',
						];
					}

					foreach ( $user_role_locations as $user_role ) {
						$args                  = $post_meta_args;
						$cached_field_groups[] = [
							'object_type'    => 'user',
							'meta_key'       => $field['meta_key'],
							'post_meta_args' => $args,
							'user_condition' => 'role',
							'user_role'      => $user_role,
						];
					}
				}//end foreach
			}//end foreach
			set_transient( 'ecm_cached_prepared_field_groups', $cached_field_groups );
		}//end if

		foreach ( $cached_field_groups as $field ) {
			// Applied post-cache because the sanitizer is a Closure (uncacheable).
			$field['post_meta_args']['sanitize_callback'] = self::get_sanitizer_for_field_type( $field['post_meta_args']['field_type'] ?? '' );

			if ( 'user' === $field['object_type'] && isset( $field['user_condition'] ) ) {
				$args = $field['post_meta_args'];

				if ( 'logged_in' === $field['user_condition'] ) {
					$args['auth_callback'] = function ( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
						return is_user_logged_in() && ( $object_id === $user_id || current_user_can( 'edit_users' ) );
					};
				} elseif ( 'role' === $field['user_condition'] && ! empty( $field['user_role'] ) ) {
					$role                  = $field['user_role'];
					$args['auth_callback'] = function ( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) use ( $role ) {
						$user = get_userdata( $object_id );
						if ( ! $user ) {
							return false;
						}

						$has_role = in_array( $role, (array) $user->roles, true );
						
						return $has_role && ( $object_id === $user_id || current_user_can( 'edit_users' ) );
					};
				}

				register_meta(
					'user',
					$field['meta_key'],
					$args
				);
			} else {
				register_meta(
					$field['object_type'],
					$field['meta_key'],
					$field['post_meta_args']
				);
			}
		}
	}


	/**
	 * Resolve a per-field-type sanitize callback at registration time.
	 *
	 * The field config is authored in a React app and JSON-round-tripped, so any
	 * closure/`sanitize_callback` set on the PHP field handler is lost (a Closure
	 * JSON-encodes to null) and the stored `null` then overrides the parse default.
	 * That left custom-field meta stored RAW → stored XSS for anyone able to edit a
	 * post. We rebuild the callback here, in PHP, keyed on the field type. The
	 * returned closure is only held in memory by register_meta(), never serialized.
	 *
	 * @param string $field_type
	 * @return callable
	 */
	public static function get_sanitizer_for_field_type( string $field_type ) {
		switch ( $field_type ) {
			case 'url':
			case 'page-link':
			case 'page_link':
			case 'oembed':
			case 'video':
				$scalar = 'esc_url_raw';
				break;
			case 'email':
				$scalar = 'sanitize_email';
				break;
			// Everything else (text, textarea, rich-text/wysiwyg, select, radio,
			// checkbox, color, icon, date, map, relationship, repeater, group, …)
			// goes through wp_kses_post: it preserves legitimate HTML in rich fields
			// but strips <script>, event handlers and other XSS vectors.
			default:
				$scalar = 'wp_kses_post';
				break;
		}

		return static function ( $value ) use ( $scalar ) {
			return self::deep_sanitize_meta( $value, $scalar );
		};
	}

	/**
	 * Recursively apply a scalar sanitizer to a meta value (handles repeater/group
	 * arrays), leaving non-string scalars (int/float/bool/null) untouched.
	 *
	 * @param mixed    $value
	 * @param callable $scalar
	 * @return mixed
	 */
	public static function deep_sanitize_meta( $value, $scalar ) {
		if ( is_array( $value ) ) {
			return array_map(
				static function ( $item ) use ( $scalar ) {
					return self::deep_sanitize_meta( $item, $scalar );
				},
				$value
			);
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		return call_user_func( $scalar, $value );
	}

	public static function prepare_field_for_register( array $posts, bool $is_local = false ) {
		$cached_fields = [];

		foreach ( $posts as $post ) {
			$post_content = $is_local ? $post['post_content'] : maybe_unserialize( $post->post_content );

			if ( empty( $post_content['custom_fields'] ) || ! is_array( $post_content['custom_fields'] ) ) {
				continue;
			}

			foreach ( $post_content['custom_fields'] as $field ) {
				if ( ! is_array( $field ) || empty( $field['meta_key'] ) || empty( $field['object_type'] ) ) {
					continue;
				}

				// Normal field
				$post_meta_args = isset( $field['post_meta_args'] ) && is_array( $field['post_meta_args'] )
					? $field['post_meta_args']
					: [];

				$post_meta_args = wp_parse_args(
					$post_meta_args,
					[
						'type'              => 'string',
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => 'sanitize_text_field',
						'auth_callback'     => '__return_true',
					]
				);

				// Normalize default values
				if ( isset( $post_meta_args['type'], $post_meta_args['default'] ) ) {
					$type    = $post_meta_args['type'];
					$default = $post_meta_args['default'];

					switch ( $type ) {
						case 'integer':
							$post_meta_args['default'] = is_numeric( $default ) ? (int) $default : 0;
							break;
						case 'boolean':
							$post_meta_args['default'] = (bool) $default;
							break;
						case 'array':
							$post_meta_args['default'] = is_array( $default ) ? $default : [];
							break;
						case 'string':
						default:
							$post_meta_args['default'] = (string) $default;
							break;
					}
				}

				if ( 'post' === $field['object_type'] ) {
					$cached_fields[] = [
						'object_type'    => 'post',
						'meta_key'       => $field['meta_key'],
						'post_meta_args' => $post_meta_args,
					];
				}

				if ( 'term' === $field['object_type'] ) {
					$cached_fields[] = [
						'object_type'    => 'term',
						'meta_key'       => $field['meta_key'],
						'post_meta_args' => $post_meta_args,
					];
				}
			}//end foreach
		}//end foreach

		return $cached_fields;
	}
}
