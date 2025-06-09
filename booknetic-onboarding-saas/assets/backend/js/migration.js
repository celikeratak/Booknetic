/**
 * Booknetic Migration Section JavaScript
 * This file handles all the functionality for the migration section
 */

(function($) {
    'use strict';
    

    // Migration section functionality
    const BookneticMigration = {
        init: function() {
            this.bindEvents();
            this.checkMigrationStatus();
        },

        bindEvents: function() {
            // Refresh migration status
            $('#bkntc-refresh-migration-status').on('click', this.checkMigrationStatus.bind(this));
            
            // Run migration
            $('#bkntc-run-migration').on('click', this.runMigration.bind(this));
            
            // Backup management
            $('#bkntc-create-backup').on('click', this.createBackup.bind(this));
            $('#bkntc-view-backups').on('click', this.showBackupsModal.bind(this));
            
            // Close result
            $('#bkntc-close-migration-result').on('click', this.hideResult.bind(this));
            
            // Close modals
            $('#bkntc-close-backups-modal').on('click', this.hideBackupsModal.bind(this));
            
            // Document click to close modal when clicking outside
            $(document).on('click', function(e) {
                if ($(e.target).hasClass('bkntc-migration-modal')) {
                    BookneticMigration.hideBackupsModal();
                }
            });
        },

        // Check migration status
        checkMigrationStatus: function() {
            this.setStatusChecking();
            
            // Record the start time
            const startTime = new Date().getTime();
            const minimumLoadingTime = 2500; // 2.5 seconds minimum loading time
            
            // Store the current progress bar state to prevent resets
            window.bkntcCheckingState = true;
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_check_migration_status',
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Calculate how much time has passed
                        const currentTime = new Date().getTime();
                        const elapsedTime = currentTime - startTime;
                        
                        // If the response came back too quickly, add a delay
                        if (elapsedTime < minimumLoadingTime) {
                            const remainingTime = minimumLoadingTime - elapsedTime;
                            
                            setTimeout(function() {
                                window.bkntcCheckingState = false;
                                if (response.status) {
                                    BookneticMigration.updateStatusDisplay(response.data);
                                } else {
                                    BookneticMigration.showError(response.error || 'Failed to check migration status');
                                }
                            }, remainingTime);
                        } else {
                            // If enough time has passed, update immediately
                            window.bkntcCheckingState = false;
                            if (response.status) {
                                BookneticMigration.updateStatusDisplay(response.data);
                            } else {
                                BookneticMigration.showError(response.error || 'Failed to check migration status');
                            }
                        }
                    } catch (e) {
                        window.bkntcCheckingState = false;
                        BookneticMigration.showError('Invalid response from server');
                    }
                },
                error: function() {
                    // For errors, also ensure minimum loading time
                    const currentTime = new Date().getTime();
                    const elapsedTime = currentTime - startTime;
                    
                    if (elapsedTime < minimumLoadingTime) {
                        setTimeout(function() {
                            window.bkntcCheckingState = false;
                            BookneticMigration.showError('Failed to connect to server');
                        }, minimumLoadingTime - elapsedTime);
                    } else {
                        window.bkntcCheckingState = false;
                        BookneticMigration.showError('Failed to connect to server');
                    }
                }
            });
        },

        // Set status to checking with enhanced visual feedback
        setStatusChecking: function() {
            // Don't reset the progress bar if we're already in checking state
            if (window.bkntcCheckingState) {
                return;
            }
            
            // Set checking state flag to prevent duplicate animations
            window.bkntcCheckingState = true;
            
            // Clear any existing animation interval to prevent memory leaks
            if (window.bkntcStatusCheckingInterval) {
                clearInterval(window.bkntcStatusCheckingInterval);
                window.bkntcStatusCheckingInterval = null;
            }
            
            // Fade out existing content for a smooth transition
            $('#bkntc-migration-status-indicator .bkntc-migration-status-icon').fadeOut(200, function() {
                $(this)
                    .removeClass('status-uptodate status-needsupdate status-notinstalled')
                    .addClass('status-checking')
                    .html('<i class="fas fa-sync-alt"></i>')
                    .fadeIn(300, function() {
                        // Add a subtle entrance animation after fade-in
                        const $icon = $(this).find('i');
                        $icon.css({
                            'transform': 'scale(0.5)',
                            'opacity': '0.5'
                        }).animate({
                            'transform': 'scale(1)',
                            'opacity': '1'
                        }, 400);
                    });
                
                // Add a subtle animation to the icon
                const rotateAnimation = function() {
                    const $icon = $('#bkntc-migration-status-indicator .bkntc-migration-status-icon i');
                    $icon.css({
                        'transition': 'transform 1.5s cubic-bezier(0.68, -0.55, 0.27, 1.55)',
                        'transform': 'rotate(0deg)'
                    });
                    
                    setTimeout(function() {
                        $icon.css('transform', 'rotate(360deg)');
                    }, 50);
                };
                
                // Start the rotation animation and repeat it with a stored interval reference
                rotateAnimation();
                window.bkntcStatusCheckingInterval = setInterval(rotateAnimation, 1500);
            });
            
            // Update text with typing effect
            const $title = $('#bkntc-migration-status-title');
            const $description = $('#bkntc-migration-status-description');
            const checkingText = booknetic_help_i18n.checking_status || 'Checking status...';
            const waitingText = booknetic_help_i18n.please_wait || 'Please wait while we check your database.';
            
            $title.empty();
            $description.empty();
            
            // Simple typing effect for the title
            let titleIndex = 0;
            const typeTitle = function() {
                if (titleIndex < checkingText.length) {
                    $title.append(checkingText.charAt(titleIndex));
                    titleIndex++;
                    setTimeout(typeTitle, 50);
                }
            };
            
            // Start typing the title
            typeTitle();
            
            // Fade in the description after a short delay
            setTimeout(function() {
                $description.text(waitingText).css('opacity', 0).animate({opacity: 1}, 500);
            }, 600);
            
            // Reset version badges with a fade effect
            $('#bkntc-current-db-version, #bkntc-latest-db-version').fadeOut(200, function() {
                $(this).text('-').fadeIn(300);
            });
            
            // Animate the progress bar to show activity
            const $progressBar = $('#bkntc-migration-progress-bar');
            const $progressText = $('#bkntc-migration-progress-text');
            
            // Clear any existing animations and intervals
            if (window.bkntcProgressInterval) {
                clearInterval(window.bkntcProgressInterval);
                window.bkntcProgressInterval = null;
            }
            
            // Stop any ongoing animations
            $progressBar.stop(true, true);
            
            // Reset progress bar
            $progressBar.removeClass().addClass('bkntc-migration-progress-bar checking');
            $progressBar.css('width', '0%');
            $progressText.text('0%');
            
            // Simple pulsing animation for the checking state that doesn't reset
            setTimeout(function() {
                $progressBar.animate({width: '100%'}, {
                    duration: 1400,
                    easing: 'easeOutQuad',
                    step: function(now) {
                        $progressText.text(Math.round(now) + '%');
                    }
                });
            }, 300);
            
            // Define easing function if not already available
            if (!$.easing.easeOutQuad) {
                $.easing.easeOutQuad = function(x) {
                    return 1 - (1 - x) * (1 - x);
                };
            }
        },

        // Update status display based on response with enhanced transitions
        updateStatusDisplay: function(data) {
            // Reset the checking state flag
            window.bkntcCheckingState = false;
            
            const statusIcon = $('#bkntc-migration-status-indicator .bkntc-migration-status-icon');
            const statusTitle = $('#bkntc-migration-status-title');
            const statusDesc = $('#bkntc-migration-status-description');
            const progressBar = $('#bkntc-migration-progress-bar');
            const progressText = $('#bkntc-migration-progress-text');
            
            // Update version badges with animation
            $('#bkntc-current-db-version').fadeOut(300, function() {
                $(this).text(data.installed_version || 'Not Installed').fadeIn(300);
            });
            
            $('#bkntc-latest-db-version').fadeOut(300, function() {
                $(this).text(data.latest_version).fadeIn(300);
            });
            
            // Calculate progress percentage
            let progressPercentage = 0;
            
            // Determine status based on migrations_needed
            const status = data.installed_version === '1.0.0' && data.migrations_needed ? 'needs_update' : 
                           data.installed_version && !data.migrations_needed ? 'up_to_date' : 'not_installed';
            
            
            // Clear any existing animation intervals
            if (window.bkntcRotateInterval) {
                clearInterval(window.bkntcRotateInterval);
                window.bkntcRotateInterval = null;
            }
            
            // Clear any existing animation interval to prevent memory leaks
            if (window.bkntcStatusCheckingInterval) {
                clearInterval(window.bkntcStatusCheckingInterval);
                window.bkntcStatusCheckingInterval = null;
            }
            
            // Clear progress bar animation interval
            if (window.bkntcProgressInterval) {
                clearInterval(window.bkntcProgressInterval);
                window.bkntcProgressInterval = null;
            }
            
            // Prepare the status update with animation
            const updateStatus = function(iconClass, iconHTML, titleText, descText, progressColor, progressValue) {
                // Animate the status icon change with a smooth transition
                statusIcon.fadeOut(300, function() {
                    $(this).removeClass('status-checking status-uptodate status-needsupdate status-notinstalled')
                        .addClass(iconClass)
                        .html(iconHTML);
                        
                    // Add a subtle entrance animation with a bounce effect
                    $(this).css({
                        'transform': 'scale(0.8)',
                        'opacity': '0'
                    }).show().animate({
                        'transform': 'scale(1.1)',  // Slightly overshoot for bounce effect
                        'opacity': '1'
                    }, {
                        duration: 300,
                        complete: function() {
                            // Bounce back to normal size
                            $(this).animate({
                                'transform': 'scale(1)'
                            }, {
                                duration: 150,
                                easing: 'easeOutBack'
                            });
                        }
                    });
                });
                
                // Animate the title change
                statusTitle.fadeOut(300, function() {
                    $(this).text(titleText).fadeIn(300);
                });
                
                // Animate the description change
                statusDesc.fadeOut(300, function() {
                    $(this).text(descText).fadeIn(300);
                });
                
                // Get the current progress width
                const currentWidth = parseFloat(progressBar.css('width')) / progressBar.parent().width() * 100;
                
                // Only animate if the new value is greater than the current value
                // This prevents the progress bar from going backwards
                const targetValue = Math.max(currentWidth, progressValue);
                
                // Stop any ongoing progress bar animations and clear intervals
                progressBar.stop(true, true);
                
                if (window.bkntcProgressInterval) {
                    clearInterval(window.bkntcProgressInterval);
                    window.bkntcProgressInterval = null;
                }
                
                // Remove checking class
                progressBar.removeClass('checking');
                
                // Set the background color
                progressBar.css('background', progressColor);
                
                // Simple animation to the target value
                progressBar.animate({
                    width: targetValue + '%'
                }, {
                    duration: 800,
                    easing: 'easeOutQuad',
                    step: function(now) {
                        // Update the progress text during animation
                        progressText.text(Math.round(now) + '%');
                    },
                    complete: function() {
                        // Ensure the final text is accurate
                        progressText.text(targetValue + '%');
                        
                        // Add completion effect for 100%
                        if (targetValue >= 100) {
                            // Add shine effect
                            const $shine = $('<div class="progress-shine"></div>');
                            progressBar.append($shine);
                            
                            setTimeout(function() {
                                $shine.addClass('animate');
                                setTimeout(function() {
                                    $shine.remove();
                                }, 1000);
                            }, 100);
                        }
                    }
                });
            };
            
            // Define easing functions if not already available
            if (!$.easing.easeOutQuart) {
                $.easing.easeOutQuart = function(x) {
                    return 1 - Math.pow(1 - x, 4);
                };
            }
            
            if (!$.easing.easeOutBack) {
                $.easing.easeOutBack = function(x) {
                    const c1 = 1.70158;
                    const c3 = c1 + 1;
                    return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2);
                };
            }
            
            // Apply the appropriate status update
            if (status === 'not_installed') {
                // Use 'Update Required' instead of 'Not Installed'
                updateStatus(
                    'status-needsupdate', // Use needs update class instead of not installed
                    '<i class="fas fa-exclamation-triangle"></i>', // Use warning icon instead of error
                    booknetic_help_i18n.update_required,
                    booknetic_help_i18n.database_needs_update,
                    '#ffc107', // Use warning color instead of error
                    0
                );
                
            } else if (status === 'needs_update') {
                // Calculate progress
                if (data.installed_version && data.latest_version) {
                    const current = parseFloat(data.installed_version.replace(/[^0-9.]/g, ''));
                    const latest = parseFloat(data.latest_version.replace(/[^0-9.]/g, ''));
                    
                    if (!isNaN(current) && !isNaN(latest) && latest > 0) {
                        progressPercentage = Math.round((current / latest) * 100);
                    }
                }
                
                updateStatus(
                    'status-needsupdate',
                    '<i class="fas fa-exclamation-triangle"></i>',
                    booknetic_help_i18n.update_required,
                    booknetic_help_i18n.database_needs_update,
                    '#ffc107',
                    progressPercentage
                );
                
            } else if (status === 'up_to_date') {
                updateStatus(
                    'status-uptodate',
                    '<i class="fas fa-check-circle"></i>',
                    booknetic_help_i18n.up_to_date,
                    booknetic_help_i18n.database_up_to_date,
                    'var(--primary-color)',
                    100
                );
            }
        },
        // Run migration
        runMigration: function() {
            const createBackup = $('#bkntc-backup-before-migration').is(':checked');
            
            // Show loading state
            $('#bkntc-run-migration').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.running);
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_run_migration',
                    create_backup: createBackup ? 1 : 0,
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.status) {
                            BookneticMigration.showResult(
                                'success',
                                booknetic_help_i18n.migration_successful,
                                response.data.message,
                                response.data.details || null
                            );
                            
                            // Refresh status after successful migration
                            BookneticMigration.checkMigrationStatus();
                        } else {
                            BookneticMigration.showResult(
                                'error',
                                booknetic_help_i18n.migration_failed,           response.error || booknetic_help_i18n.unknown_error,
                                response.details || null
                            );
                        }
                    } catch (e) {
                        BookneticMigration.showResult(
                            'error',
                            booknetic_help_i18n.migration_failed,                  booknetic_help_i18n.invalid_response,
                            null
                        );
                    }
                },
                error: function() {
                    BookneticMigration.showResult(
                        'error',
                        booknetic_help_i18n.migration_failed,
                        booknetic_help_i18n.server_error,
                        null
                    );
                },
                complete: function() {
                    // Reset button state
                    $('#bkntc-run-migration').prop('disabled', false).html('<i class="fas fa-play-circle"></i> ' + booknetic_help_i18n.run_migration);
                }
            });
        },

        // Create backup
        createBackup: function() {
            // Show loading state
            $('#bkntc-create-backup').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.creating);
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_create_backup',
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.status) {
                            BookneticMigration.showResult(
                                'success',
                                booknetic_help_i18n.backup_created,
                                response.data.message,
                                null
                            );
                        } else {
                            BookneticMigration.showResult(
                                'error',
                                booknetic_help_i18n.backup_failed,                      response.error || booknetic_help_i18n.unknown_error,
                                null
                            );
                        }
                    } catch (e) {
                        BookneticMigration.showResult(
                            'error',
                            booknetic_help_i18n.backup_failed,
                            booknetic_help_i18n.invalid_response,
                            null
                        );
                    }
                },
                error: function() {
                    BookneticMigration.showResult(
                        'error',
                        booknetic_help_i18n.backup_failed,
                        booknetic_help_i18n.server_error,
                        null
                    );
                },
                complete: function() {
                    // Reset button state
                    $('#bkntc-create-backup').prop('disabled', false).html('<i class="fas fa-download"></i> ' + booknetic_help_i18n.create_backup);
                }
            });
        },

        // Show backups modal
        showBackupsModal: function() {
            // Show modal
            $('#bkntc-backups-modal').css('display', 'flex');
            
            // Load backups
            this.loadBackups();
        },

        // Hide backups modal
        hideBackupsModal: function() {
            $('#bkntc-backups-modal').hide();
        },

        // Load backups
        loadBackups: function() {
            const backupsList = $('#bkntc-backups-list');
            
            // Show loading
            backupsList.html('<div class="bkntc-migration-loading"><i class="fas fa-spinner fa-spin"></i> ' + booknetic_help_i18n.loading_backups + '</div>');
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_get_backups',
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.status) {
                            BookneticMigration.renderBackupsList(response.data.backups);
                        } else {
                            backupsList.html('<div class="bkntc-migration-error">' + (response.error || booknetic_help_i18n.failed_to_load_backups) + '</div>');
                        }
                    } catch (e) {
                        backupsList.html('<div class="bkntc-migration-error">' + booknetic_help_i18n.invalid_response + '</div>');
                    }
                },
                error: function() {
                    backupsList.html('<div class="bkntc-migration-error">' + booknetic_help_i18n.server_error + '</div>');
                }
            });
        },

        // Render backups list
        renderBackupsList: function(backups) {
            const backupsList = $('#bkntc-backups-list');
            
            
            // Check if backups is an object or array
            if (!backups || (Array.isArray(backups) && backups.length === 0) || (typeof backups === 'object' && Object.keys(backups).length === 0)) {
                backupsList.html('<div class="bkntc-migration-empty">' + booknetic_help_i18n.no_backups + '</div>');
                return;
            }
            
            let html = '';
            
            // Convert object to array if needed
            const backupsArray = Array.isArray(backups) ? backups : Object.values(backups);
            
            backupsArray.forEach(function(backup) {
                html += '<div class="bkntc-migration-backup-item" data-id="' + backup.id + '">';
                html += '    <div class="bkntc-migration-backup-info">';
                html += '        <div class="bkntc-migration-backup-name">' + backup.name + '</div>';
                html += '        <div class="bkntc-migration-backup-date">' + backup.date + '</div>';
                html += '    </div>';
                html += '    <div class="bkntc-migration-backup-size">' + backup.size + '</div>';
                html += '    <div class="bkntc-migration-backup-actions">';
                html += '        <button type="button" class="bkntc-migration-backup-action restore" data-id="' + backup.id + '">';
                html += '            <i class="fas fa-undo"></i> ' + booknetic_help_i18n.restore;
                html += '        </button>';
                html += '        <button type="button" class="bkntc-migration-backup-action download" data-id="' + backup.id + '">';
                html += '            <i class="fas fa-download"></i> ' + booknetic_help_i18n.download;
                html += '        </button>';
                html += '        <button type="button" class="bkntc-migration-backup-action delete" data-id="' + backup.id + '">';
                html += '            <i class="fas fa-trash-alt"></i> ' + booknetic_help_i18n.delete;
                html += '        </button>';
                html += '    </div>';
                html += '</div>';
            });
            
            backupsList.html(html);
            
            // Bind events for backup actions
            $('.bkntc-migration-backup-action.restore').on('click', function() {
                const backupId = $(this).data('id');
                BookneticMigration.restoreBackup(backupId);
            });
            
            $('.bkntc-migration-backup-action.download').on('click', function() {
                const backupId = $(this).data('id');
                BookneticMigration.downloadBackup(backupId);
            });
            
            $('.bkntc-migration-backup-action.delete').on('click', function() {
                const backupId = $(this).data('id');
                BookneticMigration.deleteBackup(backupId);
            });
        },

        // Restore backup
        restoreBackup: function(backupId) {
            if (!confirm(booknetic_help_i18n.confirm_restore_backup)) {
                return;
            }
            
            // Show loading
            const button = $('.bkntc-migration-backup-action.restore[data-id="' + backupId + '"]');
            const originalText = button.html();
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_restore_backup',
                    backup_id: backupId,
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.status) {
                            BookneticMigration.hideBackupsModal();
                            BookneticMigration.showResult(
                                'success',
                                booknetic_help_i18n.backup_restored,
                                response.data.message,
                                null
                            );
                            
                            // Refresh status after restore
                            BookneticMigration.checkMigrationStatus();
                        } else {
                            alert(response.error || booknetic_help_i18n.restore_failed);
                        }
                    } catch (e) {
                        alert(booknetic_help_i18n.invalid_response);          }
                },
                error: function() {
                    alert(booknetic_help_i18n.server_error);      },
                complete: function() {
                    // Reset button state
                    button.prop('disabled', false).html(originalText);
                }
            });
        },

        // Download backup
        downloadBackup: function(backupId) {
            window.location.href = ajaxurl + '?action=booknetic_download_backup&backup_id=' + backupId + '&_wpnonce=' + booknetic_help_i18n.nonce;
        },

        // Delete backup
        deleteBackup: function(backupId) {
            if (!confirm(booknetic_help_i18n.confirm_delete_backup)) {
                return;
            }
            
            // Show loading
            const button = $('.bkntc-migration-backup-action.delete[data-id="' + backupId + '"]');
            const originalText = button.html();
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'booknetic_delete_backup',
                    backup_id: backupId,
                    _wpnonce: booknetic_help_i18n.nonce
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.status) {
                            // Remove the backup item from the list
                            $('.bkntc-migration-backup-item[data-id="' + backupId + '"]').fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if there are no more backups
                                if ($('.bkntc-migration-backup-item').length === 0) {
                                    $('#bkntc-backups-list').html('<div class="bkntc-migration-empty">' + booknetic_help_i18n.no_backups + '</div>');
                                }
                            });
                        } else {
                            alert(response.error || booknetic_help_i18n.delete_failed);
                        }
                    } catch (e) {
                        alert(booknetic_help_i18n.invalid_response);          }
                },
                error: function() {
                    alert(booknetic_help_i18n.server_error);      },
                complete: function() {
                    // Reset button state
                    button.prop('disabled', false).html(originalText);
                }
            });
        },

        // Show result
        showResult: function(type, title, message, details) {
            const resultContainer = $('#bkntc-migration-result');
            const resultContent = $('#bkntc-migration-result-content');
            const resultTitle = $('#bkntc-migration-result-title');
            
            // Set title
            resultTitle.text(title);
            
            // Build content
            let content = '<div class="bkntc-migration-result-' + type + '">' + message + '</div>';
            
            if (details) {
                content += '<div class="bkntc-migration-result-code">' + details + '</div>';
            }
            
            // Set content
            resultContent.html(content);
            
            // Show result
            resultContainer.show();
            
            // Scroll to result
            $('html, body').animate({
                scrollTop: resultContainer.offset().top - 50
            }, 500);
        },

        // Hide result
        hideResult: function() {
            $('#bkntc-migration-result').hide();
        },

        // Show error
        showError: function(message) {
            this.showResult('error', booknetic_help_i18n.error, message, null);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        // Check if migration section exists
        if ($('#bkntc-migration-section').length > 0) {
            BookneticMigration.init();
        }
    });

})(jQuery);
