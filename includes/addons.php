<?php
namespace EasyContentManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Addons {
	public static function init() {
		$self = new self();
		// Load all addons
		$self->addons_loader();
	}

	private function addons_loader() {
		$Autoload = Autoload::get_instance();
		$addons = apply_filters('easy_content_manager/addons/loader_args', [
			'review-and-ratings' => 'ReviewAndRatings',
			'upvote' => 'Upvote',
			'view-counter' => 'ViewCounter',
			'claim' => 'Claim',
			'bookmark' => 'Bookmark',
			'reactions' => 'Reactions',
			'frontend-submission' => 'FrontendSubmission',
		]);
		foreach ( $addons as $addon_name => $addon_class_name ) {
			$addon_root_path = EASY_CONTENT_MANAGER_ADDONS_DIR_PATH . $addon_name . '/';
			// Register the addon's root namespace and path.
			$addon_namespace = 'ECM' . $addon_class_name;
			$Autoload->add_namespace_directory( $addon_namespace, $addon_root_path );
			// Initialize the addon's main class.
			$class = $addon_namespace . '\\' . $addon_class_name;
			$class::init();
		}
	}
}
