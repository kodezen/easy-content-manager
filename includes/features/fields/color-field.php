<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ColorField extends AbstractFieldHandler {

	protected $default = '#000000';
	protected string $field_type = 'color';

	protected function general(): array {
		return [
			'field_type' => 'color-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '#000000',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}
}
