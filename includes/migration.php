<?php

namespace EasyContentManager;

use EasyContentManager\Classes\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Migration {
	public static function init() {
		$self = new self();
		$self->maybe_run_installation();

		// Capabilities must also be granted on upgrade, not only on activation:
		// an activation-only grant strands every site that updates in place.
		// Version-gated and idempotent, so this is a single option read per
		// request once it has run.
		Capabilities::maybe_install();
	}

	public function maybe_run_installation() {
		if ( empty( get_option( EASY_CONTENT_MANAGER_SETTINGS_NAME ) ) ) {
			if ( class_exists( 'EasyContentManager\\Installer' ) ) {
				$installer = new \EasyContentManager\Installer();
				$installer->save_main_settings();
			}
		}
	}
}
