<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_pms_generate_gift_link', array( $this, 'generate_gift_link' ) );
    }

    /**
     * AJAX handler to generate gift link
     */
    public function generate_gift_link() {
        check_ajax_referer( 'pms_generate_gift_link_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Oturum açmanız gerekiyor.', 'pms-gift-articles' ) ) );
        }

        $user_id = get_current_user_id();
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Geçersiz makale.', 'pms-gift-articles' ) ) );
        }

        // Check if user is active member
        if ( ! function_exists( 'pms_is_member' ) || ! pms_is_member( $user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Bu özellik sadece aktif aboneler içindir.', 'pms-gift-articles' ) ) );
        }

        $credits_obj = PMS_Gift_Articles_Credits::get_instance()->get_user_credits( $user_id );
        
        if ( ! $credits_obj || $credits_obj->credits_remaining <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Bu ayki hediye limitinize ulaştınız.', 'pms-gift-articles' ) ) );
        }

        // Generate token
        $token = PMS_Gift_Articles_Tokens::get_instance()->generate_token( $post_id, $user_id );

        if ( $token ) {
            // Deduct credit
            PMS_Gift_Articles_Credits::get_instance()->deduct_credit( $user_id );
            
            $updated_credits = PMS_Gift_Articles_Credits::get_instance()->get_user_credits( $user_id );
            $gift_link = add_query_arg( 'gift_article', $token, get_permalink( $post_id ) );

            wp_send_json_success( array(
                'link'              => $gift_link,
                'credits_remaining' => $updated_credits->credits_remaining,
                'message'           => __( 'Link başarıyla oluşturuldu.', 'pms-gift-articles' )
            ) );
        }

        wp_send_json_error( array( 'message' => __( 'Link oluşturulamadı.', 'pms-gift-articles' ) ) );
    }
}
