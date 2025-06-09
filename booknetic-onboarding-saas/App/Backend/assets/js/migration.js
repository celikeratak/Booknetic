/**
 * Booknetic Onboarding SaaS - Migration Feature
 * 
 * This file handles the migration feature in the Advanced tab of the Settings page.
 * It allows users to check for and run database migrations after plugin updates.
 */

(function($) {
    'use strict';

    // Get AJAX URL and nonce following Booknetic pattern
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    

    // DOM elements
    const $runMigrationBtn = $('#runMigration');
    const $backupBeforeMigration = $('#backupBeforeMigration');
    const $migrationResult = $('#migrationResult');
    const $migrationStatusIcon = $('#migrationStatusIcon');
    const $migrationStatusTitle = $('#migrationStatusTitle');
    const $migrationStatusDescription = $('#migrationStatusDescription');

    /**
     * Initialize the migration feature
     */
    function init() {
        
        // Check migration status on page load
        checkMigrationStatus();

        // Run migration button click handler
        $runMigrationBtn.on('click', function(e) {
            runMigration();
        });
    }

    /**
     * Check if any migrations are needed
     */
    function checkMigrationStatus() {
        // Update UI to show checking status
        updateMigrationStatus('checking', 'Checking migration status...', 'Please wait while we check if any migrations are needed.');

        // Make AJAX request to check migration status following Booknetic pattern
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_check_migration_status',
                _wpnonce: nonce
            },
            beforeSend: function() {
                // Disable run button while checking
                $runMigrationBtn.prop('disabled', true);
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Update UI based on migration status
                    if (response.status) {
                        if (response.data.migrations_needed) {
                            // Migrations needed
                            updateMigrationStatus(
                                'needed', 
                                'Migrations needed', 
                                'There are ' + response.data.pending_migrations + ' pending migrations. Click the "Run Migration" button to update your database.'
                            );
                        } else {
                            // No migrations needed
                            updateMigrationStatus(
                                'up_to_date', 
                                'Database is up to date', 
                                'Your database schema is up to date. No migrations are needed at this time.'
                            );
                            
                            // Show success message
                            booknetic.toast('Database is up to date', 'success');
                        }
                    } else {
                        // Error checking migration status
                        updateMigrationStatus(
                            'error', 
                            'Error checking migration status', 
                            response.error || 'An error occurred while checking migration status.'
                        );
                        
                        // Show error message
                        booknetic.toast(response.error || 'Error checking migration status', 'error');
                    }
                } catch (e) {
                    // Error parsing response
                    updateMigrationStatus(
                        'error', 
                        'Error checking migration status', 
                        'Invalid response from server.'
                    );
                    
                    // Show error message
                    booknetic.toast('Invalid response from server', 'error');
                }
            },
            error: function() {
                // AJAX error
                updateMigrationStatus(
                    'error', 
                    'Error checking migration status', 
                    'Failed to connect to the server. Please try again.'
                );
                
                // Show error message
                booknetic.toast('Failed to process request', 'error');
            },
            complete: function() {
                // Enable run button
                $runMigrationBtn.prop('disabled', false);
            }
        });
    }

    /**
     * Run database migrations
     */
    function runMigration() {
        // Get backup option
        const createBackup = $backupBeforeMigration.is(':checked');

        // Update UI to show migration in progress
        updateMigrationStatus('in_progress', 'Migration in progress...', 'Please wait while we update your database. This may take a few moments.');
        
        // Clear previous results
        $migrationResult.empty().hide();

        // Make AJAX request to run migrations following Booknetic pattern
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'booknetic_run_migration',
                _wpnonce: nonce,
                create_backup: createBackup
            },
            beforeSend: function() {
                // Disable run button while migrating
                $runMigrationBtn.prop('disabled', true);
                
                // Show loading indicator
                booknetic.loading(true);
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    // Handle migration result
                    if (response.status) {
                        // Migration successful
                        updateMigrationStatus(
                            'success', 
                            'Migration completed successfully', 
                            'Your database has been updated to the latest version.'
                        );
                        
                        // Show detailed results
                        showMigrationResults(response.data);
                        
                        // Show success message
                        booknetic.toast('Database migration completed successfully', 'success');
                    } else {
                        // Migration failed
                        updateMigrationStatus(
                            'error', 
                            'Migration failed', 
                            response.error || 'An error occurred during migration.'
                        );
                        
                        // Show error message
                        booknetic.toast(response.error || 'Migration failed', 'error');
                    }
                } catch (e) {
                    // Error parsing response
                    updateMigrationStatus(
                        'error', 
                        'Migration failed', 
                        'Invalid response from server.'
                    );
                    
                    // Show error message
                    booknetic.toast('Invalid response from server', 'error');
                }
            },
            error: function() {
                // AJAX error
                updateMigrationStatus(
                    'error', 
                    'Migration failed', 
                    'Failed to connect to the server. Please try again.'
                );
                
                // Show error message
                booknetic.toast('Failed to process request', 'error');
            },
            complete: function() {
                // Enable run button
                $runMigrationBtn.prop('disabled', false);
                
                // Hide loading indicator
                booknetic.loading(false);
                
                // Refresh migration status after a short delay
                setTimeout(checkMigrationStatus, 3000);
            }
        });
    }

    /**
     * Update the migration status UI
     * 
     * @param {string} status - Status code (checking, needed, up_to_date, in_progress, success, error)
     * @param {string} title - Status title
     * @param {string} description - Status description
     */
    function updateMigrationStatus(status, title, description) {
        // Update title and description
        $migrationStatusTitle.text(title);
        $migrationStatusDescription.text(description);
        
        // Update icon based on status
        $migrationStatusIcon.html('');
        
        switch (status) {
            case 'checking':
                $migrationStatusIcon.html('<i class="fas fa-spinner fa-spin text-secondary fa-2x"></i>');
                break;
            case 'needed':
                $migrationStatusIcon.html('<i class="fas fa-exclamation-triangle text-warning fa-2x"></i>');
                $runMigrationBtn.prop('disabled', false);
                break;
            case 'up_to_date':
                $migrationStatusIcon.html('<i class="fas fa-check-circle text-success fa-2x"></i>');
                $runMigrationBtn.prop('disabled', true);
                break;
            case 'in_progress':
                $migrationStatusIcon.html('<i class="fas fa-sync-alt fa-spin text-primary fa-2x"></i>');
                $runMigrationBtn.prop('disabled', true);
                break;
            case 'success':
                $migrationStatusIcon.html('<i class="fas fa-check-circle text-success fa-2x"></i>');
                $runMigrationBtn.prop('disabled', true);
                break;
            case 'error':
                $migrationStatusIcon.html('<i class="fas fa-times-circle text-danger fa-2x"></i>');
                $runMigrationBtn.prop('disabled', false);
                break;
            default:
                $migrationStatusIcon.html('<i class="fas fa-question-circle text-secondary fa-2x"></i>');
        }
    }

    /**
     * Show detailed migration results
     * 
     * @param {Object} data - Migration result data
     */
    function showMigrationResults(data) {
        // Create result HTML
        let resultHtml = '<div class="alert alert-success">';
        resultHtml += '<h6><i class="fas fa-check-circle mr-2"></i>Migration Summary</h6>';
        
        // Add migration details
        if (data.backup_created) {
            resultHtml += '<p><strong>Backup:</strong> Created successfully</p>';
        }
        
        resultHtml += '<p><strong>Migrations applied:</strong> ' + data.migrations_applied + '</p>';
        
        // Add list of applied migrations if available
        if (data.applied_migrations && data.applied_migrations.length > 0) {
            resultHtml += '<p><strong>Applied changes:</strong></p>';
            resultHtml += '<ul>';
            data.applied_migrations.forEach(function(migration) {
                resultHtml += '<li>' + migration + '</li>';
            });
            resultHtml += '</ul>';
        }
        
        resultHtml += '</div>';
        
        // Show results
        $migrationResult.html(resultHtml).show();
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });
    
    // Alternative initialization for WordPress admin
    $(window).on('load', function() {
        if (!$runMigrationBtn.data('initialized')) {
            init();
        }
    });

})(jQuery);
