<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DateTimeField extends AbstractFieldHandler {

	protected string $type = 'string';
	protected string $field_type = 'date-time';

	protected $default = '';

	protected function general(): array {
		return [
			'field_type' => 'date-time-field',
			'field_label' => '',
			'field_name' => '',
			'first_day' => 1,
		];
	}

	protected function validation(): array {
		return [
			'display_format' => 'yyyy-MM-dd HH:mm:ss',
			'return_format' => 'yyyy-MM-dd HH:mm:ss',
			'required' => false,
		];
	}
}
