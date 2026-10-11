<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class DateField extends AbstractFieldHandler {

	protected string $field_type = 'date';

	protected function general(): array {
		return [
			'field_type' => 'date-field',
			'field_label' => '',
			'field_name' => 'date-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'date_format' => '',
			'required' => false,
		];
	}
}
