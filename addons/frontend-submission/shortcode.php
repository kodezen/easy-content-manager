<?php

namespace ECMFrontendSubmission;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Shortcode {

    private static $shortcode_data = [];

    public function __construct()
    {
        add_shortcode('ecm_frontend_submission', [$this, 'render_frontend_submission_shortcode']);
    }

    public function render_frontend_submission_shortcode($atts) {
        $atts = shortcode_atts([
            'show_form' => true,
            'show_list' => true,
            'post_type' => 'post',
        ], $atts, 'ecm_frontend_submission');

        $config = [
            'showForm' => filter_var($atts['show_form'], FILTER_VALIDATE_BOOLEAN),
            'showList' => filter_var($atts['show_list'], FILTER_VALIDATE_BOOLEAN),
            'postType' => sanitize_text_field($atts['post_type']),
        ];

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template('addons/frontend-submission/app.php', [
            'config' => $config,
        ]);
        return ob_get_clean();
    }
}