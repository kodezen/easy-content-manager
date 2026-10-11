<?php

namespace EasyContentManager\Utils;

use EasyContentManager\Features\RegisterPostTypes;
use EasyContentManager\Features\RegisterTaxonomies;
use EasyContentManager\Utils\Traits\ToolsHelper;
use ZipArchive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImportExportHelper {

	use ToolsHelper;

	/**
	 * Sub-directory of wp-uploads that generated export archives live in.
	 */
	const EXPORT_DIR_NAME = 'ecm-exports';

	public function export( $args = [] ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'zip_unavailable', __( 'ZipArchive class is not available.', 'easy-content-manager' ) );
		}

		$defaults = [
			'export_type' => 'all',
			'filename'    => null,
			'destination' => null,
		];

		$args = wp_parse_args( $args, $defaults );

		$export_type = $args['export_type'];
		$valid_types = [ 'all', 'post_types', 'taxonomies', 'settings_builder', 'field_groups', 'settings' ];
		
		if ( ! in_array( $export_type, $valid_types, true ) ) {
			return new \WP_Error( 'invalid_export_type', __( 'Invalid export type.', 'easy-content-manager' ) );
		}

		if ( $args['destination'] ) {
			$export_dir = trailingslashit( $args['destination'] );
			$is_custom_destination = true;
		} else {
			$export_dir = $this->get_protected_export_dir();
			$is_custom_destination = false;

			$this->cleanup_old_exports( untrailingslashit( $export_dir ) );
		}

		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
		}

		if ( $args['filename'] ) {
			$filename = $args['filename'];
			if ( substr( $filename, -4 ) !== '.zip' ) {
				$filename .= '.zip';
			}
		} else {
			// The archive contains every post (including drafts and private posts) and
			// all of their meta, so the name must not be guessable from the clock.
			$filename = 'ecm_export_' . time() . '_' . wp_generate_password( 24, false, false ) . '.zip';
		}

		$export_file = $export_dir . $filename;

		$zip = new ZipArchive();
		if ( $zip->open( $export_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
			return new \WP_Error( 'zip_create_failed', __( 'Unable to create ZIP file.', 'easy-content-manager' ) );
		}

		$stats = [
			'post_types'        => 0,
			'post_type_content' => 0,
			'taxonomies'        => 0,
			'settings_builder'  => 0,
			'field_groups'      => 0,
			'settings'          => 0,
			'settings_data'     => 0,
		];

		if ( $export_type === 'all' || $export_type === 'post_types' ) {
			$stats['post_types']        = $this->add_post_types_to_zip( $zip );
			$stats['post_type_content'] = $this->add_post_type_content_to_zip( $zip );
		}
		if ( $export_type === 'all' || $export_type === 'taxonomies' ) {
			$stats['taxonomies'] = $this->add_taxonomies_to_zip( $zip );
		}
		if ( $export_type === 'all' || $export_type === 'settings_builder' ) {
			$stats['settings_builder'] = $this->add_settings_builder_to_zip( $zip );
			$stats['settings_data'] = $this->add_settings_data_to_zip( $zip );
		}
		if ( $export_type === 'all' || $export_type === 'field_groups' ) {
			$stats['field_groups'] = $this->add_field_groups_to_zip( $zip );
		}
		if ( $export_type === 'all' || $export_type === 'settings' ) {
			$stats['settings'] = $this->add_settings_to_zip( $zip );
		}

		$zip->addFromString( 'manifest.json', wp_json_encode( [
			'version'        => '1.0',
			'export_type'    => $export_type,
			'export_date'    => current_time( 'mysql' ),
			'site_url'       => get_site_url(),
			'plugin_name'    => 'easy-content-manager',
			'plugin_version' => defined( 'EASY_CONTENT_MANAGER_VERSION' ) ? EASY_CONTENT_MANAGER_VERSION : '1.0.0',
			'stats'          => $stats,
		], JSON_PRETTY_PRINT ) );

		$zip->close();

		$response = [
			'file_path' => $export_file,
			'filename'  => $filename,
			'stats'     => $stats,
		];

		// The export directory is not web-readable, so hand back an authenticated
		// download endpoint rather than a direct file URL.
		if ( ! $is_custom_destination ) {
			$response['file_url'] = self::get_export_download_url( $filename );
		}

		return $response;
	}

	/**
	 * Directory that generated exports are written to.
	 *
	 * Exports contain every post (drafts and private posts included) plus all of
	 * their meta, so they must never be directly fetchable over HTTP. The directory
	 * is created inside uploads with server-level deny rules and an index guard;
	 * downloads go through the capability-checked endpoint instead.
	 *
	 * @return string Trailing-slashed absolute path.
	 */
	public static function get_protected_export_dir() {
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( trailingslashit( $upload_dir['basedir'] ) . self::EXPORT_DIR_NAME );

		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
		}

		$guards = [
			'.htaccess'  => "Require all denied\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n",
			'web.config' => "<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
			'index.php'  => "<?php\n// Silence is golden.\n",
		];

		foreach ( $guards as $guard_file => $contents ) {
			$path = $export_dir . $guard_file;
			if ( ! file_exists( $path ) ) {
				file_put_contents( $path, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writing a static access guard during export; WP_Filesystem would require credentials on some hosts.
			}
		}

		return $export_dir;
	}

	/**
	 * Nonced, capability-checked download URL for a generated export archive.
	 *
	 * @param string $filename Basename of the archive.
	 * @return string
	 */
	public static function get_export_download_url( $filename ) {
		// Built with add_query_arg rather than wp_nonce_url(): that helper esc_html's
		// its return value, and the encoded `&amp;` separators would corrupt the query
		// string when the client assigns this URL to window.location.
		return add_query_arg(
			[
				'action'   => 'ecm_download_export',
				'file'     => rawurlencode( $filename ),
				'_wpnonce' => wp_create_nonce( 'ecm_download_export' ),
			],
			admin_url( 'admin-post.php' )
		);
	}

	/**
	 * Resolve an export filename to a real path inside the protected export
	 * directory, or false when it escapes that directory / does not exist.
	 *
	 * @param string $filename
	 * @return string|false
	 */
	public static function resolve_export_file( $filename ) {
		$filename = basename( wp_unslash( (string) $filename ) );

		if ( '' === $filename || substr( $filename, -4 ) !== '.zip' ) {
			return false;
		}

		$export_dir = wp_normalize_path( self::get_protected_export_dir() );
		$real       = realpath( $export_dir . $filename );

		if ( false === $real ) {
			return false;
		}

		$real = wp_normalize_path( $real );

		return 0 === strpos( $real, $export_dir ) ? $real : false;
	}

	public function import( $args = [] ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'zip_unavailable', __( 'ZipArchive class is not available.', 'easy-content-manager' ) );
		}

		$defaults = [
			'file_path'   => '',
			'import_type' => 'all',
			'delete_file' => false,
		];

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['file_path'] ) ) {
			return new \WP_Error( 'missing_file_path', __( 'File path is required.', 'easy-content-manager' ) );
		}

		$import_type = $args['import_type'];
		$valid_types = [ 'all', 'post_types', 'taxonomies', 'settings_builder', 'field_groups', 'settings' ];
		
		if ( ! in_array( $import_type, $valid_types, true ) ) {
			return new \WP_Error( 'invalid_import_type', __( 'Invalid import type.', 'easy-content-manager' ) );
		}

		$file_path = $args['file_path'];

		if ( ! $this->is_safe_file_path( $file_path ) ) {
			return new \WP_Error( 'unsafe_file_path', __( 'Invalid or unsafe file path.', 'easy-content-manager' ) );
		}

		if ( ! file_exists( $file_path ) ) {
			return new \WP_Error( 'file_not_found', __( 'File not found at the specified path.', 'easy-content-manager' ) );
		}

		if ( wp_check_filetype( $file_path )['ext'] !== 'zip' ) {
			return new \WP_Error( 'invalid_file_type', __( 'Only ZIP files are allowed.', 'easy-content-manager' ) );
		}

		$zip = new ZipArchive();
		if ( $zip->open( $file_path ) !== true ) {
			return new \WP_Error( 'zip_open_failed', __( 'Unable to open ZIP file.', 'easy-content-manager' ) );
		}

		$manifest = $this->read_manifest_from_zip( $zip );
		if ( ! $manifest || ( $manifest['plugin_name'] ?? '' ) !== 'easy-content-manager' ) {
			$zip->close();
			return new \WP_Error( 'invalid_export_file', __( 'Invalid or incompatible export file.', 'easy-content-manager' ) );
		}

		$stats = [
			'post_types'        => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'post_type_content' => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'taxonomies'        => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'settings_builder'  => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'field_groups'      => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'settings'          => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
			'settings_data'     => [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ],
		];

		$should_import_field_groups = false;
		
		if ( $import_type === 'all' ) {
			$should_import_field_groups = true;
		} elseif ( in_array( $import_type, [ 'post_types', 'taxonomies', 'settings_builder' ] ) ) {
			$has_field_groups = false;
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$filename = $zip->getNameIndex( $i );
				if ( strpos( $filename, 'field-groups/' ) === 0 ) {
					$has_field_groups = true;
					break;
				}
			}
			
			if ( $has_field_groups ) {
				$should_import_field_groups = true;
			}
		}

		if ( $import_type === 'all' || $import_type === 'settings' ) {
			$stats['settings'] = $this->import_settings_from_zip( $zip );
		}
		
		if ( $should_import_field_groups ) {
			$stats['field_groups'] = $this->import_field_groups_from_zip( $zip );
		}
		
		if ( $import_type === 'all' || $import_type === 'post_types' ) {
			$stats['post_types'] = $this->import_post_types_from_zip( $zip );
		}

		if ( $import_type === 'all' || $import_type === 'taxonomies' ) {
			$stats['taxonomies'] = $this->import_taxonomies_from_zip( $zip );
		}

		if ( $import_type === 'all' || $import_type === 'post_types' ) {
			$stats['post_type_content'] = $this->import_post_type_content_from_zip( $zip );
		}

		if ( $import_type === 'all' || $import_type === 'settings_builder' ) {
			$stats['settings_builder'] = $this->import_settings_builder_from_zip( $zip );
			$stats['settings_data'] = $this->import_settings_data_from_zip( $zip );
		}

		$zip->close();

		if ( $args['delete_file'] ) {
			$this->cleanup_temp_file( $file_path );
		}

		delete_transient( 'ecm_cached_prepared_field_groups' );
		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );
		// Hooks::register_post_meta() caches the post-type slug list for a day.
		// Without this, imported post types went up to 24 hours with no
		// `ecm_priority` meta registered and priority ordering silently did nothing.
		delete_transient( 'ecm_priority_meta_slugs' );

		update_option( 'ecm_flush_rewrite_rules', true );
		update_option( 'ecm_flush_taxonomy_rules', true );

		return [
			'stats'   => $stats,
			'message' => __( 'Import completed successfully.', 'easy-content-manager' ),
		];
	}

	private function add_post_types_to_zip( $zip ) {
		$count = 0;
		foreach ( $this->get_all_custom_post_types() as $item ) {
			if ( empty( $item ) ) continue;
			$zip->addFromString(
				'post-types/' . $item['post_name'] . '.json',
				wp_json_encode( [
					'post_title'   => $item['post_title'],
					'post_name'    => $item['post_name'],
					'post_content' => $item['post_content'],
					'post_status'  => $item['post_status'],
				], JSON_PRETTY_PRINT )
			);
			$count++;
		}
		return $count;
	}

	private function add_post_type_content_to_zip( $zip ) {
		$count = 0;

		foreach ( $this->get_all_custom_post_types() as $post_type_def ) {
			if ( empty( $post_type_def['post_name'] ) ) {
				continue;
			}

			$post_type_slug = $post_type_def['post_name'];

			$posts = get_posts( [
				'post_type'      => $post_type_slug,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			] );

			foreach ( $posts as $post ) {
				if ( empty( $post->post_name ) ) {
					continue;
				}

				$zip->addFromString(
					"content/{$post_type_slug}/{$post->post_name}.json",
					wp_json_encode( $this->prepare_post_content_for_export( $post, $post_type_slug ), JSON_PRETTY_PRINT )
				);
				$count++;
			}
		}

		return $count;
	}

	private function add_taxonomies_to_zip( $zip ) {
		$count = 0;
		foreach ( $this->get_all_custom_taxonomies() as $item ) {
			if ( empty( $item ) ) continue;
			$zip->addFromString(
				'taxonomies/' . $item['post_name'] . '.json',
				wp_json_encode( [
					'post_title'   => $item['post_title'],
					'post_name'    => $item['post_name'],
					'post_content' => $item['post_content'],
					'post_status'  => $item['post_status'],
				], JSON_PRETTY_PRINT )
			);
			$count++;
		}
		return $count;
	}

	private function add_settings_builder_to_zip( $zip ) {
		$count = 0;
		foreach ( $this->get_all_settings()['items'] ?? [] as $item ) {
			$zip->addFromString(
				'settings-builder/' . $item['post_name'] . '.json',
				wp_json_encode( [
					'ID'           => $item['id'],
					'post_title'   => $item['post_title'],
					'post_name'    => $item['post_name'],
					'post_content' => $item['post_content'],
				], JSON_PRETTY_PRINT )
			);
			$count++;
		}
		return $count;
	}

	private function add_settings_data_to_zip( $zip ) {
		$count = 0;
		
		$settings_items = $this->get_all_settings()['items'] ?? [];
		
		foreach ( $settings_items as $item ) {
			$option_name = $item['post_content']['option_name'] ?? '';
			$routes = $item['post_content']['routes'] ?? [];
			
			if ( empty( $option_name ) || empty( $routes ) ) {
				continue;
			}
			
			$saved_data = get_option( $option_name, [] );
			$saved_data = maybe_unserialize( $saved_data );
			
			if ( empty( $saved_data ) ) {
				continue;
			}
			
			$settings_data = [
				'option_name' => $option_name,
				'slug'        => $item['post_name'],
				'routes'      => []
			];
			
			foreach ( $routes as $route ) {
				$route_slug = is_array( $route ) ? ( $route['slug'] ?? '' ) : $route;
				
				if ( ! empty( $route_slug ) && isset( $saved_data[ $route_slug ] ) ) {
					$settings_data['routes'][ $route_slug ] = $saved_data[ $route_slug ];
				}
			}
			
			if ( ! empty( $settings_data['routes'] ) ) {
				$zip->addFromString(
					'settings-data/' . $item['post_name'] . '.json',
					wp_json_encode( $settings_data, JSON_PRETTY_PRINT )
				);
				$count++;
			}
		}
		
		return $count;
	}

	private function add_field_groups_to_zip( $zip ) {
		$count = 0;
		foreach ( $this->get_all_field_groups() as $item ) {
			if ( empty( $item ) ) continue;
			$zip->addFromString(
				'field-groups/' . sanitize_title( $item['post_title'] ) . '.json',
				wp_json_encode( [
					'post_title'   => $item['post_title'],
					'post_name'    => $item['post_name'],
					'post_content' => $item['post_content'],
					'post_status'  => $item['post_status'],
					'meta'         => $item['meta'] ?? [],
				], JSON_PRETTY_PRINT )
			);
			$count++;
		}
		return $count;
	}

	private function add_settings_to_zip( $zip ) {
		$settings = $this->get_base_settings();
		if ( empty( $settings['data'] ) && empty( $settings['addon_settings'] ) ) {
			return 0;
		}
		
		$export_data = [
			'option_name' => $settings['option_name'],
			'data' => $settings['data']
		];
		
		// Include addon settings if present
		if ( ! empty( $settings['addon_settings'] ) ) {
			$export_data['addon_settings'] = $settings['addon_settings'];
		}
		
		$zip->addFromString(
			'settings/config.json',
			wp_json_encode( $export_data, JSON_PRETTY_PRINT )
		);
		
		return 1;
	}

	private function import_post_types_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'post-types/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$data = json_decode( $zip->getFromIndex( $i ), true );
			if ( ! $data || ! isset( $data['post_content'] ) ) {
				$stats['failed']++;
				continue;
			}

			$slug = $data['post_name'] ?? $data['post_content']['post_slug'] ?? sanitize_title( $data['post_title'] );
			$existing = $this->get_existing_post_type_post( $slug );

			if ( $existing ) {
				$result = $this->update_post_type_from_import( $existing->ID, $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['updated']++;
			} else {
				$result = $this->create_post_type_from_import( $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['imported']++;
			}
		}

		return $stats;
	}

	private function import_post_type_content_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		$files_by_post_type = [];
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'content/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$relative = substr( $filename, strlen( 'content/' ) );
			$parts = explode( '/', $relative );
			if ( count( $parts ) < 2 || empty( $parts[0] ) ) continue;

			$files_by_post_type[ $parts[0] ][] = $filename;
		}

		if ( empty( $files_by_post_type ) ) {
			return $stats;
		}

		delete_transient( 'ecm_cached_prepared_post_types' );
		delete_transient( 'ecm_cached_prepared_custom_fields' );
		( new RegisterPostTypes() )->register_custom_post_type();
		( new RegisterTaxonomies() )->register_custom_taxonomies();

		// The post type comes from a path inside an untrusted archive, so restrict it
		// to types this plugin owns. Without this an archive could create `attachment`,
		// `nav_menu_item` or even ECM's own configuration posts.
		$importable_post_types = $this->get_importable_content_post_types();

		foreach ( $files_by_post_type as $post_type_slug => $filenames ) {
			$post_type_slug = sanitize_key( $post_type_slug );

			if ( ! in_array( $post_type_slug, $importable_post_types, true ) ) {
				$stats['failed'] += count( $filenames );
				continue;
			}

			$slug_to_id = [];
			$pending_parents = [];

			foreach ( $filenames as $filename ) {
				$data = json_decode( $zip->getFromName( $filename ), true );
				if ( ! $data || empty( $data['post_name'] ) ) {
					$stats['failed']++;
					continue;
				}

				$result = $this->import_single_post_content( $post_type_slug, $data );

				if ( is_wp_error( $result['post_id'] ) ) {
					$stats['failed']++;
					continue;
				}

				$slug_to_id[ $data['post_name'] ] = $result['post_id'];
				$result['created'] ? $stats['imported']++ : $stats['updated']++;

				if ( ! empty( $data['post_parent_slug'] ) ) {
					$pending_parents[ $result['post_id'] ] = $data['post_parent_slug'];
				}
			}

			foreach ( $pending_parents as $post_id => $parent_slug ) {
				$parent_id = $slug_to_id[ $parent_slug ] ?? $this->get_existing_post_type_content_id( $post_type_slug, $parent_slug );
				if ( $parent_id ) {
					wp_update_post( [ 'ID' => $post_id, 'post_parent' => $parent_id ] );
				}
			}
		}

		return $stats;
	}

	private function import_taxonomies_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'taxonomies/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$data = json_decode( $zip->getFromIndex( $i ), true );
			if ( ! $data || ! isset( $data['post_content'] ) ) {
				$stats['failed']++;
				continue;
			}

			$slug = $data['post_name'] ?? $data['post_content']['taxonomy_slug'] ?? $data['post_title'];
			$existing = $this->get_existing_taxonomy_post( $slug );

			if ( $existing ) {
				$result = $this->update_taxonomy_from_import( $existing->ID, $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['updated']++;
			} else {
				$result = $this->create_taxonomy_from_import( $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['imported']++;
			}

			if ( ! is_wp_error( $result ) && isset( $data['post_content']['object_type'] ) ) {
				$this->link_taxonomy_to_post_types( $slug, $data['post_content']['object_type'] );
			}
		}

		return $stats;
	}

	private function import_settings_builder_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'settings-builder/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$data = json_decode( $zip->getFromIndex( $i ), true );
			if ( ! $data || ! isset( $data['post_content'] ) ) {
				$stats['failed']++;
				continue;
			}

			$slug = $data['post_name'] ?? $data['post_content']['slug'] ?? sanitize_title( $data['post_title'] );
			$existing = $this->get_existing_setting_post( $slug );

			if ( $existing ) {
				$result = $this->update_setting_from_import( $existing->ID, $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['updated']++;
			} else {
				$result = $this->create_setting_from_import( $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['imported']++;
			}
		}

		return $stats;
	}

	/**
	 * Only plugin-owned option names may be written by an import. Prevents a crafted
	 * archive from clobbering core/other-plugin options (e.g. active_plugins, siteurl).
	 *
	 * @param string $option_name
	 * @return bool
	 */
	private function is_importable_option_name( $option_name ) {
		$allowed = (bool) preg_match( '/^(ecm_|easy_content_manager)/', (string) $option_name );
		return (bool) apply_filters( 'easy_content_manager/import/is_importable_option', $allowed, $option_name );
	}

	private function import_settings_data_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'settings-data/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$data = json_decode( $zip->getFromIndex( $i ), true );
			if ( ! $data || empty( $data['option_name'] ) || empty( $data['routes'] ) ) {
				$stats['failed']++;
				continue;
			}

			$option_name = $data['option_name'];
			$routes_data = $data['routes'];

			if ( ! $this->is_importable_option_name( $option_name ) ) {
				$stats['failed']++;
				continue;
			}

			$existing_data = get_option( $option_name, [] );
			$existing_data = maybe_unserialize( $existing_data );
			
			if ( ! is_array( $existing_data ) ) {
				$existing_data = [];
			}
			
			$has_changes = false;
			foreach ( $routes_data as $route_slug => $route_values ) {
				$existing_data[ $route_slug ] = $route_values;
				$has_changes = true;
			}
			
			if ( $has_changes ) {
				$result = update_option( $option_name, maybe_serialize( $existing_data ) );
				
				if ( $result ) {
					$stats['imported']++;
				} else {
					$current = get_option( $option_name );
					$current = maybe_unserialize( $current );
					
					if ( $current === $existing_data ) {
						$stats['updated']++;
					} else {
						$stats['failed']++;
					}
				}
			} else {
				$stats['failed']++;
			}
		}

		return $stats;
	}

	private function import_field_groups_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$filename = $zip->getNameIndex( $i );
			if ( strpos( $filename, 'field-groups/' ) !== 0 || substr( $filename, -5 ) !== '.json' ) continue;

			$data = json_decode( $zip->getFromIndex( $i ), true );
			if ( ! $data || ! isset( $data['post_content'] ) ) {
				$stats['failed']++;
				continue;
			}

			$title = $data['post_title'] ?? '';
			$existing = $this->get_existing_field_group_post( $title );

			if ( $existing ) {
				$result = $this->update_field_group_from_import( $existing->ID, $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['updated']++;
			} else {
				$result = $this->create_field_group_from_import( $data );
				is_wp_error( $result ) ? $stats['failed']++ : $stats['imported']++;
			}
		}

		return $stats;
	}

	private function import_settings_from_zip( $zip ) {
		$stats = [ 'imported' => 0, 'updated' => 0, 'failed' => 0 ];

		$content = $zip->getFromName( 'settings/config.json' );
		if ( ! $content ) {
			return $stats;
		}

		$file_data = json_decode( $content, true );
		if ( ! $file_data ) {
			$stats['failed'] = 1;
			return $stats;
		}

		$main_data = null;
		$addon_data = null;
		
		if ( isset( $file_data['data'] ) && isset( $file_data['option_name'] ) ) {
			$main_data = $file_data['data'];
			
			if ( isset( $file_data['addon_settings'] ) ) {
				$addon_data = $file_data['addon_settings'];
			}
		} else {
			$main_data = $file_data;
		}

		$result = $this->import_base_settings_data( $main_data );
		
		$addon_result = true;
		if ( $addon_data !== null ) {
			$addon_result = $this->import_addon_settings_data( $addon_data );
		}
		
		if ( $result && $addon_result ) {
			$stats['imported'] = 1;
		} else {
			$stats['failed'] = 1;
		}

		return $stats;
	}

	private function is_safe_file_path( $file_path ) {
		// Resolve the real path first so `../` traversal can't slip past the prefix
		// check below. A non-existent path resolves to false and is rejected.
		$real = realpath( $file_path );
		if ( false === $real ) {
			return false;
		}
		$file_path = wp_normalize_path( $real );

		$upload_dir = wp_upload_dir();
		$allowed_paths = [
			wp_normalize_path( $upload_dir['basedir'] ),
			wp_normalize_path( WP_CONTENT_DIR ),
			wp_normalize_path( get_template_directory() ),
			wp_normalize_path( get_stylesheet_directory() ),
		];

		$allowed_paths = apply_filters( 'ecm_import_allowed_paths', $allowed_paths );

		foreach ( $allowed_paths as $allowed_path ) {
			$allowed_path = wp_normalize_path( realpath( $allowed_path ) ?: $allowed_path );
			if ( strpos( $file_path, $allowed_path ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	private function cleanup_old_exports( $base_dir ) {
		foreach ( glob( $base_dir . '/ecm_export_*.zip' ) as $file ) {
			wp_delete_file( $file );
		}

		// Earlier versions wrote exports straight into the uploads root, where they
		// stayed publicly fetchable. Sweep any leftovers whenever a new export runs.
		$upload_dir = wp_upload_dir();
		foreach ( glob( trailingslashit( $upload_dir['basedir'] ) . 'ecm_export_*.zip' ) as $legacy_file ) {
			wp_delete_file( $legacy_file );
		}
	}
}