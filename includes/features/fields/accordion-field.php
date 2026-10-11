<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccordionField extends AbstractFieldHandler {


	protected string $type = 'string';
	protected string $field_type = 'accordion';
	protected $default = '';

	protected function general(): array {
		return [
			'field_type' => 'accordion-field',
			'field_label' => '',
			'field_name' => '',
			'open' => false,
			'multi_expand' => false,
			'endpoint' => false,
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
		];
	}

	protected function rest_schema() {
		return [
			'schema' => array(
				'type' => 'string',
			),
		];
	}
}
