<?php

namespace EasyContentManager\Features\Fields;

use EasyContentManager\Classes\AbstractFieldHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class VideoField extends AbstractFieldHandler {


	protected string $field_type = 'video';

	protected function general(): array {
		return [
			'field_type' => 'video-field',
			'field_label' => '',
			'field_name' => 'video-field',
			'field_default_value' => '',
		];
	}

	protected function validation(): array {
		return [
			'is_size_limit' => false,
			'required' => false,
			'max_limit' => 0,
			'min_limit' => 0,
			'video_type' => '',
			'video_file_format' => ''
		];
	}
}
