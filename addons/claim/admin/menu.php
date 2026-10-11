<?php

namespace ECMClaim\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menu {
    public function __construct() {
        add_filter(
            'easy_content_manager/add_addons_menu',
            [ $this, 'add_admin_menu' ]
        );
    }

    public function add_admin_menu( array $menu ) {
        $menu[ EASY_CONTENT_MANAGER_PLUGIN_SLUG . '-claims' ] = [
            'parent_slug' => EASY_CONTENT_MANAGER_PLUGIN_SLUG,
            'title'       => __( 'Claims', 'easy-content-manager' ),
            'capability'  => 'manage_options',
            'is_addon'    => true,
        ];

        return $menu;
    }
}