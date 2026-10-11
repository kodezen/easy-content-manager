<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RadioField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'radio';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'radio-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '',
			'choices' => [],
			'layout' => 'vertical',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}

	protected function sanitize_callback() {
		return function ( $value ) {
			return sanitize_text_field( $value );
		};
	}

	protected function rest_schema() {
		return [
			'schema' => [
				'type' => 'object',
				'additionalProperties' => true
			],
		];
	}
}
