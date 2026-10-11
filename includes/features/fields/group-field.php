<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'group';

	protected function general(): array {
		return [
			'field_type' => 'group-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => [],
			'sub_fields' => [],
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
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
