<?php

namespace ECMClaim;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Installer {
    public static function init() {
        $self = new self();
        $self->create_database();
    }

    public function create_database() {
        Database::init();
    }
}