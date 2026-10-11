<?php

namespace EasyContentManager\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class User extends AbstractAjaxHandler {
    public function __construct() {
        $this->actions = [
            'get_user_meta' => [ 
                'callback'   => [ $this, 'get_user_meta' ],
                'fields'     => [ 'user_id' => 'integer' ],
                'capability' => 'edit_users',
            ],
        ];
    }

    public function get_user_meta( $payload ) {
        $user_id = isset( $payload['user_id'] ) ? intval( $payload['user_id'] ) : 0;

        if ( $user_id <= 0 ) {
            wp_send_json_error( [ 'message' => __( 'Invalid user ID.', 'easy-content-manager' ) ] );
        }

        $allowed_keys = Helper::get_user_scoped_custom_field_meta_keys();
        $user_meta    = get_user_meta( $user_id );

        $meta_data = [];
        foreach ( $allowed_keys as $key ) {
            if ( isset( $user_meta[ $key ][0] ) ) {
                $meta_data[ $key ] = maybe_unserialize( $user_meta[ $key ][0] );
            }
        }

        wp_send_json_success( [ 'meta' => $meta_data ] );
    }
}