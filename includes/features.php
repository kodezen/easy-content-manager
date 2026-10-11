<?php

namespace EasyContentManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Features {



	public static function init() {
		Features\RegisterPostTypes::init();
		Features\RegisterFields::init();
		Features\RegisterTaxonomies::init();
	}
}
