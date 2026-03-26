<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMS_Gift_Articles_Credits {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_reset_credits' ) );
        add_action( 'pms_subscription_activated', array( $this, 'handle_subscription_activation' ), 10, 2 );
        add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'handle_wc_subscription_renewal' ) );
    }

    /**
     * Get credits for a specific user
     */
    public function get_user_credits( $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_credits_table();
        
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE user_id = %d", $user_id ) );
        
        if ( ! $row ) {
            return $this->initialize_user_credits( $user_id );
        }
        
        return $row;
    }

    /**
     * Initialize credits for a user
     */
    public function initialize_user_credits( $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_credits_table();
        
        $default_credits = get_option( 'pms_gift_articles_credits_per_month', 5 );
        $now = current_time( 'mysql' );
        
        $wpdb->insert( 
            $table, 
            array(
                'user_id'           => $user_id,
                'credits_remaining' => $default_credits,
                'credits_used'      => 0,
                'period_start'      => $now,
                'last_reset'        => $now
            )
        );
        
        return $this->get_user_credits( $user_id );
    }

    /**
     * Deduct 1 credit from user
     */
    public function deduct_credit( $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_credits_table();
        
        return $wpdb->query( $wpdb->prepare( 
            "UPDATE $table SET credits_remaining = credits_remaining - 1, credits_used = credits_used + 1 WHERE user_id = %d AND credits_remaining > 0",
            $user_id
        ) );
    }

    /**
     * Check if credits need to be reset (Monthly)
     */
    public function maybe_reset_credits() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id = get_current_user_id();
        
        // Only for active members
        if ( ! function_exists( 'pms_is_member' ) || ! pms_is_member( $user_id ) ) {
            return;
        }

        $credits = $this->get_user_credits( $user_id );
        if ( ! $credits ) return;

        $period_start = strtotime( $credits->period_start );
        $now = current_time( 'timestamp' );

        // If 30 days have passed
        if ( $now >= strtotime( '+30 days', $period_start ) ) {
            $this->reset_user_credits( $user_id );
        }
    }

    /**
     * Reset credits for a specific user
     */
    public function reset_user_credits( $user_id ) {
        global $wpdb;
        $table = PMS_Gift_Articles_Database::get_credits_table();
        
        $default_credits = get_option( 'pms_gift_articles_credits_per_month', 5 );
        $now = current_time( 'mysql' );

        $wpdb->update(
            $table,
            array(
                'credits_remaining' => $default_credits,
                'period_start'      => $now,
                'last_reset'        => $now
            ),
            array( 'user_id' => $user_id )
        );
    }

    /**
     * Handle PMS subscription activation
     */
    public function handle_subscription_activation( $user_id, $subscription_id ) {
        $this->reset_user_credits( $user_id );
    }

    /**
     * Handle WC Subscription renewal
     */
    public function handle_wc_subscription_renewal( $subscription ) {
        $user_id = $subscription->get_user_id();
        if ( $user_id ) {
            $this->reset_user_credits( $user_id );
        }
    }
}
