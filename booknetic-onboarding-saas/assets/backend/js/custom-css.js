// Custom CSS Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Handle custom CSS form submission
    $('#saveCustomCss').on('click', function() {
        var formData = {
            action: 'booknetic_contact_us_p_save_custom_css',
            _wpnonce: nonce,
            custom_css: $('#custom_css').val()
        };
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Saving...'));
        
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
                        booknetic.toast(booknetic.__('Custom CSS updated successfully!'), 'success');
                    } else {
                        booknetic.toast(response.error || booknetic.__('Failed to update custom CSS.'), 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    booknetic.toast(booknetic.__('Invalid response from server.'), 'error');
                }
            },
            error: function() {
                // Handle AJAX errors
                booknetic.toast(booknetic.__('Failed to process request.'), 'error');
            },
            complete: function() {
                // Reset button state
                $('#saveCustomCss').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>' + booknetic.__('Save Changes'));
            }
        });
    });
});
