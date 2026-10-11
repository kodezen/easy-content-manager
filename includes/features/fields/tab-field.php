<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TabField extends AbstractFieldHandler {


	protected string $type = 'string';
	protected string $field_type = 'tab';
	protected $default = '';

	protected function general(): array {
		return [
			'field_type' => 'tab-field',
			'field_label' => '',
			'field_name' => '',
			'placement' => 'top',
			'endpoint' => false,
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
				'type' => 'string',
			],
		];
	}
}
