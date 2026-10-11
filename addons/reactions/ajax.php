<?php

namespace ECMReactions;

use ECMReactions\Admin\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {
    public function __construct() {
        ( new Admin() )->dispatch_actions();
    }
}