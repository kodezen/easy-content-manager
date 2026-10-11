<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RepeaterField extends AbstractFieldHandler {

	protected string $type = 'array';
	protected string $field_type = 'repeater';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'repeater-field',
			'field_label' => '',
			'field_name' => '',
			'layout' => 'row',
			'button_label' => 'Add Row',
			'sub_fields' => [],
		];
	}

	protected function validation(): array {
		return [
			'min' => 1,
			'max' => 0,
			'required' => false,
			'allow_null' => true,
		];
	}

	protected function rest_schema() {
		return [
			'schema' => [
				'type' => 'array',
				'items' => [
					'type' => 'object',
					'additionalProperties' => true
				],
			],
		];
	}
}
