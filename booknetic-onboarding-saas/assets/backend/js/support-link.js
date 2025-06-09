/**
 * Support Link Functionality
 * 
 * This file handles the support link form submission and loading
 * for the Booknetic Help Center plugin.
 */
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    /**
     * Handle support link form submission
     */
    $('#saveSupportLink').on('click', function() {
        // Get form values and ensure they're valid strings
        let label = $('#support_text').val();
        label = (label !== null && label !== undefined) ? label.trim() : 'Support';
        
        let url = $('#support_url').val();
        url = (url !== null && url !== undefined) ? url.trim() : '';
        
        // Only validate URL if it's not empty
        if (url !== '' && !isValidUrl(url)) {
            booknetic.toast(booknetic_help_i18n.please_enter_valid_url, 'warning');
            return;
        }
        
        
        // Prepare form data
        const formData = {
            action: 'booknetic_contact_us_p_save_support_link',
            _wpnonce: nonce,
            id: 1, // Always use ID 1 for the support link
            label: label,
            url: url,
            active: 1 // Always active
        };
        
        // Show loading state
        const $saveBtn = $(this);
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.saving);
        
        // Send AJAX request
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        // Show success message
                        booknetic.toast(booknetic_help_i18n.support_link_saved_successfully, 'success');
                        
                        // Update support link preview
                        updateSupportLinkPreview(label, url, formData.active);
                    } else {
                        // Handle error with message from server
                        booknetic.toast(response.error || booknetic_help_i18n.failed_to_save_support_link, 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    console.error('Error parsing response:', e);
                    booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error('AJAX error:', status, error);
                booknetic.toast(booknetic_help_i18n.failed_to_process_request, 'error');
            },
            complete: function() {
                // Reset button state
                $saveBtn.prop('disabled', false).html('<i class="fas fa-save" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Save Changes'));
            }
        });
    });
    
    /**
     * Load support link data from the server
     */
    function loadSupportLink() {
        // Show loading indicator if needed
        const $form = $('#livechatPageForm');
        if ($form.length) {
            $form.addClass('loading');
        }
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_get_support_link',
                _wpnonce: nonce
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status && response.data) {
                        // Update support link form
                        const link = response.data;
                        
                        // Handle different data formats
                        if (typeof link === 'string') {
                            try {
                                const parsedLink = JSON.parse(link);
                                updateFormFields(parsedLink);
                            } catch (parseError) {
                                console.error('Error parsing link data:', parseError);
                            }
                        } else {
                            updateFormFields(link);
                        }
                    } else {
                        // Handle error with message from server
                        console.warn('Failed to load support link:', response);
                    }
                } catch (e) {
                    // Handle parsing errors
                    console.error('Error parsing response:', e);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error('AJAX error:', status, error);
            },
            complete: function() {
                // Remove loading state
                if ($form.length) {
                    $form.removeClass('loading');
                }
            }
        });
    }
    
    /**
     * Update form fields with support link data
     */
    function updateFormFields(link) {
        if (!link) return;
        
        // Update form fields
        if (link.label) {
            $('#support_text').val(link.label);
        }
        
        if (link.url) {
            $('#support_url').val(link.url);
        }
        
        // Update preview if it exists
        updateSupportLinkPreview(link.label, link.url, link.active);
    }
    
    // Helper function to validate URL
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Helper function to update support link preview
     * @param {string} label - The label text
     * @param {string} url - The URL to link to
     * @param {number|boolean} active - Whether the link is active
     */
    function updateSupportLinkPreview(label, url, active) {
        // Update the preview if it exists on the page
        if ($('#support-link-preview').length) {
            // Ensure label is a string
            label = label || 'Support';
            
            // Ensure URL is a string
            url = url || '#';
            
            // Update elements
            $('#support-link-preview').text(label);
            $('#support-link-preview').attr('href', url);
            
            // Show/hide based on active status if needed
            if (typeof active !== 'undefined') {
                if (active) {
                    $('#support-link-preview').show();
                } else {
                    $('#support-link-preview').hide();
                }
            }
        }
    }
    
    // Load support link on page load
    loadSupportLink();
});
