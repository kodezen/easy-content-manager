<?php

namespace EasyContentManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Shortcode {
	public static function init() {
		// Initialize shortcodes
		new Shortcode\EcmFields();
	}
}
