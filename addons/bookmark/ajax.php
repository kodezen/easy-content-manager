<?php

namespace ECMBookmark;

use ECMBookmark\Ajax\ToggleBookmark;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ajax {
    public function __construct() {
        ( new ToggleBookmark() )->dispatch_actions();
    }
}