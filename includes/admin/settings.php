<?php

namespace EasyContentManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EasyContentManager\Admin\Settings\Base;

class Settings {



	public static function init() {
		$self = new self();
		$self->save_settings();
	}

	public static function save_settings() {
		Base::save_settings();
	}
}
