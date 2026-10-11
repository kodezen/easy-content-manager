<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NumberField extends AbstractFieldHandler {

	protected string $type = 'integer';
	protected string $field_type = 'number';
	protected $default = 0;
	protected function general(): array {
		return [
			'field_type' => 'number-field',
			'field_label' => '',
			'field_name' => 'number-field',
			'field_default_value' => 0,
		];
	}

	protected function validation(): array {
		return [
			'number_format' => '',
			'allow_negative_number' => false,
			'required' => false,
		];
	}
}
