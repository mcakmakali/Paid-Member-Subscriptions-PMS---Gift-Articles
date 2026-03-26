<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Admin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_ajax_pms_admin_reset_credits', array( $this, 'ajax_reset_user_credits' ) );
    }

    /**
     * Add settings page under Settings > Gift Articles
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Gift Articles Settings', 'pms-gift-articles' ),
            __( 'Gift Articles', 'pms-gift-articles' ),
            'manage_options',
            'pms-gift-articles',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'pms_gift_articles_settings', 'pms_gift_articles_credits_per_month' );
        register_setting( 'pms_gift_articles_settings', 'pms_gift_articles_token_expiry_days' );
        register_setting( 'pms_gift_articles_settings', 'pms_gift_articles_enabled_post_types' );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once PMS_GIFT_ARTICLES_PATH . 'admin/admin-page.php';
    }

    /**
     * AJAX handler to manually reset user credits
     */
    public function ajax_reset_user_credits() {
        check_ajax_referer( 'pms_admin_reset_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Yetkiniz yok.', 'pms-gift-articles' ) ) );
        }

        $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Geçersiz kullanıcı.', 'pms-gift-articles' ) ) );
        }

        PMS_Gift_Articles_Credits::get_instance()->reset_user_credits( $user_id );

        wp_send_json_success( array( 'message' => __( 'Kullanıcı hakları başarıyla sıfırlandı.', 'pms-gift-articles' ) ) );
    }

    /**
     * Get recent gift activity
     */
    public function get_recent_activity( $limit = 20 ) {
        global $wpdb;
        $tokens_table = PMS_Gift_Articles_Database::get_tokens_table();
        $users_table = $wpdb->users;
        $posts_table = $wpdb->posts;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, u.user_login, p.post_title 
             FROM $tokens_table t
             JOIN $users_table u ON t.gifter_user_id = u.ID
             JOIN $posts_table p ON t.post_id = p.ID
             ORDER BY t.created_at DESC 
             LIMIT %d",
            $limit
        ) );
    }
}
