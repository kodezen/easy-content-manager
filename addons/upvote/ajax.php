<?php

namespace ECMUpvote;

use ECMUpvote\Admin\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {
    public function __construct() {
        ( new Admin() )->dispatch_actions();
    }
}