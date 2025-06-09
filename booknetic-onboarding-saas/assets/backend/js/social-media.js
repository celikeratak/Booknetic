// Social Media Links Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    // Helper function to validate URL format
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Function to update icon preview
    function updateIconPreview(iconClass) {
        $('.preview-icon').attr('class', iconClass + ' text-primary');
    }
    
    // Function to load social media links
    function loadSocialMediaLinks() {
        const $tableBody = $('#socialMediaTableBody');
        $tableBody.html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Loading...') + '</td></tr>');
        
        // Refresh the table by reloading the page
        // This is a simple approach that ensures all server-side data is fresh
        window.location.reload();
    }
    
    // Open modal for adding a new social link
    $('#addSocialLink').on('click', function() {
        // Reset form and set title for adding new link
        $('#socialLinkForm')[0].reset();
        $('#link_id').val('');
        $('#display_order').val(1); // Default display order
        $('.modal-title').text(booknetic.__('Add Social Media Link'));
        
        // Preview icon
        updateIconPreview('fas fa-share-alt');
        
        // Show modal
        $('#socialLinkModal').modal('show');
    });
    
    // Handle icon input changes to update preview
    $('#icon').on('input', function() {
        updateIconPreview($(this).val());
    });
    
    // Handle edit button click
    $(document).on('click', '.edit-social-link', function() {
        const id = $(this).data('id');
        const platform = $(this).data('platform');
        const icon = $(this).data('icon');
        const url = $(this).data('url');
        const displayOrder = $(this).data('display-order') || 1;
        
        // Populate form
        $('#link_id').val(id);
        $('#platform').val(platform);
        $('#icon').val(icon);
        $('#url').val(url);
        $('#display_order').val(displayOrder);
        
        // Update preview
        updateIconPreview(icon);
        
        // Set modal title
        $('.modal-title').text(booknetic.__('Edit Social Media Link'));
        
        // Show modal
        $('#socialLinkModal').modal('show');
    });
    
    // Handle delete button click
    $(document).on('click', '.delete-social-link', function() {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');
        
        if (confirm(booknetic.__('Are you sure you want to delete this social media link?'))) {
            // Show loading state
            $row.addClass('bg-light');
            $(this).html('<i class="fas fa-spinner fa-spin"></i>');
            
            // Send AJAX request
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_contact_us_p_delete_social_media',
                    _wpnonce: nonce,
                    id: id
                },
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Check status and handle success
                        if (response.status || response.success) {
                            // Show success message
                            booknetic.toast(booknetic.__('Social media link deleted successfully!'), 'success');
                            
                            // Remove row from table
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            // Handle error with message from server
                            const errorMsg = response.error || booknetic.__('Failed to delete social media link');
                            booknetic.toast(errorMsg, 'error');
                            
                            // Reset row
                            $row.removeClass('bg-light');
                            $row.find('.delete-social-link').html('<i class="fas fa-trash"></i>');
                        }
                    } catch (e) {
                        // Handle parsing errors
                        console.error('Error parsing response:', e);
                        booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                        
                        // Reset row
                        $row.removeClass('bg-light');
                        $row.find('.delete-social-link').html('<i class="fas fa-trash"></i>');
                    }
                },
                error: function(xhr) {
                    // Handle AJAX errors
                    console.error('AJAX error:', xhr);
                    booknetic.toast(booknetic.__('Failed to process request'), 'error');
                    
                    // Reset row
                    $row.removeClass('bg-light');
                    $row.find('.delete-social-link').html('<i class="fas fa-trash"></i>');
                }
            });
        }
    });
    
    // Handle social link form submission
    $('#saveSocialLink').on('click', function() {
        // Validate required fields
        const platform = $('#platform').val().trim();
        const url = $('#url').val().trim();
        const icon = $('#icon').val().trim();
        const id = $('#link_id').val();
        const displayOrder = $('#display_order').val() || 0;
        
        if (!platform) {
            booknetic.toast(booknetic.__('Platform name is required'), 'error');
            return;
        }
        
        if (!url) {
            booknetic.toast(booknetic.__('URL is required'), 'error');
            return;
        }
        
        if (!icon) {
            booknetic.toast(booknetic.__('Icon is required'), 'error');
            return;
        }
        
        // Validate URL format
        if (!isValidUrl(url)) {
            booknetic.toast(booknetic.__('Please enter a valid URL'), 'error');
            return;
        }
        
        // Show loading state
        const $saveBtn = $(this);
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Saving...'));
        
        // Send AJAX request
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_save_social_media',
                _wpnonce: nonce,
                id: id ? parseInt(id) : 0,
                platform: platform,
                icon: icon,
                url: url,
                display_order: displayOrder,
                active: 1 // Default to active
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status || response.success) {
                        // Show success message
                        booknetic.toast(booknetic.__('Social media link saved successfully!'), 'success');
                        
                        // Close modal
                        $('#socialLinkModal').modal('hide');
                        
                        // Reload social media links
                        loadSocialMediaLinks();
                    } else {
                        // Handle error with message from server
                        const errorMsg = response.error || booknetic.__('Failed to save social media link');
                        booknetic.toast(errorMsg, 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    console.error('Error parsing response:', e);
                    booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                }
            },
            error: function(xhr) {
                // Handle AJAX errors
                console.error('AJAX error:', xhr);
                booknetic.toast(booknetic.__('Failed to process request'), 'error');
            },
            complete: function() {
                // Reset button state
                $saveBtn.prop('disabled', false).html(booknetic.__('Save'));
            }
        });
    });    // Initialize icon picker
    function initIconPicker() {
        // Common social media icons
        const socialIcons = [
            'fab fa-facebook', 'fab fa-twitter', 'fab fa-instagram', 'fab fa-linkedin',
            'fab fa-youtube', 'fab fa-pinterest', 'fab fa-tiktok', 'fab fa-snapchat',
            'fab fa-whatsapp', 'fab fa-telegram', 'fab fa-discord', 'fab fa-slack',
            'fab fa-github', 'fab fa-dribbble', 'fab fa-behance', 'fab fa-medium',
            'fab fa-reddit', 'fab fa-quora', 'fab fa-tumblr', 'fab fa-vimeo',
            'fab fa-flickr', 'fab fa-twitch', 'fab fa-spotify', 'fab fa-soundcloud'
        ];
        
        // Common general icons for other sections like livechat
        const generalIcons = [
            'fas fa-comments', 'fas fa-comment-dots', 'fas fa-comment-alt', 'fas fa-headset',
            'fas fa-phone', 'fas fa-envelope', 'fas fa-envelope-open', 'fas fa-paper-plane',
            'fas fa-inbox', 'fas fa-question-circle', 'fas fa-info-circle', 'fas fa-life-ring',
            'fas fa-hands-helping', 'fas fa-user-headset', 'fas fa-concierge-bell', 'fas fa-user-shield',
            'fas fa-user-tie', 'fas fa-users', 'fas fa-user-friends', 'fas fa-heart',
            'fas fa-star', 'fas fa-lightbulb', 'fas fa-bolt', 'fas fa-magic'
        ];
        
        // Combine all icons
        const allIcons = [...socialIcons, ...generalIcons];
        
        const $iconGrid = $('#iconGrid');
        $iconGrid.empty();
        
        // Populate icon grid with all icons
        allIcons.forEach(function(icon) {
            const $iconItem = $('<div class="col mb-3 text-center icon-item"></div>');
            $iconItem.append('<div class="p-3 border rounded icon-box"><i class="' + icon + ' fa-2x"></i><div class="mt-2 small">' + icon + '</div></div>');
            $iconItem.data('icon', icon);
            $iconGrid.append($iconItem);
        });
    }
    
    // Initialize the icon picker
    initIconPicker();
    
    // Direct icon selection handler for social media
    $(document).on('click', '#iconGrid .icon-item', function() {
        const icon = $(this).data('icon');
        
        // If the social link modal is visible, update its icon field
        if ($('#socialLinkModal').is(':visible')) {
            $('#icon').val(icon);
            $('.preview-icon').attr('class', icon + ' text-primary');
            $('#iconPickerModal').modal('hide');
        } else if ($('#livechatPageForm').is(':visible')) {
            $('#icon').val(icon);
            $('#livechat-icon-preview').attr('class', icon + ' text-primary');
            $('#iconPickerModal').modal('hide');
        }
    });
    
    // When opening the icon picker, focus the icon input first
    $(document).on('click', '[data-target="#iconPickerModal"]', function() {
        // Find the closest input field
        const $input = $(this).closest('.input-group').find('input');
        if ($input.length) {
            // Focus the input to set it as the last focused input
            $input.focus();
        }
    });
    
    // Handle icon search
    $('#iconSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('.icon-item').each(function() {
            const icon = $(this).data('icon');
            if (icon.toLowerCase().includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    

});

