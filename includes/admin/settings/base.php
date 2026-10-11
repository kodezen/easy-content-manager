<?php

namespace EasyContentManager\Admin\Settings;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Base {



	public static function get_saved_data() {
		$settings = get_option( EASY_CONTENT_MANAGER_SETTINGS_NAME );
		if ( $settings ) {
			return apply_filters( 'easy_content_manager/admin/settings/saved_data', json_decode( $settings, true ) );
		}
		return [];
	}
	public static function get_default_data() {
		return apply_filters('easy_content_manager/admin/settings/base_default_data', [
			// global style for light
			'color_theme_mode' => 'light',
			'primary_color'   => '#4038CA',
			'secondary_color' => '#F6F7F8',
			'text_color'      => '#141A24',
			'body_color'      => '#f7f7f7',
			'background_color' => '#fff',
			'border_color'    => '#DDDDDF',
			// global style for dark
			'dark_primary_color'   => '#4038ca',
			'dark_secondary_color' => '#2D3031',
			'dark_text_color'      => '#F6F7F8',
			'dark_body_color'      => '#252728',
			'dark_background_color' => '#222',
			'dark_border_color'    => '#38414A',
			// inherit post type
			'inherited_post_type'  => [
				'post' => false,
				'page' => false
			],
			// reactions for addon
			'reaction_addon' => [
				'reactions' => [
					[
						'value' => 'like',
						'label' => 'Like',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'like-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'like-filled' ) ),
					],
					[
						'value' => 'dislike',
						'label' => 'Dislike',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'dislike-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'dislike-filled' ) ),
					],
					[
						'value' => 'love',
						'label' => 'Love',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'love-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'love-filled' ) ),
					],
					[
						'value' => 'smile',
						'label' => 'Smile',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'smile-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'smile-filled' ) ),
					],
					[
						'value' => 'laugh',
						'label' => 'Laugh',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'laugh-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'laugh-filled' ) ),
					],
					[
						'value' => 'wow',
						'label' => 'Wow',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'wow-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'wow-filled' ) ),
					],
					[
						'value' => 'sad',
						'label' => 'Sad',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'sad-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'sad-filled' ) ),
					],
					[
						'value' => 'angry',
						'label' => 'Angry',
						'outline' => Helper::kses_svg( Helper::get_svg_icon( 'angry-outline' ) ),
						'filled' => Helper::kses_svg( Helper::get_svg_icon( 'angry-filled' ) ),
					],
				],
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				]
			],
			// others addon settings
			'upvote_addon' => [
				'enable_anonymous_upvote' => false,
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				]
			],
			'view_counter_addon' => [
				'enable_anonymous_view_count' => true,
				'view_count_frequency' => 24,
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				]
			],
			'claim_addon' => [
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				]
			],
			'review_ratings_addon' => [
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				]
			],
			'bookmark_addon' => [
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'specific_post_types' => []
				],
			],
			'frontend_submission_addon' => [
				'shortcode_settings' => [
					'enable_specific_post_types' => true,
					'enable_anonymous_submission' => false,
					'submission_status' => 'draft',
					'user_roles' => ['subscriber'],
					'specific_post_types' => [],
				],
			],
		]);
	}

	public static function save_settings( $form_data = false ) {
		$default_data = self::get_default_data();
		$saved_data = self::get_saved_data();
		$settings_data = wp_parse_args( $saved_data, $default_data );

		if ( $form_data ) {
			$settings_data = wp_parse_args( $form_data, $settings_data );
		}

		if ( count( $saved_data ) ) {
			return update_option( EASY_CONTENT_MANAGER_SETTINGS_NAME, wp_json_encode( $settings_data ) );
		}
		return add_option( EASY_CONTENT_MANAGER_SETTINGS_NAME, wp_json_encode( $settings_data ) );
	}
}
