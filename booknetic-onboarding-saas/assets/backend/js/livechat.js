// Live Chat Page Settings Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Save Live Chat Page
    $('#saveLivechatPage').on('click', function() {
        var formData = {
            action: 'booknetic_contact_us_p_save_livechat_page_settings',
            _wpnonce: nonce,
            title: $('#livechat_title').val(),
            subtitle: $('#livechat_subtitle').val(),
            embed_code: $('#livechat_embed_code').val(),
            icon: $('#icon').val()
        };

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                // Show loading indicator
                $('#saveLivechatPage').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>' + booknetic.__('Saving...'));
                // Hide any previous messages
                $('#livechat-error-message, #livechat-success-message').hide();
            },
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.status) {
                        $('#livechat-success-message').text(response.message || booknetic.__('Livechat page settings saved successfully!')).show();
                        booknetic.toast(booknetic.__('Saved successfully!'), 'success');
                    } else {
                        $('#livechat-error-message').text(response.error || booknetic.__('Failed to save livechat page settings')).show();
                        booknetic.toast(response.error || booknetic.__('Failed to save'), 'error');
                    }
                } catch (e) {
                    $('#livechat-error-message').text(booknetic.__('Invalid response from server')).show();
                    booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                    console.error('Error parsing response:', e);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                $('#livechat-error-message').text(booknetic.__('Failed to process request')).show();
                booknetic.toast(booknetic.__('Failed to process request'), 'error');
                console.error('AJAX error:', status, error);
            },
            complete: function() {
                // Reset button state
                $('#saveLivechatPage').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>' + booknetic.__('Save Changes'));
            }
        });
    });
    
    // Load Live Chat Page Settings
    function loadLivechatPageSettings() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_get_livechat_page_settings',
                _wpnonce: nonce
            },
            beforeSend: function() {
                // Show loading indicator
                $('#livechatPageForm').addClass('loading');
            },
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.status) {
                        const settings = response.data || {};
                        
                        // Set form values
                        $('#livechat_title').val(settings.title || '');
                        $('#livechat_subtitle').val(settings.subtitle || '');
                        $('#livechat_embed_code').val(settings.embed_code || '');
                        $('#icon').val(settings.icon || 'fas fa-comment');
                        
                        // Update icon preview
                        $('#livechat-icon-preview').attr('class', settings.icon + ' text-primary');
                    } else {
                        booknetic.toast(response.error || booknetic.__('Failed to load livechat page settings'), 'error');
                    }
                } catch (e) {
                    booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                }
            },
            error: function() {
                booknetic.toast(booknetic.__('Failed to load livechat page settings'), 'error');
            },
            complete: function() {
                // Hide loading indicator
                $('#livechatPageForm').removeClass('loading');
            }
        });
    }
    
    // Load settings on page load
    loadLivechatPageSettings();
});
