<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SelectField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'select';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'select-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '',
			'choices' => [],
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
			'multiple' => false,
			'allow_null' => false,
		];
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
