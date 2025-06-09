// Menu Links Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    // Open modal for adding a new menu link
    $('#addMenuLink').on('click', function() {
        $('#menuLinkModalTitle').text(booknetic_help_i18n.add_menu_link);
        $('#menuLinkForm')[0].reset();
        $('#menu_link_id').val('');
        $('#menuLinkModal').modal('show');
    });
    
    // Open modal for editing an existing menu link
    $(document).on('click', '.edit-menu-link', function() {
        const id = $(this).data('id');
        const label = $(this).data('label');
        const url = $(this).data('url');
        const order = $(this).data('order');
        
        $('#menuLinkModalTitle').text(booknetic_help_i18n.edit_menu_link);
        $('#menu_link_id').val(id);
        $('#menu_label').val(label);
        $('#menu_url').val(url);
        $('#menu_order').val(order);
        
        $('#menuLinkModal').modal('show');
    });
    
    // Save menu link (create or update)
    $('#saveMenuLink').on('click', function() {
        // Validate form
        if (!$('#menuLinkForm')[0].checkValidity()) {
            $('#menuLinkForm')[0].reportValidity();
            return;
        }
        
        const id = $('#menu_link_id').val();
        const label = $('#menu_label').val();
        const url = $('#menu_url').val();
        const order = $('#menu_order').val();
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.saving);
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_save_menu_link',
                _wpnonce: nonce,
                id: id,
                label: label,
                url: url,
                order: order
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        $('#menuLinkModal').modal('hide');
                        
                        // Reload the table with updated data
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'booknetic_contact_us_p_get_menu_links',
                                _wpnonce: nonce
                            },
                            success: function(response) {
                                try {
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response);
                                    }
                                    
                                    if (response.status) {
                                        updateMenuLinksTable(response.data);
                                        booknetic.toast(booknetic_help_i18n.menu_link_saved_successfully, 'success');
                                    }
                                } catch (e) {
                                    booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                                }
                            }
                        });
                    } else {
                        booknetic.toast(response.error || booknetic_help_i18n.failed_to_save_menu_link, 'error');
                    }
                } catch (e) {
                    booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                }
            },
            error: function() {
                booknetic.toast(booknetic_help_i18n.failed_to_process_request, 'error');
            },
            complete: function() {
                $('#saveMenuLink').prop('disabled', false).html(booknetic_help_i18n.save_changes);
            }
        });
    });
    
    // Delete menu link
    $(document).on('click', '.delete-menu-link', function() {
        if (!confirm(booknetic_help_i18n.confirm_delete_menu_link || 'Are you sure you want to delete this menu link?')) {
            return;
        }
        
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_delete_menu_link',
                _wpnonce: nonce,
                id: id
            },
            beforeSend: function() {
                row.addClass('deleting');
            },
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.status) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            
                            // If no rows left, show the "no data" message
                            if ($('#menuLinksTableBody tr').length === 0) {
                                $('#menuLinksTableBody').html('<tr class="no-data-row"><td colspan="5" class="text-center">' + booknetic_help_i18n.no_menu_links_added_yet + '</td></tr>');
                            }
                        });
                        
                        booknetic.toast(booknetic_help_i18n.menu_link_deleted_successfully, 'success');
                    } else {
                        row.removeClass('deleting');
                        booknetic.toast(response.error || booknetic_help_i18n.failed_to_delete_menu_link, 'error');
                    }
                } catch (e) {
                    row.removeClass('deleting');
                    booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                }
            },
            error: function() {
                row.removeClass('deleting');
                booknetic.toast(booknetic_help_i18n.failed_to_process_request, 'error');
            }
        });
    });
    
    // Toggle menu link status
    $(document).on('change', '.toggle-menu-status', function() {
        const id = $(this).closest('tr').data('id');
        const active = $(this).prop('checked') ? 1 : 0;
        const checkbox = $(this);
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_toggle_menu_link',
                _wpnonce: nonce,
                id: id,
                active: active
            },
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (!response.status) {
                        // Revert the checkbox state if there was an error
                        checkbox.prop('checked', !active);
                        booknetic.toast(response.error || booknetic_help_i18n.failed_to_update_menu_link_status, 'error');
                    }
                } catch (e) {
                    checkbox.prop('checked', !active);
                    booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                }
            },
            error: function() {
                checkbox.prop('checked', !active);
                booknetic.toast(booknetic_help_i18n.failed_to_process_request, 'error');
            }
        });
    });
    
    // Function to update the menu links table with new data
    function updateMenuLinksTable(links) {
        let html = '';
        
        if (links && links.length > 0) {
            links.forEach(function(link) {
                html += `
                <tr data-id="${link.id}">
                    <td>${link.label}</td>
                    <td><a href="${link.url}" target="_blank">${link.url}</a></td>
                    <td>${link.order}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-menu-link" 
                            data-id="${link.id}" 
                            data-label="${link.label}" 
                            data-url="${link.url}"
                            data-order="${link.order}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-menu-link" data-id="${link.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
        } else {
            html = `<tr class="no-data-row"><td colspan="5" class="text-center">${booknetic_help_i18n.no_menu_links_added_yet}</td></tr>`;
        }
        
        $('#menuLinksTableBody').html(html);
    }
    
    // Load menu links on page load
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'booknetic_contact_us_p_get_menu_links',
            _wpnonce: nonce
        },
        beforeSend: function() {
            // Show loading indicator
            $('#menuLinksTableBody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.loading + '</td></tr>');
        },
        success: function(response) {
            try {
                // Parse response if it's a string
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                
                // Check status and handle success
                if (response.status) {
                    // Update menu links table
                    updateMenuLinksTable(response.data);
                } else {
                    // Handle error with message from server
                    booknetic.toast(booknetic_help_i18n.failed_to_load_menu_links, 'error');
                    $('#menuLinksTableBody').html('<tr><td colspan="5" class="text-center">' + booknetic_help_i18n.failed_to_load_menu_links + '</td></tr>');
                }
            } catch (e) {
                // Handle parsing errors
                booknetic.toast(booknetic_help_i18n.invalid_response_from_server, 'error');
                $('#menuLinksTableBody').html('<tr><td colspan="5" class="text-center">' + booknetic_help_i18n.failed_to_load_menu_links + '</td></tr>');
            }
        },
        error: function() {
            // Handle AJAX errors
            booknetic.toast(booknetic_help_i18n.failed_to_load_menu_links, 'error');
            $('#menuLinksTableBody').html('<tr><td colspan="5" class="text-center">' + booknetic_help_i18n.failed_to_load_menu_links + '</td></tr>');
        }
    });
});
