<?php

namespace ContentManager\Classes\Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use Exception;
use WP_Error;

/**
 * WP_Error compatible exception.
 */
class EcmException extends Exception {




	protected string $wp_code = '';

	protected $data = [];

	/**
	 * @param string         $message
	 * @param string         $wp_code
	 * @param mixed          $data
	 * @param int            $code
	 * @param Exception|null $previous
	 */
	public function __construct( string $message, string $wp_code = '', $data = null, int $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->wp_code = $wp_code ? $wp_code : str_replace( __NAMESPACE__, '', __CLASS__ );

		if ( ! $data ) {
			$data = [];
		}

		if ( empty( $data['status'] ) ) {
			$data['status'] = $code ? $code : 500;
		}

		$this->data = $data;
	}

	public function get_wp_error_code(): string {
		return $this->wp_code;
	}

	public function get_data( string $key = null ) {
		if ( $key ) {
			if ( isset( $this->data[ $key ] ) ) {
				return $this->data[ $key ];
			}

			return null;
		}

		return $this->data;
	}

	public function set_data( array $data ) {
		$this->data = $data;
	}

	public function add_data( string $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * @deprecated use self::toWPError();
	 */
	public function get_wp_error(): WP_Error {
		return $this->toWpError();
	}

	public function toWpError(): WP_Error {
		return new WP_Error( $this->wp_code, $this->getMessage(), $this->data );
	}

	public static function from_wp_error( WP_Error $wp_error ) {
		return new self( $wp_error->get_error_message(), $wp_error->get_error_code(), $wp_error->get_error_data() );
	}
}
