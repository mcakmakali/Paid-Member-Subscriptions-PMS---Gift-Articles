<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Tokens {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Token logic
    }

    /**
     * Generate a unique token for a post
     */
    public function generate_token( $post_id, $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_tokens_table();
        
        $token = wp_generate_password( 32, false );
        $created_at = current_time( 'mysql' );
        $expiry_days = get_option( 'pms_gift_articles_token_expiry_days', 30 );
        $expires_at = date( 'Y-m-d H:i:s', strtotime( "+$expiry_days days", current_time( 'timestamp' ) ) );

        $result = $wpdb->insert(
            $table,
            array(
                'token'           => $token,
                'post_id'         => $post_id,
                'gifter_user_id'  => $user_id,
                'created_at'      => $created_at,
                'expires_at'      => $expires_at,
                'is_active'       => 1
            )
        );

        if ( $result ) {
            return $token;
        }

        return false;
    }

    /**
     * Get existing valid token for a user and post
     */
    public function get_existing_token( $post_id, $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_tokens_table();
        $now = current_time( 'mysql' );

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT token FROM $table WHERE post_id = %d AND gifter_user_id = %d AND is_active = 1 AND expires_at > %s ORDER BY created_at DESC LIMIT 1",
            $post_id,
            $user_id,
            $now
        ) );
    }

    /**
     * Validate a token
     */
    public static function validate( $token, $post_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_tokens_table();
        
        $row = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table WHERE token = %s AND post_id = %d AND is_active = 1", 
            $token, 
            $post_id 
        ) );

        if ( ! $row ) {
            return false;
        }

        $expires_at = strtotime( $row->expires_at );
        $now = current_time( 'timestamp' );

        if ( $now > $expires_at ) {
            return false;
        }

        return true;
    }
}
