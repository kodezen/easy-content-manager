<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostObjectField extends AbstractFieldHandler {


	protected string $type = 'array';
	protected string $field_type = 'post-object';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'post-object-field',
			'field_label' => '',
			'field_name' => '',
			'added_post_types' => [],
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
