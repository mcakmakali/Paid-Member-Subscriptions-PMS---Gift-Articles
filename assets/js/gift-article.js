jQuery(document).ready(function($) {

    // Function to build button text
    function updateButtonText(btn, creditsRemaining) {
        var text = pms_gift_article_vars.i18n.button_text;
        if (creditsRemaining !== undefined && creditsRemaining !== null) {
            text += ' (' + pms_gift_article_vars.i18n.kalan_label + ' ' + creditsRemaining + '/' + pms_gift_article_vars.i18n.total_credits + ')';
        }
        btn.find('.pms-gift-btn-text').text(text);
    }

    // Open Modal and Generate Link - Using delegation to handle all buttons
    $(document).on('click', '.pms-gift-article-btn', function(e) {
        // If it's a link (like in the footer), let it work normally unless it's the specific generate button
        if ($(this).attr('href') && $(this).attr('href') !== '#') {
            return;
        }

        e.preventDefault();
        var giftBtn = $(this);
        var postId = giftBtn.data('post-id');
        
        if (!postId) return; // Not a generate button

        $('#pms-gift-modal-feedback').text('');
        $('#pms-gift-link-input').val('');
        
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
                    // Update ALL gift buttons on the page with new credit count
                    $('.pms-gift-article-btn').each(function() {
                        if ($(this).data('post-id')) {
                            updateButtonText($(this), response.data.credits_remaining);
                        }
                    });
                    
                    $('#pms-gift-link-input').val(response.data.link);
                    if (response.data.credits_remaining !== undefined) {
                        $('#pms-gift-remaining-info').text(pms_gift_article_vars.i18n.remaining.replace('%d', response.data.credits_remaining));
                    }
                    
                    // Show modal using class for better reliability with !important CSS
                    $('#pms-gift-modal').addClass('pms-active');
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

    // Close Modal
    $(document).on('click', '.pms-gift-modal-close', function() {
        $('#pms-gift-modal').removeClass('pms-active');
    });

    // Close Modal when clicking outside
    $(document).on('click', function(event) {
        var modal = $('#pms-gift-modal');
        if (modal.hasClass('pms-active') && $(event.target).is(modal)) {
            modal.removeClass('pms-active');
        }
    });

    // Copy to Clipboard
    $(document).on('click', '#pms-gift-copy-btn', function() {
        var linkInput = $('#pms-gift-link-input');
        linkInput.select();
        document.execCommand('copy');
        
        var feedback = $('#pms-gift-modal-feedback');
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
