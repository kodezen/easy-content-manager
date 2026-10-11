<?php
namespace EasyContentManager;

use EasyContentManager\Admin\Menu;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin {

	public static function init() {
		Menu::init();
		add_action( 'admin_init', [ self::class, 'redirect_unauthorized_react_route' ] );
	}

	/**
	 * Redirect a direct request before WordPress renders its access-denied page.
	 *
	 * React cannot handle a reload of an unauthorized plugin page because
	 * WordPress stops the request before the bundle is enqueued.
	 */
	public static function redirect_unauthorized_react_route(): void {
		if ( wp_doing_ajax() || 'GET' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) || empty( $_GET['page'] ) ) {
			return;
		}

		$page       = sanitize_key( wp_unslash( $_GET['page'] ) );
		$app_prefix = Helper::get_ecm_app_slug();
		$is_ecm_request = EASY_CONTENT_MANAGER_PLUGIN_SLUG === $page
			|| 0 === strpos( $page, EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-' )
			|| $app_prefix === $page
			|| 0 === strpos( $page, $app_prefix . '-' );

		if ( ! $is_ecm_request ) {
			return;
		}

		$route_capabilities = Helper::get_react_admin_route_capabilities();
		if ( ! isset( $route_capabilities[ $page ] ) ) {
			return;
		}

		if ( current_user_can( $route_capabilities[ $page ] ) ) {
			return;
		}

		$allowed_routes = Helper::get_allowed_react_admin_routes();
		$first_route    = reset( $allowed_routes );
		$redirect_url   = $first_route
			? admin_url( 'admin.php?page=' . rawurlencode( $first_route ) )
			: admin_url();

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
