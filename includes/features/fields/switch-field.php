<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SwitchField extends AbstractFieldHandler {


	protected string $type = 'boolean';
	protected string $field_type = 'switch';
	protected $default = false;

	protected function general(): array {
		return [
			'field_type' => 'switch-field',
			'field_label' => '',
			'field_name' => 'switch-field',
			'field_default_value' => false,
		];
	}

	protected function validation(): array {
		return [
			'on_off' => '',
			'required' => false,
		];
	}
}
