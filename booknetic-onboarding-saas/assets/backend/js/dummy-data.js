// Dummy Data Import Functionality
(function($) {
    'use strict';
    
    // Get AJAX URL and nonce from helpCenterAjax or fallback
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initDummyDataImport();
    });
    
    /**
     * Initialize dummy data import functionality
     */
    function initDummyDataImport() {
        // Handle dummy data import
        $('#importDummyData').on('click', function() {
            const clearExisting = $('#clearExistingData').is(':checked') ? 'yes' : 'no';
            const $resultContainer = $('#dummyDataResult');
            const $importButton = $(this);
            
            // Show loading state
            $importButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
            $resultContainer.html('');
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_contact_us_p_import_dummy_data',
                    _wpnonce: nonce,
                    clear_existing: clearExisting
                },
                beforeSend: function() {
                    // Clear previous messages and show loading indicator
                    $resultContainer.html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Importing dummy data...</div>');
                },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Check status and handle success
                    if (response.success && response.data) {
                        // Create success message with summary
                        let successHtml = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.data.message + '</div>';
                        
                        // Add summary of imported data in a card with matching UI
                        successHtml += '<div class="card mt-4 mb-4 shadow-sm">';
                        successHtml += '  <div class="card-header d-flex align-items-center">';
                        successHtml += '    <i class="fas fa-clipboard-check text-primary mr-2" style="font-size: 1.2rem;"></i>';
                        successHtml += '    <h5 class="card-title mb-0">Summary of Imported Data</h5>';
                        successHtml += '  </div>';
                        successHtml += '  <div class="card-body">';
                        successHtml += '    <ul class="list-group list-group-flush">';
                        successHtml += '<li class="list-group-item d-flex justify-content-between align-items-center"><i class="fas fa-folder text-primary mr-2"></i> Categories added <span class="badge badge-primary badge-pill">' + response.data.categories_added + '</span></li>';
                        successHtml += '<li class="list-group-item d-flex justify-content-between align-items-center"><i class="fas fa-file-alt text-primary mr-2"></i> Topics added <span class="badge badge-primary badge-pill">' + response.data.topics_added + '</span></li>';
                        
                        if (response.data.search_logs_added) {
                            successHtml += '<li class="list-group-item d-flex justify-content-between align-items-center"><i class="fas fa-search text-primary mr-2"></i> Search logs added <span class="badge badge-primary badge-pill">' + response.data.search_logs_added + '</span></li>';
                        }
                        
                        if (response.data.settings_details) {
                            successHtml += '<li class="list-group-item"><div class="d-flex align-items-center mb-2"><i class="fas fa-cog text-primary mr-2"></i> <strong>Settings added:</strong></div><ul class="list-group mt-2 border">';
                            for (const [key, value] of Object.entries(response.data.settings_details)) {
                                let iconClass = 'fas fa-sliders-h';
                                
                                // Choose appropriate icon based on setting key
                                if (key.toLowerCase().includes('social') || key.toLowerCase().includes('share')) {
                                    iconClass = 'fas fa-share-alt';
                                } else if (key.toLowerCase().includes('menu') || key.toLowerCase().includes('navigation')) {
                                    iconClass = 'fas fa-bars';
                                } else if (key.toLowerCase().includes('support')) {
                                    iconClass = 'fas fa-life-ring';
                                } else if (key.toLowerCase().includes('color')) {
                                    iconClass = 'fas fa-palette';
                                } else if (key.toLowerCase().includes('css')) {
                                    iconClass = 'fas fa-code';
                                } else if (key.toLowerCase().includes('footer')) {
                                    iconClass = 'fas fa-pen-nib';
                                } else if (key.toLowerCase().includes('chat') || key.toLowerCase().includes('livechat')) {
                                    iconClass = 'fas fa-comments';
                                } else if (key.toLowerCase().includes('feedback')) {
                                    iconClass = 'fas fa-chart-line';
                                } else if (key.toLowerCase().includes('email')) {
                                    iconClass = 'fas fa-envelope';
                                } else if (key.toLowerCase().includes('logo')) {
                                    iconClass = 'fas fa-image';
                                } else if (key.toLowerCase().includes('title')) {
                                    iconClass = 'fas fa-heading';
                                } else if (key.toLowerCase().includes('url') || key.toLowerCase().includes('link')) {
                                    iconClass = 'fas fa-link';
                                }
                                
                                successHtml += '<li class="list-group-item d-flex justify-content-between align-items-center"><div><i class="' + iconClass + ' text-secondary mr-2"></i> ' + key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</div> <span class="badge badge-info badge-pill">' + value + '</span></li>';
                            }
                            successHtml += '</ul></li>';
                        }
                        
                        successHtml += '    </ul>';
                        successHtml += '  </div>';
                        successHtml += '</div>';
                        
                        // Show success message in the result container
                        $resultContainer.html(successHtml).show();
                        
                        // Add visual display of settings if available
                        if (response.data.html_content) {
                            $('#dummyDataDisplay').html(response.data.html_content);
                        }
                        booknetic.toast('Dummy data imported successfully!', 'success');
                    } else {
                        $resultContainer.html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + (response.error || 'Failed to import dummy data.') + '</div>');
                        booknetic.toast(response.error || 'Failed to import dummy data.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    $resultContainer.html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Invalid response from server.</div>');
                    booknetic.toast('Invalid response from server.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr, status, error);
                $resultContainer.html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Failed to process request.</div>');
                booknetic.toast('Failed to process request.', 'error');
            },
            complete: function() {
                $importButton.prop('disabled', false).html('<i class="fas fa-database" style="color: white; margin-right:5px;"></i> Import Dummy Data');
            }
        });
    });
    }
})(jQuery);
