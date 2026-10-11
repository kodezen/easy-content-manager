<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TextField extends AbstractFieldHandler {
	protected string $field_type = 'text';

	protected function general( $args = [] ): array {
		return [
			'field_type' => 'text-field',
			'field_label' => '',
			'field_name' => 'text-field',
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
