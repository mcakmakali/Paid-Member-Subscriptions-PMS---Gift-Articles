<?php
/**
 * Plugin Name: PMS Gift Articles
 * Plugin URI:
 * Description: Allows subscribers to gift articles to non-subscribers via unique links. Integrates with Paid Member Subscriptions.
 * Version: 1.0.0
 * Author: Mehmet Ali ÇAKMAK
 * Text Domain: pms-gift-articles
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PMS_GIFT_ARTICLES_VERSION', '1.0.0' );
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
        $this->includes();
        $this->init_hooks();
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
