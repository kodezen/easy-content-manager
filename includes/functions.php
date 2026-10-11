<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'ecm_get_plugins_post_type_path' ) ) {
	function ecm_get_plugins_post_type_path() {
		return apply_filters( 'ecm_add_plugins_post_type_path', [] );
	}
}

if ( ! function_exists( 'ecm_get_plugins_taxonomy_path' ) ) {
	function ecm_get_plugins_taxonomy_path() {
		return apply_filters( 'ecm_add_plugins_taxonomy_path', [] );
	}
}

if ( ! function_exists( 'ecm_get_plugins_settings_path' ) ) {
	function ecm_get_plugins_settings_path() {
		return apply_filters( 'ecm_add_plugins_settings_path', [] );
	}
}

if( ! function_exists( 'ecm_export_data' ) ) {
	function ecm_export_data( $args = [] ) {
		$helper = new \EasyContentManager\Utils\ImportExportHelper();
		return $helper->export( $args );
	}
}

if( ! function_exists( 'ecm_import_data' ) ) {
	function ecm_import_data( $file_path_or_args, $args = [] ) {
		$helper = new \EasyContentManager\Utils\ImportExportHelper();
		
		if ( is_string( $file_path_or_args ) ) {
			$args['file_path'] = $file_path_or_args;
		} else {
			$args = $file_path_or_args;
		}
		
		return $helper->import( $args );
	}
}

if( ! function_exists('ecm_is_safe_import_path')) {
	function ecm_is_safe_import_path( $file_path ) {
		$file_path = wp_normalize_path( $file_path );
		
		$upload_dir = wp_upload_dir();
		$allowed_paths = [
			wp_normalize_path( $upload_dir['basedir'] ),
			wp_normalize_path( WP_CONTENT_DIR ),
			wp_normalize_path( get_template_directory() ),
			wp_normalize_path( get_stylesheet_directory() ),
		];
		
		$allowed_paths = apply_filters( 'ecm_import_allowed_paths', $allowed_paths );
		
		foreach ( $allowed_paths as $allowed_path ) {
			if ( strpos( $file_path, $allowed_path ) === 0 ) {
				return true;
			}
		}
		
		return false;
	}
}


if( ! function_exists( 'ecm_add_import_path')) {
	function ecm_add_import_path( $path ) {
		add_filter( 'ecm_import_allowed_paths', function( $paths ) use ( $path ) {
			$paths[] = wp_normalize_path( $path );
			return $paths;
		} );
	}
}


if ( ! function_exists( 'ecm_load_json_files_from_folders' ) ) {
	/**
	 * Load JSON data from a list of folders and return as an array.
	 *
	 * @param array $folders List of folder paths.
	 * @return array<string,mixed> Associative array of JSON data keyed by filename (without extension)
	 */
	function ecm_load_json_files_from_folders( array $folders ): array {
		$all_data = [];

		$folders = (array) $folders;

		foreach ( $folders as $folder_path ) {
			if ( ! is_dir( $folder_path ) ) {
				continue;
			}

			$files = glob( trailingslashit( $folder_path ) . '*.json' );

			foreach ( $files as $file ) {
				$json = file_get_contents( $file );
				$data = json_decode( $json, true );

				if ( json_last_error() === JSON_ERROR_NONE ) {
					$all_data[ basename( $file, '.json' ) ] = $data;
				}
			}
		}

		return $all_data;
	}
}//end if

if ( ! function_exists( 'ecm_get_json_from_files' ) ) {
	/**
	 * Load JSON data from a list of files and return as an array.
	 *
	 * @param array $files List of file paths.
	 * @return array<string,mixed> Associative array of JSON data keyed by filename (without extension)
	 */
	function ecm_get_json_from_files( array $files ): array {
		$all_data = [];

		foreach ( $files as $file ) {
			if ( ! is_file( $file ) || pathinfo( $file, PATHINFO_EXTENSION ) !== 'json' ) {
				continue; // skip invalid or non-json files
			}

			$json = file_get_contents( $file );
			if ( false === $json ) {
				continue;
			}

			$data = json_decode( $json, true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				$key = basename( $file, '.json' );

				// Merge if key already exists
				if ( isset( $all_data[ $key ] ) && is_array( $all_data[ $key ] ) && is_array( $data ) ) {
					$all_data[ $key ] = array_merge_recursive( $all_data[ $key ], $data );
				} else {
					$all_data[ $key ] = $data;
				}
			}
		}//end foreach

		return $all_data;
	}
}//end if

/**
 * Recursively render nested groups/repeaters.
 */
if ( ! function_exists( 'ecm_nested_template_viewer' ) ) {
	function ecm_nested_template_viewer( $data, $level = 0 ) {
		if ( empty( $data ) ) {
			return;
		}

		echo '<div class="ecm-group__level ecm-group__level--' . esc_attr( $level ) . '">';

		foreach ( $data as $key => $val ) {
			$label = $key;

			echo '<div class="ecm-group__field">';
			echo '<strong class="ecm-group__key">' . esc_html( $label ) . ':</strong> ';

			if ( is_array( $val ) && isset( $val[0] ) && is_array( $val[0] ) ) {
				echo '<div class="ecm-group__repeater">';
				foreach ( $val as $i => $row ) {
					echo '<div class="ecm-group__repeater-item" data-index="' . esc_attr( $i ) . '">';
					ecm_nested_template_viewer( $row, $level + 1 );
					echo '</div>';
				}
				echo '</div>';
			} elseif ( is_array( $val ) ) {
				echo '<div class="ecm-group__subgroup">';
				ecm_nested_template_viewer( $val, $level + 1 );
				echo '</div>';
			} else {
				echo '<span class="ecm-group__value">' . esc_html( (string) $val ) . '</span>';
			}

			echo '</div>'; // .ecm-group__field
		}//end foreach

		echo '</div>'; // .ecm-group__level
	}
}//end if

