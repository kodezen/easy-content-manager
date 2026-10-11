<?php

namespace EasyContentManager\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use EasyContentManager\Admin\Settings\Base;
use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;

class BaseSettings extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_SETTINGS;


	public function __construct() {
		$this->actions = array(
			'update_base_settings' => array(
				'callback' => array( $this, 'update_base_settings' ),
				'fields' => [
					'color_theme_mode' => 'string',
					'primary_color' => 'string',
					'secondary_color' => 'string',
					'text_color' => 'string',
					'body_color' => 'string',
					'background_color' => 'string',
					'border_color' => 'string',
					// dark color
					'dark_primary_color' => 'string',
					'dark_secondary_color' => 'string',
					'dark_text_color' => 'string',
					'dark_body_color' => 'string',
					'dark_background_color' => 'string',
					'dark_border_color' => 'string',
					// inherit post type
					'inherited_post_type' => 'json',
					// addons
					'reaction_addon' => 'json',
					'upvote_addon' => 'json',
					'view_counter_addon' => 'json',
					'claim_addon' => 'json',
					'review_ratings_addon' => 'json',
					'bookmark_addon' => 'json',
					'frontend_submission_addon' => 'json',
				]
			),
		);
	}

	public function update_base_settings( $payload ) {
		do_action( 'easy_content_manager/admin/before_save_settings', $payload, 'base' );
		$default = Base::get_default_data();
		$is_update = Base::save_settings(apply_filters('easy_content_manager/admin/settings/save', [
			'color_theme_mode' => $payload['color_theme_mode'] ?? $default['color_theme_mode'],
			'primary_color' => $payload['primary_color'] ?? $default['primary_color'],
			'secondary_color' => $payload['secondary_color'] ?? $default['secondary_color'],
			'text_color' => $payload['text_color'] ?? $default['text_color'],
			'body_color' => $payload['body_color'] ?? $default['body_color'],
			'background_color' => $payload['background_color'] ?? $default['background_color'],
			'border_color' => $payload['border_color'] ?? $default['border_color'],
			// Dark
			'dark_primary_color' => $payload['dark_primary_color'] ?? $default['dark_primary_color'],
			'dark_secondary_color' => $payload['dark_secondary_color'] ?? $default['dark_secondary_color'],
			'dark_text_color' => $payload['dark_text_color'] ?? $default['dark_text_color'],
			'dark_body_color' => $payload['dark_body_color'] ?? $default['dark_body_color'],
			'dark_background_color' => $payload['dark_background_color'] ?? $default['dark_background_color'],
			'dark_border_color' => $payload['dark_border_color'] ?? $default['dark_border_color'],
			// addons
			'inherited_post_type' => $payload['inherited_post_type'] ?? $default['inherited_post_type'],
			'reaction_addon' => $payload['reaction_addon'] ?? $default['reaction_addon'],
			'upvote_addon' => $payload['upvote_addon'] ?? $default['upvote_addon'],
			'view_counter_addon' => $payload['view_counter_addon'] ?? $default['view_counter_addon'],
			'claim_addon' => $payload['claim_addon'] ?? $default['claim_addon'],
			'review_ratings_addon' => $payload['review_ratings_addon'] ?? $default['review_ratings_addon'],
			'bookmark_addon' => $payload['bookmark_addon'] ?? $default['bookmark_addon'],
			'frontend_submission_addon' => $payload['frontend_submission_addon'] ?? $default['frontend_submission_addon'],
		], $payload, $default));
		do_action( 'easy_content_manager/admin/after_save_settings', $is_update, 'base', $payload );
		wp_send_json_success( $is_update );
	}
}
