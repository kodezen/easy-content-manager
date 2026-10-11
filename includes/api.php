<?php

namespace EasyContentManager;

use EasyContentManager\Api\PostPriority;
use EasyContentManager\API\Settings;
use EasyContentManager\Api\TermPriority;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class API {


	public static function init() {
		$self = new self();

		$self->register_routes();
	}

	public function register_routes() {
		Settings::init();
		new PostPriority();
		new TermPriority();
	}
}
