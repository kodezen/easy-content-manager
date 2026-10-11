<?php

namespace EasyContentManager;

use EasyContentManager\Admin\SeLicense;
use EasyContentManager\Ajax\Addons;
use EasyContentManager\Ajax\Admin;
use EasyContentManager\Ajax\BaseSettings;
use EasyContentManager\Ajax\FieldGroup;
use EasyContentManager\Ajax\FrontendSubmission;
use EasyContentManager\Ajax\PostItems;
use EasyContentManager\Ajax\PostTypeFields;
use EasyContentManager\Ajax\Permissions;
use EasyContentManager\Ajax\PostTypes;
use EasyContentManager\Ajax\Roles;
use EasyContentManager\Ajax\Settings;
use EasyContentManager\Ajax\Taxonomy;
use EasyContentManager\Ajax\Tools;
use EasyContentManager\Ajax\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax {

	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
		$self->pro_ajax();
	}

	private function dispatch_hooks() {
		( new PostTypeFields() )->dispatch_actions();
		( new PostTypes() )->dispatch_actions();
		( new Admin() )->dispatch_actions();
		( new PostItems() )->dispatch_actions();
		( new FieldGroup() )->dispatch_actions();
		( new Taxonomy() )->dispatch_actions();
		( new BaseSettings() )->dispatch_actions();
		( new Addons() )->dispatch_actions();
		( new Tools() )->dispatch_actions();
		( new FrontendSubmission() )->dispatch_actions();
		( new Roles() )->dispatch_actions();
		( new Permissions() )->dispatch_actions();
		( new User() )->dispatch_actions();
	}

	private function pro_ajax() {
		if ( ! SeLicense::is_pro() ) {
			return;
		}

		if ( class_exists( Settings::class ) ) {
			( new Settings() )->dispatch_actions();
		}
	}
}
