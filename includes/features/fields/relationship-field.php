<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RelationshipField extends AbstractFieldHandler {


	protected string $type = 'array';
	protected string $field_type = 'relationship';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'relationship-field',
			'field_label' => '',
			'field_name' => '',
			'post_type' => [],
			'taxonomy' => [],
			'filters' => [ 'search', 'post_type', 'taxonomy' ],
			'elements' => [ 'featured_image' ],
			'min' => 0,
			'max' => 0,
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
				'oneOf' => [
					[
						'type' => 'array',
						'items' => [
							'oneOf' => [
								[ 'type' => 'integer' ],
								[ 'type' => 'object' ]
							]
						]
					],
					[ 'type' => 'integer' ],
					[ 'type' => 'object' ]
				],
			],
		];
	}
}
