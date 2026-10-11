<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MessageField extends AbstractFieldHandler {

	protected string $field_type = 'message';
	protected function general(): array {
		return [
			'field_type' => 'message-field',
			'field_label' => '',
			'field_name' => '',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [];
	}
}
