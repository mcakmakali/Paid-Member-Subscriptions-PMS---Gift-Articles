<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Gift_Access {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'pms_content_is_restricted', array( $this, 'bypass_pms_restriction' ), 10, 2 );
        add_action( 'wp_head', array( $this, 'check_invalid_token_notice' ) );
    }

    /**
     * Bypass PMS restriction if a valid gift token is present
     */
    public function bypass_pms_restriction( $is_restricted, $post_id ) {
        if ( isset( $_GET['gift_article'] ) ) {
            $token = sanitize_text_field( $_GET['gift_article'] );
            
            if ( PMS_Gift_Articles_Tokens::validate( $token, $post_id ) ) {
                return false; // Bypass restriction
            }
        }

        return $is_restricted;
    }

    /**
     * Show a notice if the gift token is invalid
     */
    public function check_invalid_token_notice() {
        if ( is_singular() && isset( $_GET['gift_article'] ) ) {
            $token = sanitize_text_field( $_GET['gift_article'] );
            $post_id = get_the_ID();

            if ( ! PMS_Gift_Articles_Tokens::validate( $token, $post_id ) ) {
                add_action( 'wp_footer', function() {
                    echo '<div class="pms-gift-invalid-notice">' . __( 'Bu hediye linki geçersiz veya süresi dolmuş.', 'pms-gift-articles' ) . '</div>';
                    echo '<style>.pms-gift-invalid-notice { background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; margin-bottom: 20px; text-align: center; font-weight: bold; border-radius: 4px; }</style>';
                } );
            }
        }
    }
}
