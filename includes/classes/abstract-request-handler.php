<?php

namespace EasyContentManager\Classes;

use Exception;
use stdClass;
use ContentManager\Classes\Exception\EcmException;
use EasyContentManager\Utils\Helper;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractRequestHandler {


	/**
	 * Default Nonce Action.
	 *
	 * @var string
	 */
	protected string $nonce_action = 'easy_content_manager_nonce';

	/**
	 * Request namespace.
	 *
	 * @var string
	 */
	protected string $namespace = EASY_CONTENT_MANAGER_PLUGIN_SLUG;

	/**
	 * Actions to handle.
	 *
	 * @var array
	 */
	protected array $actions = array();

	/**
	 * Capability required by any action in this handler that doesn't declare its
	 * own `capability`.
	 *
	 * Handlers override this with the ECM capability that matches the screen
	 * they serve (e.g. `Capabilities::MANAGE_POST_TYPES`), and individual
	 * read-only actions opt down to `Capabilities::VIEW_APP`. The default stays
	 * `manage_options` so a new handler that forgets to classify itself fails
	 * closed rather than open.
	 *
	 * @var string
	 */
	protected string $default_capability = 'manage_options';

	protected bool $is_ajax = false;

	abstract public function __construct();

	/**
	 * Run action hook.
	 *
	 * @return void
	 */
	abstract public function dispatch_actions();

	protected function is_ajax_action(): bool {
		return $this->is_ajax || str_starts_with( current_action(), 'wp_ajax_' );
	}

	/**
	 * Handle action callback.
	 *
	 * @return void
	 */
	final public function handle_request() {
		try {
			// No caching.
			nocache_headers();

			$response = $this->prepare_response();
			if ( is_wp_error( $response ) ) {
				$this->respond_error( $response );
			}

			$this->respond_success( $response );
		} catch ( EcmException $e ) {
			$this->respond_error( $e->toWpError() );
		} catch ( Exception $e ) {
			$this->respond_error( new WP_Error( 'something-went-wrong', $e->getMessage(), [ 'code' => 500 ] ) );
		}
	}

	/**
	 * Prepare error response.
	 *
	 * @param WP_Error $response
	 *
	 * @return void
	 */
	protected function respond_error( WP_Error $response ) {
		$data = $response->get_error_data();
		if ( $this->is_ajax_action() ) {
			wp_send_json_error( $response->get_error_message(), $data['code'] ?? 400 );
		} else {
			wp_die(
				esc_html( $response->get_error_message() ),
				esc_html( $data['title'] ?? __( 'Something went wrong!', 'easy-content-manager' ) ),
				[ 'response' => absint( $data['code'] ?? 400 ) ]
			);
		}
	}

	/**
	 * Prepare success response.
	 *
	 * @param mixed $response
	 *
	 * @return void
	 */
	protected function respond_success( $response ) {
		if ( null !== $response ) {
			if ( $this->is_ajax_action() ) {
				wp_send_json_success( $response );
			} elseif ( is_string( $response ) && Helper::is_valid_site_url( $response ) ) {
				wp_safe_redirect( $response );
				die(); // don't use wp_die...
			} else {
				// @XXX maybe another handler or just void.
				wp_die(
					'',
					'',
					[ 'response' => null ]
				);
			}
		}
	}

	/**
	 * Prepare response for the request.
	 *
	 * @return WP_Error|array
	 *
	 * @throws EcmException If an ECM-specific processing error occurs.
	 * @throws Exception If an unexpected error occurs during response preparation.
	 */
	protected function prepare_response() {
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		$action = explode( $this->namespace . '/', $action )[1];

		if ( ! isset( $this->actions[ $action ] ) ) {
			return new WP_Error(
				'invalid_action',
				__( 'Invalid action.', 'easy-content-manager' ),
				[
					'status' => 400,
					'title'  => __( 'Invalid action.', 'easy-content-manager' ),
				]
			);
		}

		$details = $this->actions[ $action ];
		$nonce   = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : '';

		if ( empty( $nonce ) && isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
		}

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			return new WP_Error(
				'invalid_nonce',
				__( 'Invalid nonce.', 'easy-content-manager' ),
				[
					'status' => rest_authorization_required_code(),
					'title'  => __( 'Invalid nonce.', 'easy-content-manager' ),
				]
			);
		}

		// Secure by default: an action that declares no explicit audience falls
		// back to its handler's `$default_capability` (itself `manage_options`
		// unless the handler classifies itself). Actions meant for lower roles
		// must opt in via `capability`, `only_logged_in`, or `allow_visitor_action`.
		$default_cap = ! empty( $details['only_logged_in'] ) ? 'only_logged_in' : $this->default_capability;

		if ( $this->is_ajax_action() ) {
			$allow_visitor  = ! empty( $details['allow_visitor_action'] ) ? $details['allow_visitor_action'] : false;
			$user_cap       = ! empty( $details['capability'] ) ? $details['capability'] : $default_cap;
			$has_permission = $this->check_ajax_permissions( $user_cap, $allow_visitor );
		} else {
			$allow_visitor  = ! empty( $details['allow_visitor_action'] ) ? $details['allow_visitor_action'] : false;
			$user_cap       = ! empty( $details['capability'] ) ? $details['capability'] : $default_cap;
			$has_permission = $this->check_post_permissions( $user_cap, $allow_visitor );
		}

		if ( is_wp_error( $has_permission ) ) {
			return $has_permission;
		}

		if ( empty( $details['callback'] ) || ! is_callable( $details['callback'] ) ) {
			return new WP_Error(
				'not_implemented',
				__( 'Requested method not implemented.', 'easy-content-manager' ),
				[
					'status' => 501,
					'title'  => __( 'Not implemented!', 'easy-content-manager' ),
				]
			);
		}

		$payload = [];

		if ( ! empty( $details['fields'] ) && is_array( $details['fields'] ) ) {
			foreach ( $details['fields'] as $key => $type ) {
				if ( ! empty( $_REQUEST[ $key ] ) ) {
					if ( is_array( $type ) ) {
						foreach ( $type as $type_key => $type_value ) {
                            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Decoded payload sanitized below.
							$decoded = wp_unslash( $_REQUEST[ $key ] );
                            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

							if ( str_starts_with( $decoded, '[' ) || str_starts_with( $decoded, '{' ) ) {
								$decoded = json_decode( $decoded, true );
							}

							if ( ! empty( $decoded[ $type_key ] ) ) {
								if ( is_array( $type_value ) ) {
									foreach ( $type_value as $type_value_key => $type_value_value ) {
										$decode3_type = null;

										if ( str_contains( $type_value_value, '|' ) ) {
											list($decode3_type, $type_value_value) = explode( '|', $type_value_value, 2 );
										}

										switch ( strtolower( $type_value_value ) ) {
											case 'absint':
											case 'id':
												$payload[ $key ][ $type_key ][ $type_value_key ] = absint( sanitize_text_field( $decoded[ $type_key ][ $type_value_key ] ) );
												break;
											case 'int':
											case 'integer':
												$payload[ $key ][ $type_key ][ $type_value_key ] = intval( sanitize_text_field( $decoded[ $type_key ][ $type_value_key ] ) );
												break;
											case 'double':
											case 'float':
												$payload[ $key ][ $type_key ][ $type_value_key ] = floatval( sanitize_text_field( $decoded[ $type_key ][ $type_value_key ] ) );
												break;
											case 'url':
												$payload[ $key ][ $type_key ][ $type_value_key ] = esc_url_raw( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'bool':
											case 'boolean':
												$payload[ $key ][ $type_key ][ $type_value_key ] = (bool) filter_var( sanitize_text_field( $decoded[ $type_key ][ $type_value_key ] ), FILTER_VALIDATE_BOOLEAN );
												break;
											case 'post':
												$payload[ $key ][ $type_key ][ $type_value_key ] = wp_kses_post( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'slug':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_title( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'email':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_email( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'user':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_user( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'textarea':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_textarea_field( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'text':
											case 'string':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_text_field( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'hex_color':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_hex_color( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'hex_color_no_hash':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_hex_color_no_hash( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'key':
												$payload[ $key ][ $type_key ][ $type_value_key ] = sanitize_key( $decoded[ $type_key ][ $type_value_key ] );
												break;
											case 'json':
												$payload[ $key ] = json_decode( wp_unslash( $_REQUEST[ $key ] ), true );
												break;
											default:
												if ( is_array( $payload[ $key ][ $type_key ][ $type_value_key ] ) ) {
													$payload[ $key ][ $type_key ][ $type_value_key ] = wp_kses_post_deep( $decoded[ $type_key ][ $type_value_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
												} else {
													$payload[ $key ][ $type_key ][ $type_value_key ] = wp_kses_post( trim( $decoded[ $type_key ][ $type_value_key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
												}
												break;
										}//end switch

										if ( $decode3_type && ! empty( $payload[ $key ][ $type_key ][ $type_value_key ] ) ) {
											$payload[ $key ] = $this->maybe_decode( $payload[ $key ][ $type_key ][ $type_value_key ], $decode3_type );
										}
									}//end foreach
								} else {
									$decode2_type = null;

									if ( str_contains( $type_value, '|' ) ) {
										list($decode2_type, $type_value) = explode( '|', $type_value, 2 );
									}

									switch ( strtolower( $type_value ) ) {
										case 'absint':
										case 'id':
											$payload[ $key ][ $type_key ] = absint( sanitize_text_field( $decoded[ $type_key ] ) );
											break;
										case 'int':
										case 'integer':
											$payload[ $key ][ $type_key ] = intval( sanitize_text_field( $decoded[ $type_key ] ) );
											break;
										case 'double':
										case 'float':
											$payload[ $key ][ $type_key ] = floatval( sanitize_text_field( $decoded[ $type_key ] ) );
											break;
										case 'url':
											$payload[ $key ][ $type_key ] = esc_url_raw( $decoded[ $type_key ] );
											break;
										case 'bool':
										case 'boolean':
											$payload[ $key ][ $type_key ] = (bool) filter_var( sanitize_text_field( $decoded[ $type_key ] ), FILTER_VALIDATE_BOOLEAN );
											break;
										case 'post':
											$payload[ $key ][ $type_key ] = wp_kses_post( $decoded[ $type_key ] );
											break;
										case 'slug':
											$payload[ $key ][ $type_key ] = sanitize_title( $decoded[ $type_key ] );
											break;
										case 'email':
											$payload[ $key ][ $type_key ] = sanitize_email( $decoded[ $type_key ] );
											break;
										case 'user':
											$payload[ $key ][ $type_key ] = sanitize_user( $decoded[ $type_key ] );
											break;
										case 'textarea':
											$payload[ $key ][ $type_key ] = sanitize_textarea_field( $decoded[ $type_key ] );
											break;
										case 'text':
										case 'string':
											$payload[ $key ][ $type_key ] = sanitize_text_field( $decoded[ $type_key ] );
											break;
										case 'hex_color':
											$payload[ $key ][ $type_key ] = sanitize_hex_color( $decoded[ $type_key ] );
											break;
										case 'hex_color_no_hash':
											$payload[ $key ][ $type_key ] = sanitize_hex_color_no_hash( $decoded[ $type_key ] );
											break;
										case 'key':
											$payload[ $key ][ $type_key ] = sanitize_key( $decoded[ $type_key ] );
											break;
										case 'json':
											$payload[ $key ] = json_decode( wp_unslash( $_REQUEST[ $key ] ), true );
											break;
										default:
											if ( is_array( $payload[ $key ][ $type_key ] ) ) {
												$payload[ $key ][ $type_key ] = wp_kses_post_deep( $decoded[ $type_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
											} else {
												$payload[ $key ][ $type_key ] = wp_kses_post( trim( $decoded[ $type_key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
											}
											break;
									}//end switch

									if ( $decode2_type && ! empty( $payload[ $key ][ $type_key ] ) ) {
										$payload[ $key ] = $this->maybe_decode( $payload[ $key ][ $type_key ], $decode2_type );
									}
								}//end if
							}//end if
						}//end foreach
					} else {
						$decode_type = null;

						if ( str_contains( $type, '|' ) ) {
							list($decode_type, $type) = explode( '|', $type, 2 );
						}

						switch ( strtolower( $type ) ) {
							case 'absint':
							case 'id':
								$payload[ $key ] = absint( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) );
								break;
							case 'int':
							case 'integer':
								$payload[ $key ] = intval( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) );
								break;
							case 'double':
							case 'float':
								$payload[ $key ] = floatval( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) );
								break;
							case 'url':
								$payload[ $key ] = esc_url_raw( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'bool':
							case 'boolean':
								$payload[ $key ] = (bool) filter_var( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ), FILTER_VALIDATE_BOOLEAN );
								break;
							case 'post':
								$payload[ $key ] = wp_kses_post( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'slug':
								$payload[ $key ] = sanitize_title( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'email':
								$payload[ $key ] = sanitize_email( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'user':
								$payload[ $key ] = sanitize_user( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'textarea':
								$payload[ $key ] = sanitize_textarea_field( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'text':
							case 'string':
								$payload[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'hex_color':
								$payload[ $key ] = sanitize_hex_color( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'hex_color_no_hash':
								$payload[ $key ] = sanitize_hex_color_no_hash( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'key':
								$payload[ $key ] = sanitize_key( wp_unslash( $_REQUEST[ $key ] ) );
								break;
							case 'json':
								$payload[ $key ] = json_decode( wp_unslash( $_REQUEST[ $key ] ), true );
								break;
							default:
								if ( is_array( $_REQUEST[ $key ] ) ) {
									$payload[ $key ] = wp_kses_post_deep( wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
								} else {
									$payload[ $key ] = wp_kses_post( trim( wp_unslash( $_REQUEST[ $key ] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
								}
								break;
						}//end switch

						if ( $decode_type && ! empty( $payload[ $key ] ) ) {
							$payload[ $key ] = $this->maybe_decode( $payload[ $key ], $decode_type );
						}
					}//end if
				}//end if
			}//end foreach
		}//end if

		return $this->respond( $details['callback'], $payload );
	}

	protected function maybe_decode( $payload, $type ) {
		// NOTE: deliberately no serialize/unserialize/php branch here. Running
		// maybe_unserialize() on request input is a PHP object-injection vector;
		// client payloads are decoded as JSON only.
		if ( ! is_string( $payload ) ) {
			return $payload;
		}

		if ( str_starts_with( $payload, '[' ) || str_starts_with( $payload, '{' ) ) {
			if ( 'array' === $type ) {
				return json_decode( $payload, true );
			}

			return json_decode( $payload );
		}

		return $payload;
	}

	/**
	 * Run action callback.
	 *
	 * @param array|string $callback
	 * @param array        $payload
	 *
	 * @return WP_Error|array|stdClass|string
	 *
	 * @throws EcmException If an ECM-specific processing error occurs.
	 * @throws Exception If an unexpected error occurs during response preparation.
	 */
	final protected function respond( $callback, array $payload ) {
		return call_user_func( $callback, $payload );
	}

	/**
	 * @param string $capability
	 * @param bool   $allow_visitors
	 *
	 * @return WP_Error|true
	 */
	protected function check_ajax_permissions( string $capability, bool $allow_visitors = false ) {
		if ( true === $allow_visitors ) {
			return true;
		}

		if ( 'only_logged_in' === $capability ) {
			if ( ! is_user_logged_in() ) {
				return new WP_Error(
					'forbidden_action',
					__( 'You do not have permission to access this page.', 'easy-content-manager' ),
					[
						'status' => rest_authorization_required_code(),
						'title'  => __( 'Insufficient permission!', 'easy-content-manager' ),
					]
				);
			}
			return true;
		}

		if ( ( ! is_user_logged_in() && ! $allow_visitors ) || ( is_user_logged_in() && $capability && ! self::can( $capability ) ) ) {
			return new WP_Error(
				'forbidden_action',
				__( 'You do not have permission to access this page.', 'easy-content-manager' ),
				[
					'status' => rest_authorization_required_code(),
					'title'  => __( 'Insufficient permission!', 'easy-content-manager' ),
				]
			);
		}

		return true;
	}

	/**
	 * @param string $capability
	 * @param bool   $allow_visitors
	 *
	 * @return WP_Error|true
	 */
	protected function check_post_permissions( string $capability, bool $allow_visitors = false ) {
		if ( true === $allow_visitors ) {
			return true;
		}

		if ( 'only_logged_in' === $capability ) {
			if ( ! is_user_logged_in() ) {
				return new WP_Error(
					'forbidden_action',
					__( 'You do not have permission to access this page.', 'easy-content-manager' ),
					[
						'status' => rest_authorization_required_code(),
						'title'  => __( 'Insufficient permission!', 'easy-content-manager' ),
					]
				);
			}
			return true;
		}

		if ( ! is_user_logged_in() || ! self::can( $capability ) ) {
			return new WP_Error(
				'forbidden_action',
				__( 'You do not have permission to access this page.', 'easy-content-manager' ),
				[
					'status' => rest_authorization_required_code(),
					'title'  => __( 'Insufficient permission!', 'easy-content-manager' ),
				]
			);
		}

		return true;
	}

	/**
	 * Capability check for a dispatched action.
	 *
	 * ECM's own capabilities go through {@see Capabilities::user_can()}, which
	 * lets an administrator through even if the capability migration hasn't run
	 * yet on this site. Every other capability is checked verbatim — a handler
	 * asking for `edit_users` means `edit_users`.
	 */
	protected static function can( string $capability ): bool {
		if ( Capabilities::is_ecm_cap( $capability ) ) {
			return Capabilities::user_can( $capability );
		}

		return current_user_can( $capability );
	}
}
