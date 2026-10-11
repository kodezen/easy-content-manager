<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UrlField extends AbstractFieldHandler {

	protected string $field_type = 'url';

	protected function general(): array {
		return [
			'field_type' => 'url-field',
			'field_label' => '',
			'field_name' => 'url-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}
}
