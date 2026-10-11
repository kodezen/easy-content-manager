<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IconField extends AbstractFieldHandler {

	protected string $field_type = 'icon';

	protected function general(): array {
		return [
			'field_type' => 'icon-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}
}
