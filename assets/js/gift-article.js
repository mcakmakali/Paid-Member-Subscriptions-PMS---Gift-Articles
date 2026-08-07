jQuery(document).ready(function($) {

    // Function to build button text
    function updateButtonText(btn, creditsRemaining) {
        var text = pms_gift_article_vars.i18n.button_text;
        if (creditsRemaining !== undefined && creditsRemaining !== null) {
            text += ' (' + pms_gift_article_vars.i18n.kalan_label + ' ' + creditsRemaining + '/' + pms_gift_article_vars.i18n.total_credits + ')';
        }
        btn.find('.pms-gift-btn-text').text(text);
    }

    // Generate Link - Using delegation to handle all buttons
    $(document).on('click', '.pms-gift-article-btn', function(e) {
        // If it's a link (like in the sticky footer), let it work normally
        if ($(this).attr('href') && $(this).attr('href') !== '#') {
            return;
        }

        e.preventDefault();
        var giftBtn = $(this);
        var postId = giftBtn.data('post-id');
        
        if (!postId) return; // Not a generate button

        var box = giftBtn.closest('.pms-gift-article-box');
        var feedback = box.find('.pms-gift-action-feedback');
        var linkInput = box.find('.pms-gift-link-input');
        
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
                giftBtn.prop('disabled', true).find('.pms-gift-btn-text').text(pms_gift_article_vars.i18n.generating_text);
            },
            success: function(response) {
                giftBtn.prop('disabled', false);
                
                if (response.success) {
                    
                    // Set the input value
                    linkInput.val(response.data.link);
                    
                    // Update remaining info
                    if (response.data.credits_remaining !== undefined) {
                        var remainingText = pms_gift_article_vars.i18n.remaining.replace('%d', response.data.credits_remaining);
                        box.find('.pms-gift-remaining-count').text(remainingText);
                        
                        // Also update other buttons on page if any are still visible
                        $('.pms-gift-article-btn').each(function() {
                            if ($(this).data('post-id')) {
                                updateButtonText($(this), response.data.credits_remaining);
                            }
                        });
                    }

                    // Hide button wrapper, show inline link wrapper
                    box.find('.pms-gift-btn-wrapper').hide();
                    box.find('.pms-gift-inline-wrapper').fadeIn(300);
                    
                } else {
                    updateButtonText(giftBtn);
                    alert(response.data.message || pms_gift_article_vars.i18n.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('PMS Gift AJAX Error:', status, error);
                giftBtn.prop('disabled', false);
                updateButtonText(giftBtn);
                alert(pms_gift_article_vars.i18n.error);
            }
        });
    });

    // Copy to Clipboard (Inline)
    $(document).on('click', '.pms-gift-copy-btn', function() {
        var box = $(this).closest('.pms-gift-article-box');
        var linkInput = box.find('.pms-gift-link-input');
        
        linkInput.select();
        document.execCommand('copy');
        
        var feedback = box.find('.pms-gift-action-feedback');
        feedback.text(pms_gift_article_vars.i18n.copy_success).show();
        
        setTimeout(function() {
            feedback.fadeOut(function() {
                $(this).text('');
            });
        }, 3000);
    });

    // Sticky Footer Toggle (Expand/Collapse)
    $(document).on('click', '.pms-footer-toggle', function() {
        var footer = $('#pms-gift-sticky-footer');
        footer.toggleClass('pms-expanded pms-collapsed');
    });
});
