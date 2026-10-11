<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PageLinkField extends AbstractFieldHandler {

	protected string $type = 'array';
	protected string $field_type = 'page-link';
	protected $default = [];

	protected function general(): array {
		return [
			'field_type' => 'page-link-field',
			'field_label' => '',
			'field_name' => '',
			'post_type' => [],
			'multiple' => false,
			'allow_archives' => false,
		];
	}

	protected function validation(): array {
		return [
			'required' => false,
			'allow_null' => true,
		];
	}

	protected function rest_schema() {
		return [
			'schema' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
		];
	}
}
