<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LinkField extends AbstractFieldHandler {

	protected string $type = 'object';
	protected string $field_type = 'link';
	protected $default = null;

	protected function general(): array {
		return [
			'field_type' => 'link-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => [],
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}

	protected function auth_callback() {
		return null;
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
