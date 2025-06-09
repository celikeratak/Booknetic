// Database Tables Creation Functionality
jQuery(document).ready(function($) {
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Handle database table creation
    $('#createTables').on('click', function() {
        const forceRecreate = $('#forceRecreate').is(':checked') ? 'yes' : 'no';
        const $resultContainer = $('#databaseTablesResult');
        const $createButton = $(this);
        
        // Confirm if force recreate is enabled
        if (forceRecreate === 'yes') {
            if (!confirm('Warning: This will delete all existing help center data. Are you sure you want to continue?')) {
                return;
            }
        }
        
        $createButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Tables...');
        $resultContainer.html('');
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_contact_us_p_create_tables',
                _wpnonce: nonce,
                force_recreate: forceRecreate
            },
            beforeSend: function() {
                // Show loading indicator
                $resultContainer.html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Creating database tables...</div>');
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.status) {
                        $resultContainer.html(`
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span>${response.message || 'Database tables created successfully!'}</span>
                            </div>
                        `).show();
                        
                        // Show toast notification
                        booknetic.toast(response.message || 'Database tables created successfully!', 'success');
                    } else {
                        // Handle error with message from server
                        $resultContainer.html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>${response.message || 'Failed to create database tables.'}</span>
                            </div>
                        `).show();
                        
                        booknetic.toast(response.message || 'Failed to create database tables.', 'error');
                    }
                } catch (e) {
                    // Handle parsing errors
                    $resultContainer.html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Invalid response from server.</span>
                        </div>
                    `).show();
                    
                    booknetic.toast('Invalid response from server.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr, status, error);
                $resultContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Failed to process request.</span>
                    </div>
                `).show();
                
                booknetic.toast('Failed to process request.', 'error');
            },
            complete: function() {
                // Reset button state
                $createButton.prop('disabled', false).html('<i class="fas fa-database" style="color: white; margin-right:5px;"></i> Create Database Tables');
            }
        });
    });
});
