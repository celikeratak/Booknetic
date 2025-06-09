// Copyright Text Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Handle copyright text form submission
    $('#saveCopyright').on('click', function() {
        var formData = {
            action: 'booknetic_contact_us_p_save_copyright_text',
            _wpnonce: nonce,
            copyright_text: $('#copyright_text').val()
        };
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                // Clear previous messages
                $('#error-message, #success-message').hide();
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        booknetic.toast('Copyright text updated successfully!', 'success');
                    } else {
                        booknetic.toast(response.error || 'Failed to update copyright text.', 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    booknetic.toast('Invalid response from server.', 'error');
                }
            },
            error: function() {
                // Handle AJAX errors
                booknetic.toast('Failed to process request.', 'error');
            },
            complete: function() {
                // Reset button state
                $('#saveCopyright').prop('disabled', false).html('Save Changes');
            }
        });
    });
});
