<?php
namespace ECMReactions\Shortcodes;

use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ReactionsShortcode {

    public function __construct() {
        add_shortcode( 'ecm_reaction', [ $this, 'render_shortcode' ] );
        add_shortcode( 'ecm_reaction_count', [ $this, 'render_reaction_count' ] );
        add_shortcode( 'ecm_reaction_list', [ $this, 'render_reaction_list' ] );
    }

    public function render_shortcode( $atts = [] ) {

        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'labels'  => false,
        ], $atts, 'ecm_reaction');

        $post_id = intval($atts['post_id']);
        if ( ! $post_id ) {
            return '';
        }

        // Fetch stored reactions
        $reactions_data = get_post_meta($post_id, '_ecm_reactions', true);
        $reactions_data = is_array($reactions_data) ? $reactions_data : [];

        $current_user_id = get_current_user_id();
        $user_reaction   = null;

        if ($current_user_id) {
            foreach ($reactions_data as $reaction => $users) {
                if (in_array($current_user_id, $users, true)) {
                    $user_reaction = $reaction;
                    break;
                }
            }
        }

        $reaction_settings = Helper::get_settings('reaction_addon', []);
        $available_reactions = $reaction_settings->reactions;

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template(
            'addons/reactions/reaction-button.php',
            [
                'post_id'            => $post_id,
                'available_reactions'=> $available_reactions,
                'reactions_data'     => $reactions_data,
                'user_reaction'      => $user_reaction,
                'labels'             => (bool) $atts['labels'],
            ]
        );

        return ob_get_clean();
    }

    public function render_reaction_count( $atts = [] ) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'show_label' => 'false',
            'label_single' => esc_html__( 'reaction', 'easy-content-manager' ),
            'label_plural' => esc_html__( 'reactions', 'easy-content-manager' ),
            'wrapper_class' => 'ecm-reaction-count',
        ], $atts );

        $post_id = intval( $atts['post_id'] );
        if ( ! $post_id ) return '';

        $reactions_data = get_post_meta( $post_id, '_ecm_reactions', true );
        $reactions_data = is_array( $reactions_data ) ? $reactions_data : [];

        $count = 0;
        foreach ( $reactions_data as $users ) {
            $count += is_array( $users ) ? count( $users ) : 0;
        }

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/reactions/reaction-count.php', [
            'count' => $count,
            'wrapper_class' => $atts['wrapper_class'],
            'show_label' => 'true' === $atts['show_label'],
            'label_single' => $atts['label_single'],
            'label_plural' => $atts['label_plural'],
        ]);
        return ob_get_clean();
    }

    public function render_reaction_list( $atts = [] ) {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Please login to view reacted posts.', 'easy-content-manager' );
        }

        $atts = shortcode_atts([
            'user_id' => get_current_user_id(),
            'not_found_text' => esc_html__( 'No reacted posts found.', 'easy-content-manager' ),
        ], $atts );

        $user_id = intval( $atts['user_id'] );

        $user_reactions = get_user_meta( $user_id, '_ecm_user_reactions', true );
        $user_reactions = is_array( $user_reactions ) ? $user_reactions : [];

        $posts_with_reactions = [];
        
        foreach ( $user_reactions as $post_id => $reaction ) {
            $posts_with_reactions[] = [
                'post_id' => $post_id,
                'reaction' => $reaction
            ];
        }

        do_action( 'ecm_shortcode_loaded');

        ob_start();
        Helper::get_template( 'addons/reactions/reaction-list.php', [
            'posts_with_reactions' => $posts_with_reactions,
            'not_found_text' => $atts['not_found_text'],
        ]);
        return ob_get_clean();
    }
}
