<?php

namespace ECMClaim;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Database {
    public static function init(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$prefix = $wpdb->prefix;
		$charset_collate = $wpdb->get_charset_collate();
        $self = new self();
        $self->create_claims_table( $prefix, $charset_collate );
	}

    private function create_claims_table( $prefix, $charset_collate ): void {
        $table_name = $prefix . ECM_CLAIM_TABLE;

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            proof_content longtext DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }
}