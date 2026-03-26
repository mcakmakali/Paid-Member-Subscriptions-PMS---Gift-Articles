<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$credits_per_month = get_option( 'pms_gift_articles_credits_per_month', 5 );
$token_expiry_days = get_option( 'pms_gift_articles_token_expiry_days', 30 );
$enabled_post_types = get_option( 'pms_gift_articles_enabled_post_types', array( 'post' ) );
$all_post_types = get_post_types( array( 'public' => true ), 'objects' );

$activity = PMS_Gift_Articles_Admin::get_instance()->get_recent_activity();
?>

<div class="wrap">
    <h1><?php _e( 'Gift Articles Settings', 'pms-gift-articles' ); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields( 'pms_gift_articles_settings' ); ?>
        <?php do_settings_sections( 'pms_gift_articles_settings' ); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Global credits per month', 'pms-gift-articles' ); ?></th>
                <td><input type="number" name="pms_gift_articles_credits_per_month" value="<?php echo esc_attr( $credits_per_month ); ?>" /></td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e( 'Token expiry days', 'pms-gift-articles' ); ?></th>
                <td><input type="number" name="pms_gift_articles_token_expiry_days" value="<?php echo esc_attr( $token_expiry_days ); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e( 'Enabled Post Types', 'pms-gift-articles' ); ?></th>
                <td>
                    <?php foreach ( $all_post_types as $pt ) : ?>
                        <label>
                            <input type="checkbox" name="pms_gift_articles_enabled_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $enabled_post_types ) ); ?>>
                            <?php echo esc_html( $pt->label ); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr>

    <h2><?php _e( 'Recent Gift Activity', 'pms-gift-articles' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Gifter', 'pms-gift-articles' ); ?></th>
                <th><?php _e( 'Post', 'pms-gift-articles' ); ?></th>
                <th><?php _e( 'Date', 'pms-gift-articles' ); ?></th>
                <th><?php _e( 'Status', 'pms-gift-articles' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $activity ) : ?>
                <?php foreach ( $activity as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item->user_login ); ?></td>
                        <td><?php echo esc_html( $item->post_title ); ?></td>
                        <td><?php echo esc_html( $item->created_at ); ?></td>
                        <td>
                            <?php 
                            $is_expired = strtotime( $item->expires_at ) < current_time( 'timestamp' );
                            if ( ! $item->is_active ) {
                                _e( 'Cancelled', 'pms-gift-articles' );
                            } elseif ( $is_expired ) {
                                _e( 'Expired', 'pms-gift-articles' );
                            } else {
                                _e( 'Active', 'pms-gift-articles' );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4"><?php _e( 'No activity found.', 'pms-gift-articles' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <h2><?php _e( 'Manual Credit Reset', 'pms-gift-articles' ); ?></h2>
    <div class="pms-admin-reset-form">
        <input type="number" id="pms-reset-user-id" placeholder="<?php _e( 'User ID', 'pms-gift-articles' ); ?>">
        <button id="pms-admin-reset-btn" class="button"><?php _e( 'Reset Credits', 'pms-gift-articles' ); ?></button>
        <span id="pms-admin-reset-msg"></span>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#pms-admin-reset-btn').on('click', function() {
            var userId = $('#pms-reset-user-id').val();
            if (!userId) return;

            var data = {
                action: 'pms_admin_reset_credits',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce("pms_admin_reset_nonce"); ?>'
            };

            $('#pms-admin-reset-msg').text('Processing...');

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    $('#pms-admin-reset-msg').css('color', 'green').text(response.data.message);
                } else {
                    $('#pms-admin-reset-msg').css('color', 'red').text(response.data.message);
                }
            });
        });
    });
    </script>
</div>
