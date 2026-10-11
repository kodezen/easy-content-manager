<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TrueFalseField extends AbstractFieldHandler {

	protected string $type = 'boolean';
	protected string $field_type = 'boolean';
	protected $default = false;

	protected function general(): array {
		return [
			'field_type' => 'true-false-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => false,
			'message' => '',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}
}
