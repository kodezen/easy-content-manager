<?php

namespace EasyContentManager\Utils\Traits;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait Taxonomy {


	public static function get_all_custom_taxonomies(): array {
		return get_posts([
			'post_type'   => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
			'post_status' => 'publish',
			'numberposts' => -1,
		]);
	}

	public static function get_taxonomies_for_post_type( string $post_type ): array {
		$taxonomies = self::get_all_custom_taxonomies();
		$attached = [];

		foreach ( $taxonomies as $taxonomy_post ) {
			$content = maybe_unserialize( $taxonomy_post->post_content );

			if ( empty( $content['taxonomy_slug'] ) || empty( $content['object_type'] ) ) {
				continue;
			}

			if ( in_array( $post_type, (array) $content['object_type'], true ) ) {
				$attached[] = $content['taxonomy_slug'];
			}
		}

		return $attached;
	}
}
