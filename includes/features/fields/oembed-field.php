<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OEmbedField extends AbstractFieldHandler {

	protected string $field_type = 'oembed';
	protected function general(): array {
		return [
			'field_type' => 'oembed-field',
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

	protected function rest_schema() {
		return [
			'type' => 'string',
			'format' => 'uri',
		];
	}
}
