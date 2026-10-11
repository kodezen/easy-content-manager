<?php

namespace EasyContentManager\Classes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class AbstractFieldHandler {




	protected string $post_type;
	protected string $meta_key;
	protected array $args = [];

	protected string $type = 'string';
	protected bool $is_single = true;
	protected bool $is_revisions_enabled = false;
	protected string $object_type = 'post';
	protected string $field_type = 'text';

	protected $default = '';

	public function __construct( $post_type, $meta_key = '', $object_type = 'post' ) {
		$this->post_type = $post_type;
		$this->meta_key = $meta_key;
		$this->object_type = $object_type;
		$this->args = $this->build_default_args();
	}

	private function build_default_args(): array {
		$args = [
			'object_subtype'    => $this->post_type,
			'type'              => $this->type,
			'field_type'        => $this->field_type,
			'label'             => '',
			'description'       => '',
			'single'            => $this->is_single,
			'sanitize_callback' => $this->sanitize_callback(),
			'auth_callback'     => $this->auth_callback(),
			'show_in_rest'      => $this->rest_schema(),
			'revisions_enabled' => $this->is_revisions_enabled,
		];

		if ( null !== $this->default ) {
			$args['default'] = $this->default;
		}

		return $args;
	}


	protected function sanitize_callback() {
		return null;
	}

	protected function auth_callback() {
		return null;
	}

	protected function rest_schema() {
		return true;
	}

	abstract protected function general(): array;

	abstract protected function validation(): array;

	protected function conditional_logic(): array {
		return [
			'is_enabled' => false,
			'logics' => [
				[
					(object) [
						'logic_type' => 'and',
						'selected_field' => '',
						'selected_field_meta_key' => '',
						'condition_option' => '',
						'condition_label' => '',
						'condition_value' => '',
						'condition_field' => '',
					],
				],
			]
		];
	}

	public function get( $is_json = false, array $overrides = [] ) {
		$data = [
			'object_type' => $this->object_type,
			'meta_key' => $this->meta_key,
			'general' => $this->general(),
			'validation' => $this->validation(),
			'conditional_logic' => $this->conditional_logic(),
			'post_meta_args' => $this->args,
		];

		if ( ! empty( $overrides ) ) {
			$data = Helper::deep_merge_args( $data, $overrides );
		}

		return $is_json ? wp_json_encode( $data ) : $data;
	}
}
