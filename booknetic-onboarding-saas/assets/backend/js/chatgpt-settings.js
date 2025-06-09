(function($) {
    "use strict";

    // Get AJAX URL and nonce
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    // Use help_center_nonce directly as it's defined in the adminp-social-media.php file



    // Initialize Select2 for the model dropdown
    $('#chatgptModel').select2({
        placeholder: 'Select a model',
        allowClear: true,
        width: '100%'
    });

    // ChatGPT API Settings
    $('#saveChatGPTSettings').on('click', function() {
        const apiKey = $('#chatgptApiKey').val();
        const model = $('#chatgptModel').val();
        const enabled = $('#enableChatGPT').is(':checked') ? '1' : '0';
        
        // Show loading indicator
        $(this).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Saving...'));
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_save_chatgpt_settings',
                _wpnonce: help_center_nonce,
                api_key: apiKey,
                model: model,
                enabled: enabled
            },
            beforeSend: function() {
                // Already showing loading indicator
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        booknetic.toast(booknetic.__( 'Settings saved successfully' ), 'success');
                    } else {
                        booknetic.toast(response.error || booknetic.__( 'Failed to save settings' ), 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    booknetic.toast(booknetic.__( 'Invalid response from server' ), 'error');
                }
            },
            error: function() {
                // Handle AJAX errors
                booknetic.toast(booknetic.__( 'Failed to save settings' ), 'error');
            },
            complete: function() {
                // Reset button text
                $('#saveChatGPTSettings').html('<i class="fas fa-save"></i> ' + booknetic.__( 'Save Settings' ));
            }
        });
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
})(jQuery);
