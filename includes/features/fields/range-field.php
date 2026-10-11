<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RangeField extends AbstractFieldHandler {

	protected string $type = 'integer';
	protected string $field_type = 'range';
	protected $default = 0;

	protected function general(): array {
		return [
			'field_type' => 'range-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => 0,
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
			'min' => 0,
			'max' => 100,
			'step' => 1,
		];
	}
}
