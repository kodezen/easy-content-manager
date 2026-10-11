<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Classes\Capabilities;

if( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Roles extends AbstractAjaxHandler {

	protected string $default_capability = Capabilities::VIEW_APP;

    public function __construct() {
        $this->actions = [
            'get_all_user_roles' => [
                'callback' => [ $this, 'get_all_user_roles' ]
            ]
        ];
    }

    public function get_all_user_roles() {
        $roles = wp_roles()->get_names();
        $formatted_roles = [];
        foreach ( $roles as $role_key => $role_name ) {
            $formatted_roles[] = [
                'key' => $role_key,
                'name' => $role_name 
            ];
        }
        wp_send_json_success( $formatted_roles );
    }
}