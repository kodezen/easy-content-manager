<?php

namespace EasyContentManager\Utils\Traits;

use EasyContentManager\Utils\Helper;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait ToolsHelper {

	private function read_manifest_from_zip( $zip ) {
		$content = $zip->getFromName( 'manifest.json' );
		return $content ? json_decode( $content, true ) : null;
	}

	private function cleanup_temp_file( $path ) {
		if ( file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}
	
	private function get_existing_post_type_post( $slug ) {
		$posts = get_posts( [
			'post_type'      => Helper::ECM_POST_TYPE_NAME,
			'name'           => sanitize_title( $slug ),
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		return $posts[0] ?? null;
	}

	private function create_post_type_from_import( $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['post_slug'] ?? sanitize_title( $data['post_title'] );
		
		return wp_insert_post( [
			'post_title'   => sanitize_text_field( $data['post_title'] ),
			'post_name'    => sanitize_title( $slug ),
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => $data['post_status'] ?? 'publish',
			'post_type'    => Helper::ECM_POST_TYPE_NAME,
		], true );
	}

	private function update_post_type_from_import( $post_id, $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['post_slug'] ?? sanitize_title( $data['post_title'] );
		
		return wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => sanitize_text_field( $data['post_title'] ),
			'post_name'    => sanitize_title( $slug ),
			'post_content' => maybe_serialize( $post_content ),
		], true );
	}

	private function get_all_custom_post_types() {
		$query = new WP_Query( [
			'post_type'      => Helper::ECM_POST_TYPE_NAME,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		] );

		$posts = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();
				$posts[] = [
					'ID'           => $post->ID,
					'post_title'   => $post->post_title,
					'post_name'    => $post->post_name,
					'post_content' => maybe_unserialize( $post->post_content ),
					'post_status'  => $post->post_status,
				];
			}
			wp_reset_postdata();
		}
		return $posts;
	}
	
	private function prepare_post_content_for_export( $post, $post_type_slug ) {
		$exclude_meta_keys = apply_filters( 'ecm_export_post_meta_exclude_keys', [
			'_edit_lock',
			'_edit_last',
			'_wp_old_slug',
			'_wp_old_date',
			'_thumbnail_id',
		] );

		$meta = [];
		foreach ( get_post_meta( $post->ID ) as $meta_key => $meta_values ) {
			if ( in_array( $meta_key, $exclude_meta_keys, true ) ) {
				continue;
			}
			$meta[ $meta_key ] = array_map( 'maybe_unserialize', $meta_values );
		}

		$parent_slug = null;
		if ( $post->post_parent ) {
			$parent = get_post( $post->post_parent );
			$parent_slug = $parent ? $parent->post_name : null;
		}

		return [
			'post_title'       => $post->post_title,
			'post_name'        => $post->post_name,
			'post_content'     => $post->post_content,
			'post_excerpt'     => $post->post_excerpt,
			'post_status'      => $post->post_status,
			'menu_order'       => $post->menu_order,
			'comment_status'   => $post->comment_status,
			'post_date'        => $post->post_date,
			'post_parent_slug' => $parent_slug,
			'meta'             => $meta,
			'featured_image'   => $this->get_featured_image_data_for_export( $post->ID ),
			'taxonomies'       => $this->get_post_taxonomy_terms_for_export( $post->ID, $post_type_slug ),
		];
	}

	private function get_featured_image_data_for_export( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return null;
		}

		$url = wp_get_attachment_url( $thumbnail_id );
		if ( ! $url ) {
			return null;
		}

		$attachment = get_post( $thumbnail_id );

		return [
			'url'         => $url,
			'alt'         => get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ),
			'title'       => $attachment ? $attachment->post_title : '',
			'caption'     => $attachment ? $attachment->post_excerpt : '',
			'description' => $attachment ? $attachment->post_content : '',
		];
	}

	private function get_post_taxonomy_terms_for_export( $post_id, $post_type_slug ) {
		$taxonomies = get_object_taxonomies( $post_type_slug, 'names' );
		$data = [];

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			$data[ $taxonomy ] = array_map( function ( $term ) {
				$parent_slug = null;
				if ( $term->parent ) {
					$parent_term = get_term( $term->parent, $term->taxonomy );
					$parent_slug = ( $parent_term && ! is_wp_error( $parent_term ) ) ? $parent_term->slug : null;
				}
				return [
					'name'        => $term->name,
					'slug'        => $term->slug,
					'parent_slug' => $parent_slug,
				];
			}, $terms );
		}

		return $data;
	}

	private function get_existing_post_type_content_id( $post_type_slug, $slug ) {
		$posts = get_posts( [
			'post_type'      => $post_type_slug,
			'name'           => $slug,
			'post_status'    => 'any',
			'posts_per_page' => 1,
		] );

		return $posts[0]->ID ?? 0;
	}

	/**
	 * Post types an archive is allowed to create content in: the custom post types
	 * this plugin defines, and only those that are actually registered.
	 *
	 * @return string[]
	 */
	private function get_importable_content_post_types() {
		$slugs = [];

		foreach ( $this->get_all_custom_post_types() as $post_type_def ) {
			if ( empty( $post_type_def['post_name'] ) ) {
				continue;
			}

			$slug = sanitize_key( $post_type_def['post_name'] );
			if ( post_type_exists( $slug ) ) {
				$slugs[] = $slug;
			}
		}

		return array_values( array_unique( $slugs ) );
	}

	private function import_single_post_content( $post_type_slug, $data ) {
		$existing_id = $this->get_existing_post_type_content_id( $post_type_slug, sanitize_title( $data['post_name'] ) );

		// Statuses come from an untrusted archive; fall back rather than letting an
		// arbitrary string reach the posts table.
		$post_status = isset( $data['post_status'] ) ? sanitize_key( $data['post_status'] ) : 'publish';
		if ( ! in_array( $post_status, get_post_stati(), true ) ) {
			$post_status = 'publish';
		}

		$comment_status = isset( $data['comment_status'] ) ? sanitize_key( $data['comment_status'] ) : 'closed';
		if ( ! in_array( $comment_status, [ 'open', 'closed' ], true ) ) {
			$comment_status = 'closed';
		}

		$postarr = [
			'post_title'     => sanitize_text_field( $data['post_title'] ?? '' ),
			'post_name'      => sanitize_title( $data['post_name'] ),
			'post_content'   => $this->sanitize_imported_html( $data['post_content'] ?? '' ),
			'post_excerpt'   => $this->sanitize_imported_html( $data['post_excerpt'] ?? '' ),
			'post_status'    => $post_status,
			'post_type'      => $post_type_slug,
			'menu_order'     => (int) ( $data['menu_order'] ?? 0 ),
			'comment_status' => $comment_status,
		];

		// Only accept a well-formed MySQL datetime; anything else falls back to "now".
		if ( ! empty( $data['post_date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $data['post_date'] ) ) {
			$postarr['post_date'] = (string) $data['post_date'];
		}

		$created = ! $existing_id;

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$post_id = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			return [ 'post_id' => $post_id, 'created' => false ];
		}

		$this->import_post_meta_from_export( $post_id, $data['meta'] ?? [] );
		$this->import_featured_image_from_export( $post_id, $data['featured_image'] ?? null );
		$this->import_post_taxonomy_terms_from_export( $post_id, $data['taxonomies'] ?? [] );

		return [ 'post_id' => $post_id, 'created' => $created ];
	}

	/**
	 * Post content / excerpt arrive from an untrusted archive.
	 *
	 * `wp_kses_post()` strips HTML comments, which would destroy Gutenberg block
	 * delimiters, so it is only applied when the importing user is not trusted with
	 * raw HTML — mirroring how WordPress treats content on a normal post save.
	 *
	 * @param mixed $html
	 * @return string
	 */
	private function sanitize_imported_html( $html ) {
		$html = (string) $html;

		if ( current_user_can( 'unfiltered_html' ) ) {
			return $html;
		}

		return wp_kses_post( $html );
	}

	/**
	 * Meta keys an archive is allowed to write: the meta keys of ECM custom fields
	 * plus the plugin's own `ecm_`/`_ecm_` namespace. Everything else is dropped so a
	 * crafted archive cannot set arbitrary (including protected) post meta.
	 *
	 * @param string $meta_key
	 * @return bool
	 */
	private function is_importable_post_meta_key( $meta_key ) {
		static $field_meta_keys = null;

		if ( null === $field_meta_keys ) {
			$field_meta_keys = [];

			foreach ( Helper::get_all_custom_field_groups() as $field_group ) {
				foreach ( (array) ( $field_group['custom_fields'] ?? [] ) as $field ) {
					if ( ! empty( $field['meta_key'] ) ) {
						$field_meta_keys[] = $field['meta_key'];
					}
				}
			}

			$field_meta_keys = array_unique( $field_meta_keys );
		}

		// `_thumbnail_id` is deliberately absent: the featured image is re-resolved by
		// import_featured_image_from_export(), never taken as a raw attachment ID.
		$allowed = in_array( $meta_key, $field_meta_keys, true )
			|| 'ecm_priority' === $meta_key
			|| (bool) preg_match( '/^_?ecm_/', $meta_key );

		return (bool) apply_filters( 'easy_content_manager/import/is_importable_post_meta_key', $allowed, $meta_key );
	}

	private function import_post_meta_from_export( $post_id, $meta ) {
		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return;
		}

		foreach ( $meta as $meta_key => $meta_values ) {
			if ( ! is_string( $meta_key ) || ! $this->is_importable_post_meta_key( $meta_key ) ) {
				continue;
			}

			delete_post_meta( $post_id, $meta_key );
			foreach ( (array) $meta_values as $meta_value ) {
				add_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Featured image URLs come from an untrusted archive and are fetched server-side,
	 * so restrict them to http(s) with a real host and an image extension. Core's
	 * download_url() already routes through wp_safe_remote_get() (which blocks
	 * internal hosts); this narrows the surface further.
	 *
	 * @param string $url
	 * @return bool
	 */
	private function is_sideloadable_image_url( $url ) {
		$url   = (string) $url;
		$parts = wp_parse_url( $url );

		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
			return false;
		}

		if ( ! in_array( strtolower( $parts['scheme'] ), [ 'http', 'https' ], true ) ) {
			return false;
		}

		$extension = strtolower( pathinfo( $parts['path'], PATHINFO_EXTENSION ) );
		$allowed   = apply_filters(
			'easy_content_manager/import/sideloadable_image_extensions',
			[ 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'avif', 'svg' ]
		);

		return in_array( $extension, $allowed, true );
	}

	private function import_featured_image_from_export( $post_id, $image_data ) {
		if ( empty( $image_data['url'] ) || ! $this->is_sideloadable_image_url( $image_data['url'] ) ) {
			return;
		}

		$attachment_id = $this->find_attachment_by_source_url( $image_data['url'] );

		if ( ! $attachment_id ) {
			if ( ! function_exists( 'media_sideload_image' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$attachment_id = media_sideload_image( $image_data['url'], $post_id, $image_data['title'] ?? '', 'id' );

			if ( is_wp_error( $attachment_id ) ) {
				return;
			}

			update_post_meta( $attachment_id, '_ecm_source_url', $image_data['url'] );

			if ( ! empty( $image_data['alt'] ) ) {
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $image_data['alt'] ) );
			}

			if ( ! empty( $image_data['caption'] ) || ! empty( $image_data['description'] ) ) {
				// Same kses pass the post body gets — these come out of the archive
				// too, and skipping it here let an importer without `unfiltered_html`
				// land raw markup in the media library.
				wp_update_post( [
					'ID'           => $attachment_id,
					'post_excerpt' => $this->sanitize_imported_html( $image_data['caption'] ?? '' ),
					'post_content' => $this->sanitize_imported_html( $image_data['description'] ?? '' ),
				] );
			}
		}

		set_post_thumbnail( $post_id, $attachment_id );
	}

	private function find_attachment_by_source_url( $url ) {
		global $wpdb;

		$attachment_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_ecm_source_url' AND meta_value = %s LIMIT 1",
			$url
		) );

		return $attachment_id ? (int) $attachment_id : 0;
	}

	private function import_post_taxonomy_terms_from_export( $post_id, $taxonomies_data ) {
		if ( empty( $taxonomies_data ) || ! is_array( $taxonomies_data ) ) {
			return;
		}

		foreach ( $taxonomies_data as $taxonomy => $terms ) {
			if ( ! taxonomy_exists( $taxonomy ) || empty( $terms ) ) {
				continue;
			}

			$slug_to_term_id = [];
			$remaining = $terms;
			$guard = 0;

			while ( ! empty( $remaining ) && $guard < 50 ) {
				$guard++;
				$next_remaining = [];

				foreach ( $remaining as $term_data ) {
					$parent_id = 0;

					if ( ! empty( $term_data['parent_slug'] ) ) {
						if ( isset( $slug_to_term_id[ $term_data['parent_slug'] ] ) ) {
							$parent_id = $slug_to_term_id[ $term_data['parent_slug'] ];
						} else {
							$existing_parent = get_term_by( 'slug', $term_data['parent_slug'], $taxonomy );
							if ( $existing_parent ) {
								$parent_id = $existing_parent->term_id;
							} else {
								$next_remaining[] = $term_data;
								continue;
							}
						}
					}

					$slug_to_term_id[ $term_data['slug'] ] = $this->get_or_create_term_for_import( $taxonomy, $term_data, $parent_id );
				}

				$remaining = $next_remaining;
			}

			if ( ! empty( $slug_to_term_id ) ) {
				wp_set_object_terms( $post_id, array_values( $slug_to_term_id ), $taxonomy, false );
			}
		}
	}

	private function get_or_create_term_for_import( $taxonomy, $term_data, $parent_id ) {
		$existing = get_term_by( 'slug', $term_data['slug'], $taxonomy );
		if ( $existing ) {
			return $existing->term_id;
		}

		$inserted = wp_insert_term( $term_data['name'], $taxonomy, [
			'slug'   => $term_data['slug'],
			'parent' => $parent_id,
		] );

		return is_wp_error( $inserted ) ? 0 : $inserted['term_id'];
	}

	private function get_existing_taxonomy_post( $slug ) {
		$posts = get_posts( [
			'post_type'      => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
			'name'           => sanitize_title( $slug ),
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		return $posts[0] ?? null;
	}

	private function create_taxonomy_from_import( $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['taxonomy_slug'] ?? $data['post_title'];
		
		return wp_insert_post( [
			'post_title'   => $slug,
			'post_name'    => $slug,
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => $data['post_status'] ?? 'publish',
			'post_type'    => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
		], true );
	}

	private function update_taxonomy_from_import( $post_id, $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['taxonomy_slug'] ?? $data['post_title'];
		
		return wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => $slug,
			'post_name'    => $slug,
			'post_content' => maybe_serialize( $post_content ),
		], true );
	}

	private function link_taxonomy_to_post_types( $taxonomy_slug, $object_types ) {
		foreach ( (array) $object_types as $post_type ) {
			$post_type_slug = $post_type['post_type'] ?? $post_type['title'] ?? ( is_string( $post_type ) ? $post_type : null );
			if ( ! $post_type_slug ) continue;

			$posts = get_posts( [
				'name'           => $post_type_slug,
				'post_type'      => Helper::ECM_POST_TYPE_NAME,
				'posts_per_page' => 1,
				'post_status'    => 'any',
			] );

			if ( empty( $posts ) ) continue;

			$post_content = maybe_unserialize( $posts[0]->post_content );
			$post_content['taxonomies'] = $post_content['taxonomies'] ?? [];

			if ( ! in_array( $taxonomy_slug, $post_content['taxonomies'], true ) ) {
				$post_content['taxonomies'][] = $taxonomy_slug;
				wp_update_post( [
					'ID'           => $posts[0]->ID,
					'post_content' => maybe_serialize( $post_content ),
				] );
			}
		}
	}

	private function get_all_custom_taxonomies() {
		$query = new WP_Query( [
			'post_type'      => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'orderby'        => 'post_title',
			'order'          => 'asc',
		] );

		$results = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();
				$results[] = [
					'ID'           => $post->ID,
					'post_title'   => $post->post_title,
					'post_name'    => $post->post_name,
					'post_content' => maybe_unserialize( $post->post_content ),
					'post_status'  => $post->post_status,
				];
			}
			wp_reset_postdata();
		}
		return $results;
	}
	
	private function get_existing_setting_post( $slug ) {
		$posts = get_posts( [
			'post_type'      => Helper::ECM_SETTINGS_POST_TYPE_NAME,
			'name'           => sanitize_title( $slug ),
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		
		return $posts[0] ?? null;
	}

	private function create_setting_from_import( $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['slug'] ?? sanitize_title( $data['post_title'] );
		$title = $post_content['label'] ?? $data['post_title'];
		
		$post_id = wp_insert_post( [
			'post_title'   => sanitize_text_field( $title ),
			'post_name'    => sanitize_title( $slug ),
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => 'publish',
			'post_type'    => Helper::ECM_SETTINGS_POST_TYPE_NAME,
		], true );
		
		return $post_id;
	}

	private function update_setting_from_import( $post_id, $data ) {
		$post_content = $data['post_content'];
		$slug = $data['post_name'] ?? $post_content['slug'] ?? sanitize_title( $data['post_title'] );
		$title = $post_content['label'] ?? $data['post_title'];
		
		$result = wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => sanitize_text_field( $title ),
			'post_name'    => sanitize_title( $slug ),
			'post_content' => maybe_serialize( $post_content ),
		], true );
		
		return $result;
	}

	private function get_all_settings() {
		$posts = get_posts( [
			'post_type'      => Helper::ECM_SETTINGS_POST_TYPE_NAME,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
			'order'          => 'ASC',
		] );

		return [
			'items' => array_map( function ( $post ) {
				return [
					'id'           => $post->ID,
					'post_title'   => $post->post_title,
					'post_name'    => $post->post_name,
					'post_content' => maybe_unserialize( $post->post_content ) ?? [],
				];
			}, $posts ),
		];
	}
	
	private function get_existing_field_group_post( $title ) {
		$posts = get_posts( [
			'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'title'          => $title,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		] );
		return $posts[0] ?? null;
	}

	private function create_field_group_from_import( $data ) {
		$post_content = $data['post_content'];
		
		$post_id = wp_insert_post( [
			'post_title'   => sanitize_text_field( $data['post_title'] ),
			'post_name'    => sanitize_title( $data['post_name'] ?? $data['post_title'] ),
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => $data['post_status'] ?? 'publish',
			'post_type'    => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
		], true );

		if ( ! is_wp_error( $post_id ) && isset( $data['meta'] ) ) {
			foreach ( $data['meta'] as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

		if ( ! is_wp_error( $post_id ) && isset( $post_content['location'] ) ) {
			$field_group_location = Helper::get_post_types_from_location_array( $post_content['location'] );
			update_post_meta( $post_id, 'ecm_field_group_location', maybe_serialize( $field_group_location ) );
		}

		return $post_id;
	}

	private function update_field_group_from_import( $post_id, $data ) {
		$post_content = $data['post_content'];
		
		$result = wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => sanitize_text_field( $data['post_title'] ),
			'post_name'    => sanitize_title( $data['post_name'] ?? $data['post_title'] ),
			'post_content' => maybe_serialize( $post_content ),
		], true );

		if ( ! is_wp_error( $result ) && isset( $data['meta'] ) ) {
			foreach ( $data['meta'] as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

		if ( ! is_wp_error( $result ) && isset( $post_content['location'] ) ) {
			$field_group_location = Helper::get_post_types_from_location_array( $post_content['location'] );
			update_post_meta( $post_id, 'ecm_field_group_location', maybe_serialize( $field_group_location ) );
		}

		return $result;
	}

	private function get_all_field_groups() {
		$query = new WP_Query( [
			'post_type'      => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		] );

		$groups = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();
				$groups[] = [
					'ID'           => $post->ID,
					'post_title'   => $post->post_title,
					'post_name'    => $post->post_name,
					'post_content' => maybe_unserialize( $post->post_content ),
					'post_status'  => $post->post_status,
					'meta'         => get_post_meta( $post->ID ),
				];
			}
			wp_reset_postdata();
		}
		return $groups;
	}
	
	private function get_base_settings() {
		$possible_names = [
			defined( 'EASY_CONTENT_MANAGER_SETTINGS_NAME' ) ? constant( 'EASY_CONTENT_MANAGER_SETTINGS_NAME' ) : null,
			'easy_content_manager_settings',
			'ecm_settings'
		];
		
		$main_settings = [];
		$main_option_name = '';
		
		foreach ( $possible_names as $option_name ) {
			if ( ! $option_name ) continue;
			
			$settings = get_option( $option_name, false );
			
			if ( $settings !== false ) {
				if ( is_string( $settings ) ) {
					$settings = json_decode( $settings, true );
				}
				
				$main_settings = $settings ?: [];
				$main_option_name = $option_name;
				break;
			}
		}
		
		if ( empty( $main_option_name ) ) {
			$main_option_name = 'easy_content_manager_settings';
		}

		$addon_settings = [];
		$addon_option_names = [
			'ecm_addons_settings',
			'easy_content_manager_addons_settings'
		];

		if ( defined( 'EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME' ) ) {
			array_unshift( $addon_option_names, constant( 'EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME' ) );
		}
		
		foreach ( $addon_option_names as $addon_option ) {
			$addons = get_option( $addon_option, false );
			if ( $addons !== false ) {
				if ( is_string( $addons ) ) {
					$addons = json_decode( $addons, true );
				}
				$addon_settings = $addons ?: [];
				break;
			}
		}

		return [
			'option_name' => $main_option_name,
			'data' => $main_settings,
			'addon_settings' => $addon_settings
		];
	}

	private function import_base_settings_data( $data ) {
		$settings_option = 'easy_content_manager_settings';
		
		if ( defined( 'EASY_CONTENT_MANAGER_SETTINGS_NAME' ) ) {
			$settings_option = constant( 'EASY_CONTENT_MANAGER_SETTINGS_NAME' );
		}

		$json_data = is_string( $data ) ? $data : wp_json_encode( $data );
		
		delete_option( $settings_option );
		
		$result = add_option( $settings_option, $json_data, '', 'no' );
		
		return $result;
	}

	private function import_addon_settings_data( $addon_data ) {
		if ( empty( $addon_data ) ) {
			return true;
		}
		
		$addon_option = 'ecm_addons_settings';
		
		if ( defined( 'EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME' ) ) {
			$addon_option = constant( 'EASY_CONTENT_MANAGER_ADDONS_SETTINGS_NAME' );
		}
		
		$current_addons = (array) json_decode( get_option( $addon_option, '{}' ), true );
		
		$json_addon_data = is_string( $addon_data ) ? $addon_data : wp_json_encode( $addon_data );
		
		delete_option( $addon_option );
		$result = add_option( $addon_option, $json_addon_data, '', 'no' );
		
		// Only fire activation hooks for known ECM addon slugs — an imported archive
		// must not be able to trigger arbitrary dynamic `activated_{$slug}` actions.
		$known_addons = apply_filters(
			'easy_content_manager/known_addon_slugs',
			[ 'review-and-ratings', 'upvote', 'view-counter', 'claim', 'bookmark', 'reactions', 'frontend-submission', 'white-label' ]
		);

		if ( $result && is_array( $addon_data ) ) {
			foreach ( $addon_data as $addon_slug => $status ) {
				$addon_slug = sanitize_key( $addon_slug );
				if ( ! in_array( $addon_slug, $known_addons, true ) ) {
					continue;
				}

				$status = (bool) $status;
				$was_active = isset( $current_addons[ $addon_slug ] ) ? (bool) $current_addons[ $addon_slug ] : false;

				if ( $status && ! $was_active ) {
					do_action( 'easy_content_manager/before_active_addon', $addon_slug, [] );
					
					do_action( "easy-content-manager/addons/activated_{$addon_slug}", $status );
				} elseif ( ! $status && $was_active ) {
					do_action( "easy-content-manager/addons/deactivated_{$addon_slug}", $status );
				}
			}
		}
		
		return $result;
	}
}