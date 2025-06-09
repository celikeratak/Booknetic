// Custom Page Icon Picker Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    // Initialize the custom page icon picker
    function initCustomPageIconPicker() {
        // Custom page related icons - focused on content and UI elements
        const customPageIcons = [
            // Content and document icons
            'fas fa-file', 'fas fa-file-alt', 'fas fa-file-word', 'fas fa-file-pdf',
            'fas fa-file-image', 'fas fa-file-video', 'fas fa-file-audio', 'fas fa-file-code',
            'fas fa-file-contract', 'fas fa-file-invoice', 'fas fa-file-signature',
            
            // UI and interaction icons
            'fas fa-pen', 'fas fa-pencil-alt', 'fas fa-edit', 'fas fa-highlighter',
            'fas fa-marker', 'fas fa-paint-brush', 'fas fa-magic', 'fas fa-wand-magic-sparkles',
            
            // Layout and design icons
            'fas fa-columns', 'fas fa-table', 'fas fa-th', 'fas fa-th-large',
            'fas fa-th-list', 'fas fa-list', 'fas fa-list-ul', 'fas fa-list-ol',
            'fas fa-paragraph', 'fas fa-indent', 'fas fa-outdent', 'fas fa-align-left',
            'fas fa-align-center', 'fas fa-align-right', 'fas fa-align-justify',
            
            // Content type icons
            'fas fa-heading', 'fas fa-text-height', 'fas fa-text-width', 'fas fa-font',
            'fas fa-bold', 'fas fa-italic', 'fas fa-underline', 'fas fa-link',
            'fas fa-image', 'fas fa-photo-video', 'fas fa-camera', 'fas fa-video',
            'fas fa-music', 'fas fa-headphones', 'fas fa-microphone', 'fas fa-podcast',
            
            // Functional icons
            'fas fa-search', 'fas fa-filter', 'fas fa-sort', 'fas fa-sort-alpha-down',
            'fas fa-sort-numeric-up', 'fas fa-calendar', 'fas fa-calendar-alt', 'fas fa-clock',
            'fas fa-map', 'fas fa-map-marker-alt', 'fas fa-compass', 'fas fa-directions',
            
            // UI element icons
            'fas fa-sliders-h', 'fas fa-toggle-on', 'fas fa-toggle-off', 'fas fa-cog',
            'fas fa-cogs', 'fas fa-tools', 'fas fa-wrench', 'fas fa-screwdriver',
            'fas fa-bars', 'fas fa-ellipsis-h', 'fas fa-ellipsis-v', 'fas fa-plus',
            'fas fa-minus', 'fas fa-times', 'fas fa-check', 'fas fa-exclamation',
            
            // Informational icons
            'fas fa-info-circle', 'fas fa-question-circle', 'fas fa-exclamation-circle',
            'fas fa-exclamation-triangle', 'fas fa-bell', 'fas fa-bullhorn', 'fas fa-rss',
            
            // Topic specific icons
            'fas fa-book', 'fas fa-bookmark', 'fas fa-graduation-cap', 'fas fa-award',
            'fas fa-certificate', 'fas fa-trophy', 'fas fa-medal', 'fas fa-crown',
            'fas fa-lightbulb', 'fas fa-brain', 'fas fa-atom', 'fas fa-microscope',
            'fas fa-flask', 'fas fa-vial', 'fas fa-dna', 'fas fa-pills',
            
            // Business and commerce icons
            'fas fa-briefcase', 'fas fa-chart-line', 'fas fa-chart-bar', 'fas fa-chart-pie',
            'fas fa-chart-area', 'fas fa-coins', 'fas fa-dollar-sign', 'fas fa-euro-sign',
            'fas fa-credit-card', 'fas fa-receipt', 'fas fa-shopping-cart', 'fas fa-store',
            
            // Communication icons
            'fas fa-comments', 'fas fa-comment-dots', 'fas fa-comment-alt', 'fas fa-envelope',
            'fas fa-envelope-open', 'fas fa-paper-plane', 'fas fa-reply', 'fas fa-share',
            'fas fa-phone', 'fas fa-mobile-alt', 'fas fa-headset', 'fas fa-microphone-alt'
        ];
        
        const $iconGrid = $('#customPageIconGrid');
        $iconGrid.empty();
        
        // Populate icon grid with custom page icons
        customPageIcons.forEach(function(icon) {
            const $iconItem = $('<div class="col mb-3 text-center icon-item"></div>');
            $iconItem.append('<div class="p-3 border rounded icon-box"><i class="' + icon + ' fa-2x"></i><div class="mt-2 small">' + icon + '</div></div>');
            $iconItem.data('icon', icon);
            $iconGrid.append($iconItem);
        });
    }
    
    // Initialize the custom page icon picker
    initCustomPageIconPicker();
    
    // Handle icon selection
    $(document).on('click', '#customPageIconGrid .icon-item', function() {
        const icon = $(this).data('icon');
        
        // Update the input and preview
        $('#icon').val(icon);
        $('#livechat-icon-preview').attr('class', icon + ' text-primary');
        
        // Close modal
        $('#customPageIconPickerModal').modal('hide');
    });
    
    // Handle icon search
    $('#customPageIconSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('#customPageIconGrid .icon-item').each(function() {
            const icon = $(this).data('icon');
            if (icon.toLowerCase().includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
