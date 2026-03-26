<?php

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gift_article_tokens" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gift_article_credits" );

// Delete options
delete_option( 'pms_gift_articles_db_version' );
delete_option( 'pms_gift_articles_credits_per_month' );
delete_option( 'pms_gift_articles_token_expiry_days' );
delete_option( 'pms_gift_articles_enabled_post_types' );

// Delete user meta if any (though we used custom tables)
