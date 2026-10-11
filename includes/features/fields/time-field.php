<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TimeField extends AbstractFieldHandler {

	protected string $field_type = 'time';

	protected function general(): array {
		return [
			'field_type' => 'time-field',
			'field_label' => '',
			'field_name' => 'time-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'am_pm' => '',
			'required' => false,
		];
	}
}
