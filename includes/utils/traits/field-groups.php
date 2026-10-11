<?php

namespace EasyContentManager\Utils\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait FieldGroups {


	public static function get_all_ecm_field_groups() {
		$args = [
			'post_type' => self::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'posts_per_page' => -1,
		];

		return get_posts( $args );
	}

	public static function get_custom_field_group_from_post_type( string $given_post_type ) {
		$post_types = get_posts([
			'post_type'  => self::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'posts_per_page' => -1,
			'post_status' => 'any',
		]);

		$field_groups = [];

		foreach ( $post_types as $post_type ) {
			$post_meta = maybe_unserialize( get_post_meta( $post_type->ID, 'ecm_field_group_location', true ) );

			if ( ! empty( $post_meta ) && in_array( $given_post_type, $post_meta, true ) ) {
				$field_groups[] = maybe_unserialize( $post_type->post_content );
			}
		}

		return $field_groups;
	}
	/**
	 * Meta keys of custom fields whose field group is located on a user profile
	 * (current_user or current_user_role). Used to allowlist which usermeta keys
	 * the user-profile custom fields UI is allowed to read/write, since it must
	 * never touch unrelated WordPress core usermeta (e.g. wp_capabilities).
	 */
	public static function get_user_scoped_custom_field_meta_keys(): array {
		$meta_keys = [];

		foreach ( self::get_all_ecm_field_groups() as $field_group ) {
			$locations = maybe_unserialize( get_post_meta( $field_group->ID, 'ecm_field_group_location', true ) );

			$is_user_scoped = ! empty( $locations['current_user'] ) || ! empty( $locations['current_user_role'] );
			if ( ! $is_user_scoped ) {
				continue;
			}

			$post_content = maybe_unserialize( $field_group->post_content );
			foreach ( (array) ( $post_content['custom_fields'] ?? [] ) as $field ) {
				if ( ! empty( $field['meta_key'] ) ) {
					$meta_keys[] = $field['meta_key'];
				}
			}
		}

		return array_unique( $meta_keys );
	}

	/**
	 * Per-request memo. Reset via flush_field_group_runtime_cache() whenever field
	 * groups change so a create-then-read within one request is never stale.
	 *
	 * @var array|null
	 */
	private static $runtime_field_group_cache = null;

	public static function flush_field_group_runtime_cache(): void {
		self::$runtime_field_group_cache = null;
	}

	public static function get_all_custom_field_groups() {
		if ( null !== self::$runtime_field_group_cache ) {
			return self::$runtime_field_group_cache;
		}

		$posts = get_posts([
			'post_type' => self::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'posts_per_page' => -1,
			'post_status' => 'any'
		]);

		self::$runtime_field_group_cache = array_map(function ( $post ) {
			$content = maybe_unserialize( $post->post_content );
			$content['location'] = maybe_unserialize( get_post_meta( $post->ID, 'ecm_field_group_location', true ) ?? [] );
			return $content;
		}, $posts);

		return self::$runtime_field_group_cache;
	}

	/**
	 * Published field groups located on a user profile (current_user / current_user_role).
	 *
	 * This is what the profile-screen bundle renders, and the only field-group
	 * data a non-privileged user has any business receiving. Mirrors the meta-key
	 * allowlist in {@see get_user_scoped_custom_field_meta_keys()}.
	 *
	 * @return array
	 */
	public static function get_user_scoped_field_groups(): array {
		return self::get_published_field_groups_matching( function ( array $locations ): bool {
			return ! empty( $locations['current_user'] ) || ! empty( $locations['current_user_role'] );
		} );
	}

	/**
	 * Published field groups whose location targets one of the given post types.
	 *
	 * The front end must never be handed {@see get_all_custom_field_groups()}:
	 * that returns every group on the site at any status — drafts and trashed
	 * included — with its complete definition (field keys, labels, types,
	 * conditional logic and location rules). Localized on a public page that is
	 * readable by anyone who views source.
	 *
	 * This returns only what a front-end form actually needs: published groups
	 * bound to the post types the visitor is being shown.
	 *
	 * @param string[] $post_types Post type slugs to scope to.
	 * @return array
	 */
	public static function get_published_field_groups_for_post_types( array $post_types ): array {
		$post_types = array_filter( array_map( 'strval', $post_types ) );

		if ( empty( $post_types ) ) {
			return [];
		}

		return self::get_published_field_groups_matching( function ( array $locations ) use ( $post_types ): bool {
			return (bool) array_intersect( $post_types, (array) ( $locations['post_type'] ?? [] ) );
		} );
	}

	/**
	 * Published field groups whose unserialized location data matches a predicate.
	 *
	 * Shared query/assembly logic behind {@see get_user_scoped_field_groups()} and
	 * {@see get_published_field_groups_for_post_types()}, which differ only in how
	 * they filter on location.
	 *
	 * @param callable(array):bool $matches Receives a group's location array, returns whether to include it.
	 * @return array
	 */
	private static function get_published_field_groups_matching( callable $matches ): array {
		$groups = [];

		$posts = get_posts([
			'post_type'      => self::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		]);

		foreach ( $posts as $post ) {
			$locations = (array) maybe_unserialize( get_post_meta( $post->ID, 'ecm_field_group_location', true ) );

			if ( ! $matches( $locations ) ) {
				continue;
			}

			$content             = maybe_unserialize( $post->post_content );
			$content['location'] = $locations;
			$groups[]            = $content;
		}

		return $groups;
	}
}
