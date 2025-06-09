// Icon Picker Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    // Icon picker functionality
    let lastFocusedInput = null;
    
    // Set focus tracking for all icon inputs
    $('.icon-input, input[name="icon"], #icon, #livechat_icon').on('focus', function() {
        lastFocusedInput = this;
        
        // Remove previous focus class
        $('.icon-input-focused').removeClass('icon-input-focused');
        
        // Add focus class to current input
        $(this).addClass('icon-input-focused');
    });
    
    // When the browse button next to livechat icon is clicked, focus the input first
    $('[data-target="#iconPickerModal"]').on('click', function() {
        // Find the closest input field
        const $input = $(this).closest('.input-group').find('input');
        if ($input.length) {
            // Focus the input to set it as the last focused input
            $input.focus();
        }
    });
    
    // Update icon preview when input changes
    $(document).on('input', '.icon-input, input[name="icon"], #icon, #livechat_icon', function() {
        const iconClass = $(this).val();
        const isLivechatIcon = this.id === 'livechat_icon';
        const isSocialMediaIcon = this.id === 'icon' && $('#socialLinkModal').is(':visible');
        
        if (isLivechatIcon || (this.id === 'icon' && $('#livechatPageForm').is(':visible'))) {
            $('#livechat-icon-preview').attr('class', iconClass + ' text-primary');
        } else if (isSocialMediaIcon || this.id === 'icon') {
            // For social media icons
            const previewIcon = $(this).closest('.input-group').find('.preview-icon');
            if (previewIcon.length) {
                previewIcon.attr('class', iconClass + ' text-primary');
            }
        } else {
            // For other icons
            const previewIcon = $(this).closest('.input-group').find('.preview-icon');
            if (previewIcon.length) {
                previewIcon.attr('class', iconClass + ' text-muted');
            }
        }
    });
    
    // Handle icon selection from the picker
    $(document).on('click', '.icon-option, .icon-item', function() {
        // Get icon class - handle both .icon-option and .icon-item elements
        let iconClass;
        if ($(this).hasClass('icon-option')) {
            iconClass = $(this).find('i').attr('class');
        } else if ($(this).hasClass('icon-item')) {
            iconClass = $(this).data('icon');
        }
        
        if (lastFocusedInput && iconClass) {
            // Update input value
            $(lastFocusedInput).val(iconClass);
            
            // Manually trigger input event to update preview
            $(lastFocusedInput).trigger('input');
            
            // Close modals
            $('#iconPickerModal, #customPageIconPickerModal').modal('hide');
        }
    });
    
    // Icon search functionality
    $('#iconSearch').on('input', function() {
        const searchTerm = this.value.toLowerCase();
        $('.icon-option').each(function() {
            const iconClass = $(this).find('i').attr('class').toLowerCase() || '';
            $(this).toggle(iconClass.includes(searchTerm));
        });
    });
});
