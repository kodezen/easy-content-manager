<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class FileField extends AbstractFieldHandler {


	protected string $type = 'integer';
	protected string $field_type = 'file';
	protected $default = 0;

	protected function general(): array {
		return [
			'field_type' => 'file-field',
			'field_label' => '',
			'field_name' => 'file-field',
			'field_default_value' => 0,
		];
	}

	protected function validation(): array {
		return [
			'is_size_limit' => false,
			'required' => false,
			'max_limit' => 0,
			'min_limit' => 0,
			'file_type' => 'media-manager',
			'file_format' => ''
		];
	}
}
