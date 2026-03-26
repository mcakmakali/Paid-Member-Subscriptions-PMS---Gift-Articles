<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Frontend {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'the_content', array( $this, 'inject_gift_button' ), 5 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_modal' ) );
        add_shortcode( 'pms_gift_button', array( $this, 'render_shortcode' ) );
    }

    /**
     * Get button HTML
     */
    public function get_button_html( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user_id = get_current_user_id();
        
        // Check if user is active PMS member
        if ( ! function_exists( 'pms_is_member' ) || ! pms_is_member( $user_id ) ) {
            return '';
        }

        $credits_obj = PMS_Gift_Articles_Credits::get_instance()->get_user_credits( $user_id );
        
        if ( ! $credits_obj || $credits_obj->credits_remaining <= 0 ) {
            return '';
        }

        $button_text = get_option( 'pms_gift_articles_button_text', __( 'Hediye Et', 'pms-gift-articles' ) );

        $button_html = '<div class="pms-gift-article-wrapper">';
        $button_html .= sprintf(
            '<button id="pms-gift-article-btn" class="pms-gift-article-btn" data-post-id="%d">%s (%s %d/%d)</button>',
            $post_id,
            esc_html( $button_text ),
            __( 'kalan', 'pms-gift-articles' ),
            $credits_obj->credits_remaining,
            get_option( 'pms_gift_articles_credits_per_month', 5 )
        );
        $button_html .= '</div>';

        return $button_html;
    }

    /**
     * Shortcode callback
     */
    public function render_shortcode() {
        return $this->get_button_html();
    }

    /**
     * Inject "Hediye Et" button into content
     */
    public function inject_gift_button( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }

        $post_type = get_post_type();
        $enabled_types = get_option( 'pms_gift_articles_enabled_post_types', array( 'post' ) );
        
        if ( ! in_array( $post_type, $enabled_types ) ) {
            return $content;
        }

        // To prevent double button if shortcode is used
        if ( has_shortcode( $content, 'pms_gift_button' ) ) {
            return $content;
        }

        $button_html = $this->get_button_html();

        return $button_html . $content;
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        if ( ! is_singular() && ! has_shortcode( get_post()->post_content, 'pms_gift_button' ) ) return;

        wp_enqueue_style( 'pms-gift-article-css', PMS_GIFT_ARTICLES_URL . 'assets/css/gift-article.css', array(), PMS_GIFT_ARTICLES_VERSION );
        wp_enqueue_script( 'pms-gift-article-js', PMS_GIFT_ARTICLES_URL . 'assets/js/gift-article.js', array( 'jquery' ), PMS_GIFT_ARTICLES_VERSION, true );

        wp_localize_script( 'pms-gift-article-js', 'pms_gift_article_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'pms_generate_gift_link_nonce' ),
            'i18n'     => array(
                'copy_success' => __( 'Link kopyalandı! Paylaşmaya hazır.', 'pms-gift-articles' ),
                'error'        => __( 'Bir hata oluştu, lütfen tekrar deneyin.', 'pms-gift-articles' ),
                'remaining'    => __( 'Bu ay %d hediye hakkınız kaldı.', 'pms-gift-articles' ),
                'button_text'  => get_option( 'pms_gift_articles_button_text', __( 'Hediye Et', 'pms-gift-articles' ) )
            )
        ) );
    }

    /**
     * Render modal HTML in footer
     */
    public function render_modal() {
        if ( ! is_user_logged_in() ) return;

        ?>
        <div id="pms-gift-modal" class="pms-gift-modal">
            <div class="pms-gift-modal-content">
                <span class="pms-gift-modal-close">&times;</span>
                <h3><?php _e( 'Makaleyi Hediye Et', 'pms-gift-articles' ); ?></h3>
                <p><?php _e( 'Bu makaleyi hediye etmek için aşağıdaki linki kopyalayın:', 'pms-gift-articles' ); ?></p>
                <div class="pms-gift-link-container">
                    <input type="text" id="pms-gift-link-input" readonly>
                    <button id="pms-gift-copy-btn"><?php _e( 'Kopyala', 'pms-gift-articles' ); ?></button>
                </div>
                <div id="pms-gift-modal-feedback"></div>
                <p id="pms-gift-remaining-info"></p>
            </div>
        </div>
        <?php
    }
}
