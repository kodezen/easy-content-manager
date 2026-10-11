<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CheckboxField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'checkbox';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'checkbox-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => [],
			'choices' => [],
			'layout' => 'vertical',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
			'allow_custom' => false
		];
	}

	protected function sanitize_callback() {
		return function ( $value ) {
			if ( is_array( $value ) ) {
				return array_map( 'sanitize_text_field', $value );
			}
			return [];
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
