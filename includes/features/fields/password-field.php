<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PasswordField extends AbstractFieldHandler {


	protected string $field_type = 'password';
	protected function general(): array {
		return [
			'field_type' => 'password-field',
			'field_label' => '',
			'field_name' => 'password-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'min_length' => 0,
			'required' => false,
		];
	}
}
