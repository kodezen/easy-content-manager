<?php
namespace ECMReactions\Ajax;

use EasyContentManager\Classes\AbstractAjaxHandler;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ReactionHandler extends AbstractAjaxHandler {

    public function __construct() {

        $this->actions = [
            'submit_reaction' => [
                'callback'             => [ $this, 'process_reaction' ],
                'fields'               => [
                    'post_id' => 'absint',
                    'reaction' => 'string',
                ],
                'only_logged_in' => true,
            ],
        ];

        $this->dispatch_actions();
    }

    public function process_reaction( array $payload ) {

        $post_id  = absint( $payload['post_id'] ?? 0 );
        $reaction = sanitize_key( $payload['reaction'] ?? '' );

        if ( ! $post_id || ! $reaction ) {
            wp_send_json_error([
                'message' => __( 'Invalid post or reaction.', 'easy-content-manager' ),
            ]);
        }

        if ( ! Helper::is_addon_enabled_for_post_type( 'reaction', get_post_type( $post_id ) ) ) {
            wp_send_json_error([
                'message' => __( 'Reactions are not enabled for this post type.', 'easy-content-manager' ),
            ]);
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error([
                'message' => __( 'You must be logged in to react.', 'easy-content-manager' ),
            ]);
        }

        // Reacting to a post you cannot even read (a draft, a private post) is not
        // something the UI ever offers — but the endpoint took any ID.
        if ( ! current_user_can( 'read_post', $post_id ) ) {
            wp_send_json_error([
                'message' => __( 'You do not have permission to react to this item.', 'easy-content-manager' ),
            ]);
        }

        // The reaction key is stored verbatim as a post-meta array key, so it has
        // to be one of the configured reactions. Without this any logged-in user
        // could write unbounded arbitrary keys into _ecm_reactions and, since this
        // release, into their own _ecm_user_reactions as well.
        $reaction_data = $this->get_reaction_data( $reaction );

        if ( ! $reaction_data ) {
            wp_send_json_error([
                'message' => __( 'Unknown reaction.', 'easy-content-manager' ),
            ]);
        }

        $user_id   = get_current_user_id();

        $reactions = get_post_meta( $post_id, '_ecm_reactions', true );
        $reactions = is_array( $reactions ) ? $reactions : [];

        $user_reactions = get_user_meta( $user_id, '_ecm_user_reactions', true );
        $user_reactions = is_array( $user_reactions ) ? $user_reactions : [];

        $current_reaction = null;
        $toggled          = 'added';

        foreach ( $reactions as $type => $users ) {
            if ( in_array( $user_id, $users, true ) ) {
                $current_reaction = $type;
                $reactions[$type] = array_values(
                    array_diff( $users, [ $user_id ] )
                );
                if ( empty( $reactions[$type] ) ) {
                    unset( $reactions[$type] );
                }
                break;
            }
        }

        if ( $current_reaction && isset( $user_reactions[ $post_id ] ) ) {
            unset( $user_reactions[ $post_id ] );
        }

        if ( $current_reaction === $reaction ) {
            $toggled = 'removed';
        } else {
            $reactions[ $reaction ] = $reactions[ $reaction ] ?? [];
            $reactions[ $reaction ][] = $user_id;
            $reactions[ $reaction ]   = array_unique( $reactions[ $reaction ] );
            
            $user_reactions[ $post_id ] = $reaction;
        }

        update_post_meta( $post_id, '_ecm_reactions', $reactions );
        update_user_meta( $user_id, '_ecm_user_reactions', $user_reactions );

        wp_send_json_success([
            'status'            => $toggled,
            'reaction'          => $reaction,
            'previous_reaction' => $current_reaction,
            'reactions'         => $reactions,
            'reaction_data'     => $reaction_data,
        ]);
    }

    /**
     * The configured reaction matching this key, or null when it isn't one.
     *
     * @param string $reaction
     * @return object|null
     */
    private function get_reaction_data( string $reaction ) {
        $settings = Helper::get_settings( 'reaction_addon', [] );
        $configured = isset( $settings->reactions ) ? (array) $settings->reactions : [];

        foreach ( $configured as $data ) {
            $value = is_object( $data ) ? ( $data->value ?? null ) : ( $data['value'] ?? null );

            if ( $value === $reaction ) {
                return (object) $data;
            }
        }

        return null;
    }
}
