<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Database {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Any DB related initialization
    }

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_tokens = $wpdb->prefix . 'gift_article_tokens';
        $sql_tokens = "CREATE TABLE $table_tokens (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            token varchar(64) NOT NULL,
            post_id bigint(20) NOT NULL,
            gifter_user_id bigint(20) NOT NULL,
            created_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            is_active tinyint(1) DEFAULT 1 NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY token (token),
            KEY post_id (post_id),
            KEY gifter_user_id (gifter_user_id)
        ) $charset_collate;";

        $table_credits = $wpdb->prefix . 'gift_article_credits';
        $sql_credits = "CREATE TABLE $table_credits (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            credits_remaining int(11) DEFAULT 5 NOT NULL,
            credits_used int(11) DEFAULT 0 NOT NULL,
            period_start datetime NOT NULL,
            last_reset datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_tokens );
        dbDelta( $sql_credits );

        add_option( 'pms_gift_articles_db_version', '1.0.0' );
        
        // Default settings
        add_option( 'pms_gift_articles_credits_per_month', 5 );
        add_option( 'pms_gift_articles_token_expiry_days', 30 );
        add_option( 'pms_gift_articles_enabled_post_types', array( 'post' ) );
    }

    /**
     * Get tokens table name
     */
    public static function get_tokens_table() {
        global $wpdb;
        return $wpdb->prefix . 'gift_article_tokens';
    }

    /**
     * Get credits table name
     */
    public static function get_credits_table() {
        global $wpdb;
        return $wpdb->prefix . 'gift_article_credits';
    }
}
