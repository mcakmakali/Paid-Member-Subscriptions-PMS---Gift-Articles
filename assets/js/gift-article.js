jQuery(document).ready(function($) {
    var modal = $('#pms-gift-modal');
    var closeBtn = $('.pms-gift-modal-close');
    var copyBtn = $('#pms-gift-copy-btn');
    var giftBtn = $('#pms-gift-article-btn');
    var linkInput = $('#pms-gift-link-input');
    var feedback = $('#pms-gift-modal-feedback');
    var remainingInfo = $('#pms-gift-remaining-info');

    // Open Modal and Generate Link
    giftBtn.on('click', function() {
        var postId = $(this).data('post-id');
        
        feedback.text('');
        linkInput.val('');
        
        $.ajax({
            url: pms_gift_article_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'pms_generate_gift_link',
                post_id: postId,
                nonce: pms_gift_article_vars.nonce
            },
            beforeSend: function() {
                giftBtn.prop('disabled', true).text('...');
            },
            success: function(response) {
                giftBtn.prop('disabled', false).text(pms_gift_article_vars.i18n.button_text);
                
                if (response.success) {
                    linkInput.val(response.data.link);
                    remainingInfo.text(pms_gift_article_vars.i18n.remaining.replace('%d', response.data.credits_remaining));
                    modal.show();
                    
                    // Update main button text if credits changed
                    var currentText = giftBtn.text();
                    // Basic update of remaining count in button if exists
                } else {
                    alert(response.data.message || pms_gift_article_vars.i18n.error);
                }
            },
            error: function() {
                giftBtn.prop('disabled', false).text('Hediye Et');
                alert(pms_gift_article_vars.i18n.error);
            }
        });
    });

    // Close Modal
    closeBtn.on('click', function() {
        modal.hide();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is(modal)) {
            modal.hide();
        }
    });

    // Copy to Clipboard
    copyBtn.on('click', function() {
        linkInput.select();
        document.execCommand('copy');
        
        feedback.text(pms_gift_article_vars.i18n.copy_success);
        
        setTimeout(function() {
            feedback.fadeOut(function() {
                $(this).text('').show();
            });
        }, 3000);
    });
});
