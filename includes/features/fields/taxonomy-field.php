<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TaxonomyField extends AbstractFieldHandler {


	protected string $type = 'array';
	protected string $field_type = 'taxonomy';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'taxonomy-field',
			'field_label' => '',
			'field_name' => '',
			'taxonomy' => '',
			'field_type_display' => 'checkbox',
			'add_term' => false,
			'save_terms' => false,
			'load_terms' => false,
			'return_format' => 'object',
			'multiple' => false,
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
