<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TextareaField extends AbstractFieldHandler {

	protected string $field_type = 'textarea';

	protected function general(): array {
		return [
			'field_type' => 'textarea-field',
			'field_label' => '',
			'field_name' => 'textarea-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'is_enabled' => false,
			'required' => false,
			'min_length' => 0,
			'max_length' => 0,
		];
	}
}
