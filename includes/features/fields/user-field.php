<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserField extends AbstractFieldHandler {



	protected string $type = 'array';
	protected string $field_type = 'user';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'user-field',
			'field_label' => '',
			'field_name' => '',
			'roles' => [],
			'multiple' => false,
			'return_format' => 'object',
		];
	}

	protected function validation(): array {
		return [
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
