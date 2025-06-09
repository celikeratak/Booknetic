<?php
defined('ABSPATH') or die('Direct access not allowed');
?>

<!-- Updates Section -->
<div class="card mt-4" id="bkntc-updates-section">
    <div class="card-header">
        <i class="fas fa-cloud-download-alt text-primary"></i>
        <h5 class="card-title"><?php echo bkntc__('Update Center')?></h5>
    </div>
    <div class="card-body">
        <!-- Coming Soon Overlay -->
        <div class="bkntc-coming-soon-overlay">
            <div class="bkntc-coming-soon-badge">
                <i class="fas fa-rocket" style="color: #fff;"></i> <?php echo bkntc__('Coming Soon')?>!
            </div>
            <p class="bkntc-coming-soon-message"><?php echo bkntc__('This feature will be available in the next update. Stay tuned!')?></p>
        </div>
        
        <div class="bkntc-updates-status-container bkntc-disabled-section">
            <div class="bkntc-migration-status-header">
                <h4><?php echo bkntc__('Check for updates')?></h4>
                <button type="button" class="bkntc-migration-refresh-btn" id="bkntc-refresh-update-status" disabled>
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div class="bkntc-migration-status-panel">
                <div class="bkntc-migration-status-info">
                    <div class="bkntc-migration-version-info">
                        <div class="bkntc-migration-version-item">
                            <span class="bkntc-migration-version-label"><?php echo bkntc__('Current Version:')?></span>
                            <span class="bkntc-migration-version-badge bkntc-migration-current-version" id="bkntc-current-plugin-version">3.0.0</span>
                        </div>
                        <div class="bkntc-migration-version-divider">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="bkntc-migration-version-item">
                            <span class="bkntc-migration-version-label"><?php echo bkntc__('Latest Version:')?></span>
                            <span class="bkntc-migration-version-badge bkntc-migration-latest-version" id="bkntc-latest-plugin-version">4.0.0</span>
                        </div>
                    </div>
                    
                    <div class="bkntc-migration-status-indicator"  id="bkntc-update-status-indicator">
                        <div class="bkntc-migration-status-icon" style="background-color: var(--bkntc-primary-color-back)">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="bkntc-migration-status-text">
                            <h5 id="bkntc-update-status-title"><?php echo bkntc__('Update is available!')?></h5>
                            <p id="bkntc-update-status-description"><?php echo bkntc__('You have a new version available. Please update to the latest version.')?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bkntc-migration-actions bkntc-disabled-section">
            <div class="bkntc-migration-action-card">
                <div class="bkntc-migration-action-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="bkntc-migration-action-content">
                    <h4><?php echo bkntc__('Backup Management')?></h4>
                    <p><?php echo bkntc__('Manage your plugin backups.')?></p>
                    <div class="bkntc-migration-checkbox-container">
                        <label class="bkntc-migration-checkbox">
                            <input type="checkbox" id="bkntc-backup-before-update" checked disabled>
                            <span class="bkntc-migration-checkbox-checkmark"></span>
                            <span class="bkntc-migration-checkbox-label"><?php echo bkntc__('Create backup before updating')?></span>
                        </label>
                        <div class="bkntc-migration-checkbox-hint"><?php echo bkntc__('Recommended for safety')?></div>
                    </div>
                    <button type="button" class="bkntc-migration-button bkntc-migration-primary-button" id="bkntc-check-updates" disabled>
                        <i class="fas fa-search"></i> <?php echo bkntc__('View Backups')?>
                    </button>
                </div>
            </div>
            
            <div class="bkntc-migration-action-card">
                <div class="bkntc-migration-action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="bkntc-migration-action-content">
                    <h4><?php echo bkntc__('Update Settings')?></h4>
                    <p><?php echo bkntc__('Configure how updates are handled for your plugin.')?></p>
                    <div class="form-group mt-2">
                        <label class="bkntc-migration-checkbox">
                            <input type="checkbox" id="bkntc-auto-check-updates" checked disabled>
                            <span class="bkntc-migration-checkbox-checkmark"></span>
                            <span class="bkntc-migration-checkbox-label"><?php echo bkntc__('Automatically check for updates')?></span>
                        </label>
                    </div>
                    <button type="button" class="bkntc-migration-button bkntc-migration-primary-button" id="bkntc-save-update-settings" disabled>
                        <i class="fas fa-save"></i> <?php echo bkntc__('Save Settings')?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>