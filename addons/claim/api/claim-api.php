<?php
namespace ECMClaim\Api;

use EasyContentManager\Utils\Helper;
use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ClaimApi extends WP_REST_Controller {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

      public function register_routes() {
        register_rest_route('easy-content-manager/v1', '/claim/submit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'submit_claim'],
            'permission_callback' => [$this, 'submit_permissions_check'],
            'args'                => [
                'post_id' => [
                    'type'        => 'integer',
                    'required'    => true,
                    'description' => __('The ID of the post being claimed.', 'easy-content-manager'),
                    'validate_callback' => function ( $value ) {
                                                return is_numeric( $value );
                                            },
                ],
                'proof_content' => [
                    'type'        => 'string',
                    'required'    => true,
                    'description' => __('Proof or reason for the claim.', 'easy-content-manager'),
                ],
                'name' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __('Name of the claimant (for guests).', 'easy-content-manager'),
                ],
                'email' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __('Email of the claimant (for guests).', 'easy-content-manager'),
                    'format'      => 'email',
                ],
                'phone' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __('Phone number of the claimant (for guests).', 'easy-content-manager'),
                ],
            ],
        ]);

        register_rest_route('easy-content-manager/v1', '/claims', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_claims'],
            'permission_callback' => [$this, 'admin_permissions_check'],
            'args'                => [
                'status' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __('Claim status to filter by (pending, approved, rejected, all).', 'easy-content-manager'),
                    'enum'        => ['pending', 'approved', 'rejected', 'all'],
                ],
                'search' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __('Search by name, email, or post_id.', 'easy-content-manager'),
                ],
                'page' => [
                    'type'        => 'integer',
                    'required'    => false,
                    'default'     => 1,
                    'description' => __('Page number for pagination.', 'easy-content-manager'),
                ],
                'per_page' => [
                    'type'        => 'integer',
                    'required'    => false,
                    'default'     => 10,
                    'description' => __('Number of results per page.', 'easy-content-manager'),
                ],
            ],
        ]);
        
        register_rest_route('easy-content-manager/v1', '/claims/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_claim'],
                'permission_callback' => [$this, 'admin_permissions_check'],
                'args'                => [
                    'id' => [
                        'type'        => 'integer',
                        'required'    => true,
                        'description' => __('Claim ID to update.', 'easy-content-manager'),
                    ],
                    'status' => [
                        'type'        => 'string',
                        'required'    => true,
                        'description' => __('New claim status (pending, approved or rejected).', 'easy-content-manager'),
                        'enum'        => ['approved', 'rejected', 'pending'],
                    ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_claim'],
                'permission_callback' => [$this, 'admin_permissions_check'],
                'args'                => [
                    'id' => [
                        'type'        => 'integer',
                        'required'    => true,
                        'description' => __('Claim ID to delete.', 'easy-content-manager'),
                    ],
                ],
            ],
        ]);
    }

    public function admin_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    public function is_claim_enabled_for_this_post_type( $post_type ) {
        if ( ! Helper::is_addon_enabled_for_post_type( 'claim', $post_type ) ) {
            return false;
        }

        return true;
    }

    /**
     * Public endpoint, but require a valid REST nonce (blocks blind CSRF) and
     * throttle by IP to prevent automated abuse of the claim/account-creation flow.
     */
    public function submit_permissions_check( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'Invalid or missing security token.', 'easy-content-manager' ), [ 'status' => 403 ] );
        }

        if ( Helper::rate_limit_exceeded( 'ecm_claim_rl_', 5, MINUTE_IN_SECONDS * 10 ) ) {
            return new WP_Error( 'rest_too_many_requests', __( 'Too many claim attempts. Please try again later.', 'easy-content-manager' ), [ 'status' => 429 ] );
        }

        return true;
    }

    public function submit_claim( $request ) {
        global $wpdb;

        $post_id = absint( $request->get_param( 'post_id' ) );
        $proof   = wp_kses_post( (string) $request->get_param( 'proof_content' ) );
        $name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
        $email   = sanitize_email( (string) $request->get_param( 'email' ) );
        $phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );

        if( ! $this->is_claim_enabled_for_this_post_type(get_post_type($post_id)) ) {
            return new WP_Error( 'claim_not_enabled', __( 'Claiming is not enabled for this post type.', 'easy-content-manager' ), [ 'status' => 403 ] );
        }

        $table = $wpdb->prefix . ECM_CLAIM_TABLE;

        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE post_id = %d AND status = 'approved'", $post_id ) );


        if ( ! $post_id || ! $proof ) {
            return new WP_Error( 'missing_params', __( 'Missing required parameters.', 'easy-content-manager' ), [ 'status' => 400 ] );
        }

        if ( $existing ) {
             return new WP_Error( 'already_claimed', __( 'This listing has already been claimed.', 'easy-content-manager' ), [ 'status' => 400 ] );
        }

        $user_id = get_current_user_id();

        // Handle guest
        if ( ! is_user_logged_in() ) {
            // Guest submissions create a WordPress account, so only allow them when
            // the site itself permits open registration. Otherwise require login.
            if ( ! get_option( 'users_can_register' ) ) {
                return new WP_Error( 'login_required', __( 'Please log in to submit a claim.', 'easy-content-manager' ), [ 'status' => 401 ] );
            }

            if ( ! $name || ! $email || ! $phone ) {
                return new WP_Error( 'missing_guest_info', __( 'Name, email and phone are required.', 'easy-content-manager' ), [ 'status' => 400 ] );
            }

            if ( ! is_email( $email ) ) {
                return new WP_Error( 'invalid_email', __( 'A valid email address is required.', 'easy-content-manager' ), [ 'status' => 400 ] );
            }

            if ( email_exists( $email ) ) {
                return new WP_Error( 'email_exists', __( 'This email is already registered. Please login to claim.', 'easy-content-manager' ), [ 'status' => 400 ] );
            }

            // Create user
            $user_pass = wp_generate_password();
            $user_id = wp_create_user( $email, $user_pass, $email );

            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }

            // Update user meta/details
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $name,
                'first_name' => $name
            ]);
            update_user_meta( $user_id, 'phone_number', $phone );
        } else {
            $user = get_userdata( $user_id );
            $name = $user->display_name;
            $email = $user->user_email;
        }

        $result = $wpdb->insert( $table, [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'proof_content' => $proof,
            'status' => 'pending',
            'created_at' => current_time( 'mysql' )
        ] );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Could not save claim.', 'easy-content-manager' ), [ 'status' => 500 ] );
        }

        return [ 'success' => true, 'message' => __( 'Claim submitted successfully! We will review it shortly.', 'easy-content-manager' ) ];
    }

    public function get_claims( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
        
        $params = $request->get_params();
        $status = isset( $params['status'] ) && $params['status'] !== 'all' ? $params['status'] : '';
        $search = isset( $params['search'] ) ? sanitize_text_field( $params['search'] ) : '';
        $page = isset( $params['page'] ) ? intval( $params['page'] ) : 1;
        $per_page = isset( $params['per_page'] ) ? intval( $params['per_page'] ) : 10;
        $offset = ( $page - 1 ) * $per_page;

        $conditions = [];
        $values = [];

        if ( $status ) {
            $conditions[] = "status = %s";
            $values[] = $status;
        }

        if ( $search ) {
            // Search in name, email, or post_id
            $conditions[] = "(name LIKE %s OR email LIKE %s OR post_id LIKE %s)";
            $values[] = '%' . $wpdb->esc_like( $search ) . '%';
            $values[] = '%' . $wpdb->esc_like( $search ) . '%';
            $values[] = '%' . $wpdb->esc_like( $search ) . '%';
        }

        $where = '';
        if ( ! empty( $conditions ) ) {
            $where = 'WHERE ' . implode( ' AND ', $conditions );
        }

        // Count query
        $count_sql = "SELECT COUNT(*) FROM $table $where";
        if ( ! empty( $values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $values );
        }
        $total_items = $wpdb->get_var( $count_sql );
        $total_pages = ceil( $total_items / $per_page );

        // Data query
        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset; // Offset is last
        
        $sql = $wpdb->prepare( $sql, $values );

        $results = $wpdb->get_results( $sql );

        foreach ($results as $result) {
            $user = get_user_by('email', $result->email);
            $result->avatar = get_avatar_url( $user ? $user->ID : $result->email );
        }

        $response = new \WP_REST_Response( $results );
        $response->header( 'X-WP-Total', (int) $total_items );
        $response->header( 'X-WP-TotalPages', (int) $total_pages );

        return $response;
    }

    public function update_claim( $request ) {
        global $wpdb;
        $claim_id = (int) $request->get_param('id');
        $status   = sanitize_text_field( $request->get_param('status') );
    
        if ( ! in_array( $status, [ 'approved', 'rejected', 'pending' ] ) ) {
            return new WP_Error(
                'rest_invalid_status',
                __( 'Invalid status provided. Status must be "pending", "approved" or "rejected".', 'easy-content-manager' ),
                [ 'status' => 400 ]
            );
        }
    
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;
    
        $updated = $wpdb->update(
            $table,
            [ 'status' => $status ],
            [ 'id' => $claim_id ],
            [ '%s' ],
            [ '%d' ]
        );
    
        if ( $updated === false ) {
            return new WP_Error(
                'rest_update_failed',
                __( 'Failed to update claim status.', 'easy-content-manager' ),
                [ 'status' => 500 ]
            );
        }
    
        return new \WP_REST_Response(
            [
                'message'    => __( 'Claim status updated successfully.', 'easy-content-manager' ),
                'claim_id'   => $claim_id,
                'new_status' => $status
            ],
            200
        );
    }
    public function delete_claim( $request ) {
        global $wpdb;
        $id = $request->get_param( 'id' );
        $table = $wpdb->prefix . ECM_CLAIM_TABLE;

        $wpdb->delete( $table, [ 'id' => $id ] );
        return [ 'success' => true, 'message' => __( 'Claim deleted.', 'easy-content-manager' ) ];
    }
}
