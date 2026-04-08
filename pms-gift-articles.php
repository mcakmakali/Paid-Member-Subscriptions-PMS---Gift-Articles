<?php
/**
 * Plugin Name: PMS Gift Articles
 * Plugin URI:
 * Description: Allows subscribers to gift articles to non-subscribers via unique links. Integrates with Paid Member Subscriptions.
 * Version: 1.0.9
 * Author: Mehmet Ali ÇAKMAK
 * Author URI: https://mehmetalicakmak.me
 * Text Domain: pms-gift-articles
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: paid-member-subscriptions
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PMS_GIFT_ARTICLES_VERSION', '1.0.9' );
define( 'PMS_GIFT_ARTICLES_PATH', plugin_dir_path( __FILE__ ) );
define( 'PMS_GIFT_ARTICLES_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class PMS_Gift_Articles {

    /**
     * Instance of this class.
     *
     * @var PMS_Gift_Articles
     */
    private static $instance = null;

    /**
     * Get instance of this class.
     *
     * @return PMS_Gift_Articles
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'check_dependency' ) );
    }

    /**
     * Check if Paid Member Subscriptions is active.
     */
    public function check_dependency() {
        if ( ! $this->is_pms_active() ) {
            if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'pms_missing_notice' ) );
                
                // Deactivate the plugin if PMS is missing
                add_action( 'admin_init', array( $this, 'deactivate_self' ) );
            }
            return;
        }

        $this->includes();
        $this->init_hooks();
    }

    /**
     * Verify if Paid Member Subscriptions core functions exist.
     */
    private function is_pms_active() {
        return defined( 'PMS_VERSION' ) || function_exists( 'pms_is_member' );
    }

    /**
     * Deactivate this plugin.
     */
    public function deactivate_self() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

    /**
     * Show admin notice if dependency is missing.
     */
    public function pms_missing_notice() {
        $message = sprintf(
            __( '<strong>PMS Gift Articles</strong> eklentisinin çalışması için <strong>Paid Member Subscriptions</strong> eklentisinin aktif olması gerekmektedir.', 'pms-gift-articles' )
        );
        printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
    }

    /**
     * Include necessary files.
     */
    private function includes() {
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-database.php';
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-credits.php';
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-tokens.php';
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-frontend.php';
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-gift-access.php';
        require_once PMS_GIFT_ARTICLES_PATH . 'includes/class-ajax.php';

        if ( is_admin() ) {
            require_once PMS_GIFT_ARTICLES_PATH . 'admin/class-admin.php';
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        
        // Initialize logic classes
        PMS_Gift_Articles_Database::get_instance();
        PMS_Gift_Articles_Credits::get_instance();
        PMS_Gift_Articles_Tokens::get_instance();
        PMS_Gift_Articles_Frontend::get_instance();
        PMS_Gift_Articles_Gift_Access::get_instance();
        PMS_Gift_Articles_Ajax::get_instance();

        if ( is_admin() ) {
            PMS_Gift_Articles_Admin::get_instance();
        }

        register_activation_hook( __FILE__, array( 'PMS_Gift_Articles_Database', 'activate' ) );
    }

    /**
     * Load text domain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'pms-gift-articles', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
}

// Initialize the plugin
PMS_Gift_Articles::get_instance();
