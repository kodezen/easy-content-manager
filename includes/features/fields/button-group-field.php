<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ButtonGroupField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'button-group';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'button-group-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '',
			'choices' => [],
			'layout' => 'horizontal',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
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
