<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MapField extends AbstractFieldHandler {


	protected string $type = 'object';
	protected string $field_type = 'map';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'map-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => [],
			'zoom_level' => 14,
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
