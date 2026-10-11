<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\ImportExportHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tools extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::MANAGE_TOOLS;

	public function __construct() {
		$this->actions = [
			'export_data' => [
				'callback'   => [ $this, 'export_data' ],
				'capability' => Capabilities::MANAGE_TOOLS,
				'fields'     => [ 'export_type' => 'string' ],
			],
			'import_data' => [
				'callback'   => [ $this, 'import_data' ],
				'capability' => Capabilities::MANAGE_TOOLS,
				// NOTE: deliberately no `file_path` field. Letting the client name a
				// path turned a two-step (upload, then import) into one step for
				// anything already sitting under wp-content — and uploads is
				// writable by every role holding `upload_files`. The PHP-side
				// ecm_import_data() helper still takes a path; the wire does not.
				'fields'     => [
					'import_type' => 'string',
					'file'        => 'file|optional',
				],
			],
		];

		add_action( 'admin_post_ecm_download_export', [ $this, 'download_export' ] );
	}

	/**
	 * Stream a generated export archive.
	 *
	 * Exports live outside the web root's reach and carry every post plus all post
	 * meta, so the download is gated on a capability and a nonce rather than being
	 * served as a static file URL. The gate is the same `MANAGE_TOOLS` capability
	 * that produced the archive — hardcoding `manage_options` here meant a role
	 * granted Manage Tools could generate an export and then get a 403 fetching it.
	 *
	 * @return void
	 */
	public function download_export() {
		if ( ! Capabilities::user_can( Capabilities::MANAGE_TOOLS ) ) {
			wp_die( esc_html__( 'You do not have permission to download exports.', 'easy-content-manager' ), '', [ 'response' => 403 ] );
		}

		check_admin_referer( 'ecm_download_export' );

		$requested = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
		$file_path = ImportExportHelper::resolve_export_file( $requested );

		if ( ! $file_path ) {
			wp_die( esc_html__( 'Export file not found.', 'easy-content-manager' ), '', [ 'response' => 404 ] );
		}

		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'X-Content-Type-Options: nosniff' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a binary archive; WP_Filesystem would buffer it entirely in memory.
		readfile( $file_path );
		exit;
	}

	public function export_data( $payload ) {
		$helper = new ImportExportHelper();
		
		$result = $helper->export( [
			'export_type' => $payload['export_type'] ?? 'all',
		] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [
			'zip_url'  => $result['file_url'] ?? '',
			'filename' => $result['filename'],
			'stats'    => $result['stats'],
		] );
	}

	public function import_data( $payload ) {
		$helper = new ImportExportHelper();
		
		// The archive always comes from this request's own upload, which lands in
		// the protected export directory under an unguessable name.
		$file = $this->handle_file_upload();

		if ( is_wp_error( $file ) ) {
			wp_send_json_error( [ 'message' => $file->get_error_message() ] );
		}

		$result = $helper->import( [
			'file_path'   => $file['path'],
			'import_type' => $payload['import_type'] ?? 'all',
			'delete_file' => true,
		] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [
			'stats'   => $result['stats'],
			'message' => $result['message'],
		] );
	}

	private function handle_file_upload() {
		if ( ! isset( $_FILES['file'] ) || empty( $_FILES['file']['tmp_name'] ) ) {
			return new \WP_Error( 'no_file', __( 'No file uploaded.', 'easy-content-manager' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Individual members validated below.
		$file = $_FILES['file'];

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			return new \WP_Error( 'upload_error', __( 'File upload failed.', 'easy-content-manager' ) );
		}

		// Check the real contents, not just the client-supplied name.
		$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], [ 'zip' => 'application/zip' ] );

		if ( 'zip' !== $filetype['ext'] || wp_check_filetype( $file['name'] )['ext'] !== 'zip' ) {
			return new \WP_Error( 'invalid_type', __( 'Only ZIP files are allowed.', 'easy-content-manager' ) );
		}

		// Land the upload in the protected export directory with an unguessable name
		// so it is never fetchable over HTTP while the import runs.
		$temp_file = ImportExportHelper::get_protected_export_dir()
			. 'ecm_import_' . time() . '_' . wp_generate_password( 24, false, false ) . '.zip';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_move_uploaded_file -- Direct move is required to keep the archive out of the media library.
		if ( ! move_uploaded_file( $file['tmp_name'], $temp_file ) ) {
			return new \WP_Error( 'move_failed', __( 'Failed to process file.', 'easy-content-manager' ) );
		}

		return [ 'path' => $temp_file ];
	}
}