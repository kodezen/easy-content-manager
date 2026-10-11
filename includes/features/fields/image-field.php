<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ImageField extends AbstractFieldHandler {


	protected string $type = 'array';
	protected string $field_type = 'image';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'image-field',
			'field_label' => '',
			'field_name' => 'image-field',
			'field_default_value' => [],
		];
	}

	protected function validation(): array {
		return [
			'is_size_limit' => false,
			'required' => false,
			'max_limit' => 0,
			'min_limit' => 0,
			'image_type' => 'single-image',
			'image_file_format' => ''
		];
	}

	protected function rest_schema() {
		return [
			'schema' => [
				'type' => 'array',
				'items' => [
					'type' => 'integer',
				],
			],
		];
	}
}
