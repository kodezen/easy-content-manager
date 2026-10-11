<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GalleryField extends AbstractFieldHandler {

	protected string $type = 'array';
	protected string $field_type = 'gallery';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'gallery-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => [],
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
			'is_size_limit' => false,
			'max_limit' => 0,
			'min_limit' => 0,
			'image_type' => 'media-manager',
			'image_file_format' => 'any',
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
