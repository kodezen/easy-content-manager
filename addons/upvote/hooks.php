<?php
namespace ECMUpvote;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Hooks {
    
    private $post_types = [];
    
    public function __construct() {
        add_action( 'init', [ $this, 'initialize_post_types' ], 20 );
        add_action( 'rest_api_init', [ $this, 'register_upvote_rest_hooks' ], 20 );
    }
    
    public function initialize_post_types() {
        $settings = Helper::get_settings( 'upvote_addon' );
        
        if ( empty( $settings ) ) {
            return;
        }
        
        $shortcode_settings = isset( $settings->shortcode_settings )
            ? (array) $settings->shortcode_settings
            : [];
        
        $enable_specific = ! empty( $shortcode_settings['enable_specific_post_types'] );
        
        if ( $enable_specific ) {
            $this->post_types = isset( $shortcode_settings['post_types'] ) 
                ? (array) $shortcode_settings['post_types'] 
                : [];
        } else {
            $this->post_types = get_post_types(
                [
                    'public'       => true,
                    'show_in_rest' => true,
                ],
                'names'
            );
        }
    }
    
    public function register_upvote_rest_hooks() {
        if ( empty( $this->post_types ) ) {
            return;
        }
        
        foreach ( $this->post_types as $post_type ) {
            $post_type = strtolower( trim( $post_type ) );
            
            // Add filter for this post type
            add_filter(
                "rest_prepare_{$post_type}",
                [ $this, 'inject_upvote_count' ],
                10,
                3
            );
        }
    }

    public function inject_upvote_count( $response, $post, $request ) {
        if ( ! $response instanceof \WP_REST_Response ) {
            return $response;
        }
        
        $data = $response->get_data();
        
        $upvote_count = get_post_meta( $post->ID, 'ecm_upvote_count', true );
        $data['upvote_count'] = $upvote_count ? (int) $upvote_count : 0;
        
        $response->set_data( $data );
        
        return $response;
    }
}