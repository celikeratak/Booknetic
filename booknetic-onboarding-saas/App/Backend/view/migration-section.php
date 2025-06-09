<?php
defined('ABSPATH') or die('Direct access not allowed');
?>

<div class="card" id="bkntc-migration-section">
    <div class="card-header">
        <i class="fas fa-cloud-download-alt text-primary"></i>
        <h5 class="card-title"><?php echo bkntc__('Update Center')?></h5>
    </div>
    <div class="card-body">
        <div class="bkntc-migration-status-container">
            <div class="bkntc-migration-status-header">
                <h4><?php echo bkntc__('Database Compatibility Status')?></h4>
                <button type="button" class="bkntc-migration-refresh-btn" id="bkntc-refresh-migration-status">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div class="bkntc-migration-status-panel">
                <div class="bkntc-migration-status-info">
                    <div class="bkntc-migration-version-info">
                        <div class="bkntc-migration-version-item">
                            <span class="bkntc-migration-version-label"><?php echo bkntc__('Current Version:')?></span>
                            <span class="bkntc-migration-version-badge bkntc-migration-current-version" id="bkntc-current-db-version">-</span>
                        </div>
                        <div class="bkntc-migration-version-divider">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="bkntc-migration-version-item">
                            <span class="bkntc-migration-version-label"><?php echo bkntc__('Latest Version:')?></span>
                            <span class="bkntc-migration-version-badge bkntc-migration-latest-version" id="bkntc-latest-db-version">-</span>
                        </div>
                    </div>
                    
                    <div class="bkntc-migration-status-indicator" id="bkntc-migration-status-indicator">
                        <div class="bkntc-migration-status-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="bkntc-migration-status-text">
                            <h5 id="bkntc-migration-status-title"><?php echo bkntc__('Checking status...')?></h5>
                            <p id="bkntc-migration-status-description"><?php echo bkntc__('Please wait while we check your database status.')?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bkntc-migration-progress-container">
                    <div class="bkntc-migration-progress-bar" id="bkntc-migration-progress-bar" style="width: 0%;">
                        <span id="bkntc-migration-progress-text">0%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bkntc-migration-actions">
            <div class="bkntc-migration-action-card">
                <div class="bkntc-migration-action-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="bkntc-migration-action-content">
                    <h4><?php echo bkntc__('Run Migration')?></h4>
                    <p><?php echo bkntc__('Update your database structure to the latest version.')?></p>
                    <div class="bkntc-migration-checkbox-container">
                        <label class="bkntc-migration-checkbox">
                            <input type="checkbox" id="bkntc-backup-before-migration" checked>
                            <span class="bkntc-migration-checkbox-checkmark"></span>
                            <span class="bkntc-migration-checkbox-label"><?php echo bkntc__('Create backup before migration')?></span>
                        </label>
                        <div class="bkntc-migration-checkbox-hint"><?php echo bkntc__('Recommended for safety')?></div>
                    </div>
                    <button type="button" class="bkntc-migration-button bkntc-migration-primary-button" id="bkntc-run-migration">
                        <i class="fas fa-play-circle"></i> <?php echo bkntc__('Run Migration')?>
                    </button>
                </div>
            </div>
            
            <div class="bkntc-migration-action-card">
                <div class="bkntc-migration-action-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="bkntc-migration-action-content">
                    <h4><?php echo bkntc__('Backup Management')?></h4>
                    <p><?php echo bkntc__('Create and manage database backups.')?></p>
                    <button type="button" class="bkntc-migration-button bkntc-migration-primary-button" id="bkntc-create-backup">
                        <i class="fas fa-download"></i> <?php echo bkntc__('Create Backup')?>
                    </button>
                    <button type="button" class="bkntc-migration-button bkntc-migration-secondary-button" id="bkntc-view-backups">
                        <i class="fas fa-list"></i> <?php echo bkntc__('View Backups')?>
                    </button>
                </div>
            </div>
        <div class="bkntc-migration-result" id="bkntc-migration-result" style="display: none;">
            <div class="bkntc-migration-result-header">
                <h4 id="bkntc-migration-result-title"><?php echo bkntc__('Operation Result')?></h4>
                <button type="button" class="bkntc-migration-close-btn" id="bkntc-close-migration-result">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bkntc-migration-result-content" id="bkntc-migration-result-content"></div>
        </div>
        
        <!-- Backup List Modal -->
        <div class="bkntc-migration-modal" id="bkntc-backups-modal">
            <div class="bkntc-migration-modal-content">
                <div class="bkntc-migration-modal-header">
                    <h4><?php echo bkntc__('Database Backups')?></h4>
                    <button type="button" class="bkntc-migration-modal-close" id="bkntc-close-backups-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="bkntc-migration-modal-body">
                    <div class="bkntc-migration-backups-list" id="bkntc-backups-list">
                        <div class="bkntc-migration-loading">
                            <i class="fas fa-spinner fa-spin"></i> <?php echo bkntc__('Loading backups...')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


