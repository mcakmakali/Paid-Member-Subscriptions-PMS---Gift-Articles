<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$credits_per_month  = get_option( 'pms_gift_articles_credits_per_month', 5 );
$token_expiry_days  = get_option( 'pms_gift_articles_token_expiry_days', 30 );
$button_text        = get_option( 'pms_gift_articles_button_text', __( 'Hediye Et', 'pms-gift-articles' ) );
$modal_title        = get_option( 'pms_gift_articles_modal_title', __( 'Makaleyi Hediye Et', 'pms-gift-articles' ) );
$modal_desc         = get_option( 'pms_gift_articles_modal_desc', __( 'Bu makaleyi hediye etmek için aşağıdaki linki kopyalayın:', 'pms-gift-articles' ) );
$copy_success_msg   = get_option( 'pms_gift_articles_copy_success_msg', __( 'Link kopyalandı! Paylaşmaya hazır.', 'pms-gift-articles' ) );

// Article End Section Texts
$section_title      = get_option( 'pms_gift_articles_section_title', __( 'Bu makaleyi hediye et', 'pms-gift-articles' ) );
$section_desc       = get_option( 'pms_gift_articles_section_desc', __( 'Aboneliğinizle bu makaleyi sevdiklerinize hediye edebilirsiniz.', 'pms-gift-articles' ) );

// Sticky Footer Section Texts
$footer_title       = get_option( 'pms_gift_articles_footer_title', __( 'Never miss a story from MediaCat.', 'pms-gift-articles' ) );
$footer_desc        = get_option( 'pms_gift_articles_footer_desc', __( 'This is your gift article. Get unlimited access to MediaCat.', 'pms-gift-articles' ) );
$footer_btn_text    = get_option( 'pms_gift_articles_footer_btn_text', __( 'ABONE OL', 'pms-gift-articles' ) );
$footer_btn_url     = get_option( 'pms_gift_articles_footer_btn_url', '#' );

$enabled_post_types = get_option( 'pms_gift_articles_enabled_post_types', array( 'post' ) );
$all_post_types     = get_post_types( array( 'public' => true ), 'objects' );

$activity = PMS_Gift_Articles_Admin::get_instance()->get_recent_activity();
?>

<style>
    .pms-gift-admin-wrap {
        padding: 20px;
        background: #fff;
        border: 1px solid #ccd0d4;
        margin-top: 20px;
        max-width: 1000px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .pms-gift-admin-wrap h1 {
        margin-top: 0;
    }
    .pms-gift-admin-section {
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .pms-gift-admin-section:last-child {
        border-bottom: none;
    }
    .pms-admin-reset-form {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        display: inline-block;
    }
</style>

<div class="wrap pms-wrap pms-info-wrap cozmoslabs-wrap">
    
    <h1><?php _e( 'Gift Articles Settings', 'pms-gift-articles' ); ?></h1>

    <div class="pms-gift-admin-wrap">
        <!-- <div class="pms-gift-admin-section">
            <h2><?php _e( 'Shortcode', 'pms-gift-articles' ); ?></h2>
            <p><?php _e( 'Hediye et butonunu istediğiniz yere yerleştirmek için aşağıdaki shortcode\'u kullanın:', 'pms-gift-articles' ); ?></p>
            <code>[pms_gift_button]</code>
        </div> -->

        <form method="post" action="options.php" class="pms-gift-admin-section">
            <?php settings_fields( 'pms_gift_articles_settings' ); ?>
            
            <table class="form-table">
                <tr>
                    <th colspan="2"><h3><?php _e( 'Button & Modal Texts', 'pms-gift-articles' ); ?></h3></th>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Button Text', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_button_text" value="<?php echo esc_attr( $button_text ); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Modal Title', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_modal_title" value="<?php echo esc_attr( $modal_title ); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Modal Description', 'pms-gift-articles' ); ?></th>
                    <td><textarea name="pms_gift_articles_modal_desc" class="regular-text" rows="2"><?php echo esc_textarea( $modal_desc ); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Copy Success Message', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_copy_success_msg" value="<?php echo esc_attr( $copy_success_msg ); ?>" class="regular-text" /></td>
                </tr>

                <tr>
                    <th colspan="2"><h3><?php _e( 'Article End Section Texts', 'pms-gift-articles' ); ?></h3></th>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Section Title', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_section_title" value="<?php echo esc_attr( $section_title ); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Section Description', 'pms-gift-articles' ); ?></th>
                    <td><textarea name="pms_gift_articles_section_desc" class="regular-text" rows="2"><?php echo esc_textarea( $section_desc ); ?></textarea></td>
                </tr>

                <tr>
                    <th colspan="2"><h3><?php _e( 'Gift Visitor Sticky Footer', 'pms-gift-articles' ); ?></h3></th>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Footer Title', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_footer_title" value="<?php echo esc_attr( $footer_title ); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Footer Description', 'pms-gift-articles' ); ?></th>
                    <td><textarea name="pms_gift_articles_footer_desc" class="regular-text" rows="2"><?php echo esc_textarea( $footer_desc ); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Button Text', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_footer_btn_text" value="<?php echo esc_attr( $footer_btn_text ); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Button URL', 'pms-gift-articles' ); ?></th>
                    <td><input type="text" name="pms_gift_articles_footer_btn_url" value="<?php echo esc_attr( $footer_btn_url ); ?>" class="regular-text" /></td>
                </tr>

                <tr>
                    <th colspan="2"><h3><?php _e( 'Core Settings', 'pms-gift-articles' ); ?></h3></th>
                </tr>
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

        <div class="pms-gift-admin-section">
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
                                <td>
                                    <a href="<?php echo esc_url( get_edit_user_link( $item->gifter_user_id ) ); ?>">
                                        <?php echo esc_html( $item->user_login ); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( get_edit_post_link( $item->post_id ) ); ?>">
                                        <?php echo esc_html( $item->post_title ); ?>
                                    </a>
                                </td>
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
        </div>

        <div class="pms-gift-admin-section">
            <h2><?php _e( 'Manual Credit Reset', 'pms-gift-articles' ); ?></h2>
            <div class="pms-admin-reset-form">
                <input type="number" id="pms-reset-user-id" placeholder="<?php _e( 'User ID', 'pms-gift-articles' ); ?>">
                <button id="pms-admin-reset-btn" class="button"><?php _e( 'Reset Credits', 'pms-gift-articles' ); ?></button>
                <span id="pms-admin-reset-msg" style="margin-left: 10px;"></span>
            </div>
        </div>

        <div class="pms-gift-admin-footer" style="margin-top: 20px; text-align: right; font-style: italic; color: #777;">
            <p><?php printf( __( 'Developed by %s', 'pms-gift-articles' ), '<a href="https://mehmetalicakmak.me" target="_blank">Mehmet Ali ÇAKMAK</a>' ); ?></p>
        </div>
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
