<?php

namespace EasyContentManager\Utils\Traits;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait Settings {


	public static function get_ecm_settings(): array {
		$settings = get_option( 'ecm_settings' );
		return ! empty( $settings ) ? maybe_unserialize( $settings ) : [];
	}

	public static function get_ecm_settings_formatted_global_data() {
		$settings_posts = get_posts([
			'post_type'      => Helper::ECM_SETTINGS_POST_TYPE_NAME,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		]);

		$formatted_data = [];

		$settings_posts = array_map(function ( $post ) {
			return array_merge( maybe_unserialize( $post->post_content ), [
				'id' => $post->ID,
				'post_title' => $post->post_title
			] );
		}, $settings_posts);

		$local_settings = ecm_get_json_from_files( ecm_get_plugins_settings_path() );

		$settings_posts = array_merge( $settings_posts, $local_settings );

		foreach ( $settings_posts as $post ) {
			// Settings shipped as JSON by a plugin or theme may carry neither an ID
			// nor a slug; without this guard those produced the route `ecm-app-`
			// (which then landed in the React route/capability map) plus a pair of
			// undefined-index warnings.
			if ( empty( $post['slug'] ) ) {
				continue;
			}

			$formatted_data[] = [
				'ID'    => $post['id'] ?? 0,
				'title' => $post['label'] ?? '',
				'slug'  => 'ecm-app-' . $post['slug'],
			];
		}

		return $formatted_data;
	}

	public static function get_settings( $key, $default = null ) {
		global $ecm_settings;

		if ( isset( $ecm_settings->{$key} ) ) {
			return $ecm_settings->{$key};
		}

		return $default;
	}

	public static function get_frontend_submission_shortcode_settings() {
		// get_settings() returns the [] default when the option is unset, and
		// reading ->shortcode_settings off an array is a PHP 8 warning.
		$settings = self::get_settings( 'frontend_submission_addon' );

		if ( ! is_object( $settings ) ) {
			return [];
		}

		return (array) ( $settings->shortcode_settings ?? [] );
	}
}
