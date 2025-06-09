// Color Settings Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Color Settings Functionality
    function loadColorSettings() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_get_color_settings',
                _wpnonce: nonce
            },
            beforeSend: function() {
                // Show loading indicator
                $('#saveColorSettings').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Loading...'));
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        const colors = response.data || {};
                        
                        // Set input values
                        $('#primaryColor').val(colors.primary_color || '#4050B5');
                        $('#secondaryColor').val(colors.secondary_color || '#6C757D');
                        
                        // Set color picker values
                        $('#primaryColorPicker').val(colors.primary_color || '#4050B5');
                        $('#secondaryColorPicker').val(colors.secondary_color || '#6C757D');
                        
                        // Update preview
                        updateColorPreview();
                    } else {
                        // Handle error with message from server
                        booknetic.toast(booknetic.__('Failed to load color settings'), 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                }
            },
            error: function() {
                // Handle AJAX errors
                booknetic.toast(booknetic.__('Failed to load color settings'), 'error');
            },
            complete: function() {
                // Hide loading indicator
                $('#saveColorSettings').prop('disabled', false).html('<i class="fas fa-save" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Save Changes'));
            }
        });
    }
    
    // Save color settings
    $('#saveColorSettings').on('click', function() {
        const primaryColor = $('#primaryColor').val();
        const secondaryColor = $('#secondaryColor').val();

        // Show loading state
        const $saveBtn = $(this);
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Saving...'));

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_save_color_settings',
                _wpnonce: nonce,
                primary_color: primaryColor,
                secondary_color: secondaryColor
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    // Check status and handle success
                    if (response.status) {
                        booknetic.toast(booknetic.__('Color settings saved successfully!'), 'success');
                        // Update color preview
                        updateColorPreview();
                    } else {
                        booknetic.toast(response.error || booknetic.__('Failed to save color settings.'), 'error');
                    }
                } catch (e) {
                    booknetic.toast(booknetic.__('Invalid response from server.'), 'error');
                }
            },
            error: function() {
                booknetic.toast(booknetic.__('Failed to process request.'), 'error');
            },
            complete: function() {
                $saveBtn.prop('disabled', false).html('<i class="fas fa-save" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Save Changes'));
            }
        });
    });
    
    // Update preview when color inputs change
    function updateColorPreview() {
        const primaryColor = $('#primaryColor').val();
        const secondaryColor = $('#secondaryColor').val();
        
        // Update primary button preview
        $('#previewPrimaryBtn').css({
            'background-color': primaryColor,
            'border-color': primaryColor
        });
        
        // Update secondary button preview
        $('#previewSecondaryBtn').css({
            'background-color': secondaryColor,
            'border-color': secondaryColor
        });
        
        // Update Category Card Preview
        $('.preview-category-card i').css({
            'color': primaryColor
        });
        $('.preview-category-card h2').css({
            'color': primaryColor
        });
        $('.preview-category-card .topic-count').css({
            'color': '#ffffff',
            'background-color': secondaryColor
        });
        $('.preview-category-card:hover').css({
            'border': `1px solid ${primaryColor}`
        });

        // Update Topic Item Preview
        $('.preview-topic-item .topic-header h2').css({
            'color': primaryColor
        });
        $('.preview-topic-item .topic-views-count i').css({
            'color': primaryColor
        });
        $('.preview-topic-item:hover').css({
            'border-color': primaryColor
        });

        // Update preview titles
        $('.preview-title').css({
            'color': primaryColor,
            'border-bottom': '1px solid ' + secondaryColor
        });
    }
    
    // Make updateColorPreview available globally
    window.updateColorPreview = updateColorPreview;

    // Sync color input with color picker
    $('#primaryColorPicker').on('input', function() {
        $('#primaryColor').val($(this).val());
        updateColorPreview();
    });

    $('#secondaryColorPicker').on('input', function() {
        $('#secondaryColor').val($(this).val());
        updateColorPreview();
    });

    $('#primaryColor').on('input', function() {
        $('#primaryColorPicker').val($(this).val());
        updateColorPreview();
    });

    $('#secondaryColor').on('input', function() {
        $('#secondaryColorPicker').val($(this).val());
        updateColorPreview();
    });
    
    // Load color settings on page load
    $(document).ready(function() {
        loadColorSettings();
    });
    
    // Initialize the color preview with default values
    updateColorPreview();
});
