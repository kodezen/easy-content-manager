<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class EmailField extends AbstractFieldHandler {

	protected string $field_type = 'email';

	protected function general(): array {
		return [
			'field_type' => 'email-field',
			'field_label' => '',
			'field_name' => 'email-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}
}
