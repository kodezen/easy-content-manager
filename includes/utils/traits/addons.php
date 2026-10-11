<?php

namespace EasyContentManager\Utils\Traits;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait Addons {

	/**
	 * Whether an add-on is switched on, by its add-on slug (e.g. "frontend-submission").
	 *
	 * The on/off map is a JSON object of `slug => bool` stored in the add-ons
	 * option — the same shape Ajax\Addons::saved_addon_status() writes.
	 *
	 * @param string $addon_slug
	 * @return bool
	 */
	public static function is_addon_enabled( string $addon_slug ): bool {
		$addons = json_decode( (string) get_option( EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME, '{}' ), true );

		if ( ! is_array( $addons ) ) {
			return false;
		}

		return ! empty( $addons[ $addon_slug ] );
	}

	public static function is_addon_enabled_for_post_type( string $addon_name, $post_type ): bool {
		if ( ! str_ends_with( $addon_name, '_addon' ) ) {
			$addon_name .= '_addon';
		}

		$settings = Helper::get_settings( $addon_name );

		$shortcode_settings = isset( $settings->shortcode_settings ) ? (array) $settings->shortcode_settings : [];

		if ( ! $shortcode_settings['enable_specific_post_types'] ) {
			return true;
		}

		if ( empty( $post_type ) ) {
			return false;
		}

		if ( in_array( $post_type, $shortcode_settings['specific_post_types'], true ) ) {
			return true;
		}

		return false;
	}

	private static function get_addons_sub_items(): array {
		$sub_items = [];

		$addons = [
			'claim' => [
				'title'  => 'Claim',
				'active' => Helper::get_addon_active_status( 'claim' ),
			],
			'bookmark' => [
				'title'  => 'Bookmark',
				'active' => Helper::get_addon_active_status( 'bookmark' ),
			],
			'upvote' => [
				'title'  => 'Upvote',
				'active' => Helper::get_addon_active_status( 'upvote' ),
			],
			'reactions' => [
				'title'  => 'Reactions',
				'active' => Helper::get_addon_active_status( 'reactions' ),
			],
			'review-and-ratings' => [
				'title'  => 'Review & Ratings',
				'active' => Helper::get_addon_active_status( 'review-and-ratings' ),
			],
			'view-counter' => [
				'title'  => 'View Counter',
				'active' => Helper::get_addon_active_status( 'view-counter' ),
			],
			'frontend-submission' => [
				'title'  => 'Frontend Submission',
				'active' => Helper::get_addon_active_status( 'frontend-submission' ),
			],
		];

		foreach ( $addons as $slug => $addon ) {
			if ( ! empty( $addon['active'] ) ) {
				$sub_items[] = [
					'slug'  => $slug,
					'title' => $addon['title'],
				];
			}
		}

		return apply_filters( 'easy_content_manager/addons_sub_items', $sub_items );
	}
}
