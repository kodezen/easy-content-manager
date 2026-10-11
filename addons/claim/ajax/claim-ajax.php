<?php
namespace ECMClaim\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ClaimAjax extends AbstractAjaxHandler {
    public function __construct() {
        $this->actions = [
            'get_claim_form_template' => [
                'callback'              => [ $this, 'get_template' ],
                'allow_visitor_action'  => true,
            ],
        ];

        $this->dispatch_actions();
    }

    public function get_template( $payload ) {
        ob_start();
        Helper::get_template('addons/claim/claim-form.php');
        $template = ob_get_clean();

        return wp_send_json_success( [
            'template' => $template,
        ] );
    }
}
