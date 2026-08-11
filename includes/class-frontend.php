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
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_gift_footer' ) );
        add_shortcode( 'pms_gift_button', array( $this, 'render_shortcode' ) );
        add_filter( 'the_content', array( $this, 'inject_gift_button' ) );
    }

    /**
     * Get button HTML
     */
    public function get_button_html( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // 1. Giriş yapmamış kullanıcılar zaten göremez
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user_id = get_current_user_id();

        // 1b. Sadece aktif abonelere gösterilsin (AJAX tarafındaki kontrolle aynı, class-ajax.php).
        if ( ! function_exists( 'pms_is_member' ) || ! pms_is_member( $user_id ) ) {
            return '';
        }

        /**
         * PMS RESTRICTION CHECK (Pure PMS Functions)
         */
        if ( ! function_exists( 'pms_is_post_restricted' ) ) {
            return '';
        }

        global $user_ID, $pms_is_post_restricted_arr;

        // A. Mevcut kullanıcının (abone/admin) erişimi var mı?
        // pms_is_post_restricted false dönerse kullanıcının erişimi VAR demektir.
        $user_has_access = ! pms_is_post_restricted( $post_id );

        if ( ! $user_has_access ) {
            return ''; // Kullanıcı kendisi okuyamıyorsa hediye edemez.
        }

        // B. Bu yazı Premium mu? (Ziyaretçiler için kısıtlı mı?)
        // PMS'e "Ziyaretçi (ID: 0) bu yazıyı okuyabilir mi?" diye soruyoruz.
        $original_user_id = $user_ID;
        $user_ID = 0; 
        
        // Önbelleği (cache) bu kontrol için geçici olarak temizlemeliyiz
        $cached_val = isset( $pms_is_post_restricted_arr[$post_id] ) ? $pms_is_post_restricted_arr[$post_id] : null;
        unset( $pms_is_post_restricted_arr[$post_id] );

        $is_premium = pms_is_post_restricted( $post_id );

        // Global değerleri ve cache'i eski haline getiriyoruz
        $user_ID = $original_user_id;
        if ( $cached_val !== null ) {
            $pms_is_post_restricted_arr[$post_id] = $cached_val;
        } else {
            unset( $pms_is_post_restricted_arr[$post_id] );
        }

        // // Eğer yazı bir ziyaretçi için kısıtlı değilse (herkese açıksa), Premium değildir.
        // if ( ! $is_premium ) {
        //     return '';
        // }

        /**
         * Başarılı: Kullanıcı yetkili VE yazı premium.
         */

        $credits_obj = PMS_Gift_Articles_Credits::get_instance()->get_user_credits( $user_id );
        $total_credits = get_option( 'pms_gift_articles_credits_per_month', 5 );
        $remaining = $credits_obj ? $credits_obj->credits_remaining : $total_credits;

        $button_text   = get_option( 'pms_gift_articles_button_text', __( 'Hediye Et', 'pms-gift-articles' ) );
        $section_title = get_option( 'pms_gift_articles_section_title', __( 'Bu makaleyi hediye et', 'pms-gift-articles' ) );
        $section_desc  = get_option( 'pms_gift_articles_section_desc', __( 'Aboneliğinizle bu makaleyi sevdiklerinize hediye edebilirsiniz.', 'pms-gift-articles' ) );

        $existing_token = PMS_Gift_Articles_Tokens::get_instance()->get_existing_token( $post_id, $user_id );
        $gift_link = $existing_token ? add_query_arg( 'gift_article', $existing_token, get_permalink( $post_id ) ) : '';

        // Hak kalmadıysa "Hediye Et" butonu tıklanamaz olsun, ama kalan hak/yenilenme bilgisi yine de gösterilsin.
        $btn_display  = ( $existing_token || $remaining <= 0 ) ? 'style="display:none;"' : '';
        $link_display = $existing_token ? '' : 'style="display:none;"';

        $button_html = '<div class="pms-gift-article-box">';
        $button_html .= sprintf( '<h3 class="pms-gift-box-title">%s</h3>', esc_html( $section_title ) );
        $button_html .= sprintf( '<p class="pms-gift-box-desc">%s</p>', esc_html( $section_desc ) );
        
        $button_html .= '<div class="pms-gift-btn-wrapper" ' . $btn_display . '>';
        $button_html .= sprintf(
            '<button class="pms-gift-article-btn" data-post-id="%d">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pms-gift-icon"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                <span class="pms-gift-btn-text">%s (%s %d/%d)</span>
            </button>',
            $post_id,
            esc_html( $button_text ),
            __( 'kalan', 'pms-gift-articles' ),
            $remaining,
            $total_credits
        );
        $button_html .= '</div>';

        $button_html .= '<div class="pms-gift-inline-wrapper" ' . $link_display . '>';
        $button_html .= '<div class="pms-gift-link-container">';
        $button_html .= '<input type="text" class="pms-gift-link-input" value="' . esc_url( $gift_link ) . '" readonly>';
        $button_html .= '<button class="pms-gift-copy-btn">' . __( 'Kopyala', 'pms-gift-articles' ) . '</button>';
        $button_html .= '</div>';
        $button_html .= '<div class="pms-gift-action-feedback"></div>';
        $button_html .= '</div>';

        // Kalan hak / yenilenme bilgisi her zaman gösterilir, buton/link durumundan bağımsız.
        $remaining_text = sprintf( __( 'Bu ay %d hediye hakkınız kaldı.', 'pms-gift-articles' ), $remaining );
        $button_html   .= '<p class="pms-gift-remaining-info">';
        $button_html   .= '<span class="pms-gift-remaining-count">' . esc_html( $remaining_text ) . '</span>';

        if ( $credits_obj && ! empty( $credits_obj->period_start ) ) {
            $next_reset_ts = strtotime( '+30 days', strtotime( $credits_obj->period_start ) );
            $renewal_text  = sprintf( __( ' Haklarınız %s tarihinde yenilenecek.', 'pms-gift-articles' ), date_i18n( 'd F Y', $next_reset_ts ) );
            $button_html  .= esc_html( $renewal_text ) ;
        }

        $button_html .= '</p>';

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

        if ( has_shortcode( $content, 'pms_gift_button' ) ) {
            return $content;
        }

        $button_html = $this->get_button_html();

        return $content . $button_html;
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Giriş yapmamış veya aktif abone olmayan kullanıcıya buton hiç gösterilmediği için JS/nonce de yüklenmesin.
        if ( ! is_user_logged_in() ) return;

        if ( ! function_exists( 'pms_is_member' ) || ! pms_is_member( get_current_user_id() ) ) return;

        if ( ! is_singular() && ! has_shortcode( get_post()->post_content, 'pms_gift_button' ) ) return;

        wp_enqueue_style( 'pms-gift-article-css', PMS_GIFT_ARTICLES_URL . 'assets/css/gift-article.css', array(), PMS_GIFT_ARTICLES_VERSION );
        wp_enqueue_script( 'pms-gift-article-js', PMS_GIFT_ARTICLES_URL . 'assets/js/gift-article.js', array( 'jquery' ), PMS_GIFT_ARTICLES_VERSION, true );

        wp_localize_script( 'pms-gift-article-js', 'pms_gift_article_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'pms_generate_gift_link_nonce' ),
            'i18n'     => array(
                'copy_success'    => get_option( 'pms_gift_articles_copy_success_msg', __( 'Link kopyalandı! Paylaşmaya hazır.', 'pms-gift-articles' ) ),
                'error'           => __( 'Bir hata oluştu, lütfen tekrar deneyin.', 'pms-gift-articles' ),
                'remaining'       => __( 'Bu ay %d hediye hakkınız kaldı.', 'pms-gift-articles' ),
                'button_text'     => get_option( 'pms_gift_articles_button_text', __( 'Hediye Et', 'pms-gift-articles' ) ),
                'generating_text' => __( 'Link oluşturuluyor...', 'pms-gift-articles' ),
                'kalan_label'     => __( 'kalan', 'pms-gift-articles' ),
                'total_credits'   => get_option( 'pms_gift_articles_credits_per_month', 5 )
            )
        ) );
    }

    /**
     * Render gift sticky footer for visitors
     */
    public function render_gift_footer() {
        if ( ! isset( $_GET['gift_article'] ) ) return;

        $token = sanitize_text_field( $_GET['gift_article'] );
        $post_id = get_the_ID();

        if ( ! PMS_Gift_Articles_Tokens::validate( $token, $post_id ) ) return;

        $footer_title    = get_option( 'pms_gift_articles_footer_title', __( 'Never miss a story from MediaCat.', 'pms-gift-articles' ) );
        $footer_desc     = get_option( 'pms_gift_articles_footer_desc', __( 'This is your gift article. Get unlimited access to MediaCat.', 'pms-gift-articles' ) );
        $footer_btn_text = get_option( 'pms_gift_articles_footer_btn_text', __( 'ABONE OL', 'pms-gift-articles' ) );
        $footer_btn_url  = get_option( 'pms_gift_articles_footer_btn_url', '#' );
        $login_url       = do_shortcode( '[cognito_login_url]' );

        ?>
        <div id="pms-gift-sticky-footer" class="pms-gift-sticky-footer pms-expanded">
            <div class="pms-footer-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <div class="pms-footer-content-wrap">
                <div class="pms-footer-expanded-content">
                    <div class="pms-footer-left">
                        <h2 class="pms-footer-title" ><?php echo esc_html( $footer_title ); ?></h2>
                        <p class="pms-footer-desc">
                            <?php echo esc_html( $footer_desc ); ?>
                            <br>
                            <span>Zaten abone misiniz? <a href="<?php echo esc_url( $login_url ); ?>">Giriş yapın</a></span>
                        </p>
                    </div>
                    <div class="pms-footer-right">
                        <a href="<?php echo esc_url( $footer_btn_url ); ?>" class="pms-gift-article-btn pms-footer-btn"><?php echo esc_html( $footer_btn_text ); ?></a>
                    </div>
                </div>

                <div class="pms-footer-collapsed-content">
                    <div class="pms-footer-collapsed-left">
                         <a href="<?php echo esc_url( $login_url ); ?>" class="pms-footer-login-link">GİRİŞ YAP</a>
                    </div>
                    <div class="pms-footer-collapsed-right">
                        <a href="<?php echo esc_url( $footer_btn_url ); ?>" class="pms-gift-article-btn pms-footer-btn"><?php echo esc_html( $footer_btn_text ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
