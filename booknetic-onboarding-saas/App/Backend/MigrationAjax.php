<?php
namespace BookneticApp\Backend;

defined('ABSPATH') or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * Class MigrationAjax
 * Handles AJAX requests for the migration feature
 */
class MigrationAjax
{
    /**
     * Initialize AJAX handlers
     */
    public static function init()
    {
        // Register AJAX actions following Booknetic pattern
        add_action('wp_ajax_booknetic_check_migration_status', [self::class, 'checkMigrationStatus']);
        add_action('wp_ajax_booknetic_run_migration', [self::class, 'runMigration']);
        add_action('wp_ajax_booknetic_create_backup', [self::class, 'createBackup']);
        add_action('wp_ajax_booknetic_get_backups', [self::class, 'getBackups']);
        add_action('wp_ajax_booknetic_restore_backup', [self::class, 'restoreBackup']);
        add_action('wp_ajax_booknetic_delete_backup', [self::class, 'deleteBackup']);
        add_action('wp_ajax_booknetic_download_backup', [self::class, 'downloadBackup']);
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', [self::class, 'enqueueScripts']);
        
    }
    
    /**
     * Enqueue scripts for the migration feature
     */
    public static function enqueueScripts($hook)
    {
        // Only enqueue on the plugin's admin pages
        if (strpos($hook, 'booknetic-onboarding-saas') === false && strpos($hook, 'booknetic-create-tables') === false) {
            return;
        }
        
        // Enqueue CSS for enhanced migration UI
        wp_enqueue_style(
            'booknetic-migration-css',
            plugins_url('assets/backend/css/migration.css', dirname(dirname(__FILE__))),
            [],
            filemtime(plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/backend/css/migration.css')
        );
        
        // Enqueue enhanced migration script
        wp_enqueue_script(
            'booknetic-migration-js',
            plugins_url('assets/backend/js/migration.js', dirname(dirname(__FILE__))),
            ['jquery'],
            filemtime(plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/backend/js/migration.js'),
            true
        );
        
        // Localize the script with translations and settings
        wp_localize_script(
            'booknetic-migration-js',
            'BookneticSettings',
            [
                'nonce' => wp_create_nonce('help_center_nonce'),
                'translations' => [
                    'checking_status' => esc_html__('Checking status...', 'booknetic-onboarding-saas'),
                    'please_wait' => esc_html__('Please wait while we check your database.', 'booknetic-onboarding-saas'),
                    'not_installed' => esc_html__('Not Installed', 'booknetic-onboarding-saas'),
                    'database_not_installed' => esc_html__('The database has not been installed yet.', 'booknetic-onboarding-saas'),
                    'update_required' => esc_html__('Update Required', 'booknetic-onboarding-saas'),
                    'database_needs_update' => esc_html__('Your database needs to be updated to the latest version. Please click on the "Run Migration" button.', 'booknetic-onboarding-saas'),
                    'up_to_date' => esc_html__('Up to Date', 'booknetic-onboarding-saas'),
                    'database_up_to_date' => esc_html__('Your database is up to date with the latest version.', 'booknetic-onboarding-saas'),
                    'running' => esc_html__('Running...', 'booknetic-onboarding-saas'),
                    'run_migration' => esc_html__('Run Migration', 'booknetic-onboarding-saas'),
                    'creating' => esc_html__('Creating...', 'booknetic-onboarding-saas'),
                    'create_backup' => esc_html__('Create Backup', 'booknetic-onboarding-saas'),
                    'loading_backups' => esc_html__('Loading backups...', 'booknetic-onboarding-saas'),
                    'no_backups' => esc_html__('No backups found.', 'booknetic-onboarding-saas'),
                    'restore' => esc_html__('Restore', 'booknetic-onboarding-saas'),
                    'download' => esc_html__('Download', 'booknetic-onboarding-saas'),
                    'delete' => esc_html__('Delete', 'booknetic-onboarding-saas'),
                    'confirm_restore_backup' => esc_html__('Are you sure you want to restore this backup? This will overwrite your current database.', 'booknetic-onboarding-saas'),
                    'confirm_delete_backup' => esc_html__('Are you sure you want to delete this backup?', 'booknetic-onboarding-saas'),
                    'backup_restored' => esc_html__('Backup Restored', 'booknetic-onboarding-saas'),
                    'backup_created' => esc_html__('Backup Created', 'booknetic-onboarding-saas'),
                    'backup_failed' => esc_html__('Backup Failed', 'booknetic-onboarding-saas'),
                    'restore_failed' => esc_html__('Restore Failed', 'booknetic-onboarding-saas'),
                    'delete_failed' => esc_html__('Delete Failed', 'booknetic-onboarding-saas'),
                    'migration_successful' => esc_html__('Migration Successful', 'booknetic-onboarding-saas'),
                    'migration_failed' => esc_html__('Migration Failed', 'booknetic-onboarding-saas'),
                    'error' => esc_html__('Error', 'booknetic-onboarding-saas'),
                    'unknown_error' => esc_html__('An unknown error occurred.', 'booknetic-onboarding-saas'),
                    'invalid_response' => esc_html__('Invalid response from server.', 'booknetic-onboarding-saas'),
                    'server_error' => esc_html__('Server error. Please try again.', 'booknetic-onboarding-saas'),
                    'failed_to_load_backups' => esc_html__('Failed to load backups.', 'booknetic-onboarding-saas')
                ]
            ]
        );
    }
    
    /**
     * AJAX handler for checking migration status
     * Following Booknetic AJAX implementation pattern
     */
    public static function checkMigrationStatus()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        try {
            // Check migration status
            $status = Migration::checkStatus();
            
            // Return status following Booknetic pattern
            wp_send_json([
                'status' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for running migrations
     * Following Booknetic AJAX implementation pattern
     */
    public static function runMigration()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        // Get and sanitize parameters
        $create_backup = isset($_POST['create_backup']) ? filter_var($_POST['create_backup'], FILTER_VALIDATE_BOOLEAN) : true;
        
        try {
            // Run migrations
            $result = Migration::runMigrations($create_backup);
            
            // Check if migration was successful, following Booknetic pattern
            if ($result['success']) {
                wp_send_json([
                    'status' => true,
                    'data' => $result
                ]);
            } else {
                wp_send_json([
                    'status' => false,
                    'error' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for creating database backup
     * Following Booknetic AJAX implementation pattern
     */
    public static function createBackup()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        try {
            // Create backup
            $result = Migration::createBackup();
            
            if ($result['success']) {
                wp_send_json([
                    'status' => true,
                    'data' => [
                        'message' => esc_html__('Database backup created successfully.', 'booknetic-onboarding-saas'),
                        'backup_id' => $result['backup_id'] ?? ''
                    ]
                ]);
            } else {
                wp_send_json([
                    'status' => false,
                    'error' => $result['message'] ?? esc_html__('Failed to create backup.', 'booknetic-onboarding-saas')
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for getting list of backups
     * Following Booknetic AJAX implementation pattern
     */
    public static function getBackups()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        try {
            // Get backups
            $backups = Migration::getBackups();
            
            
            wp_send_json([
                'status' => true,
                'data' => [
                    'backups' => $backups
                ]
            ]);
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for restoring database from backup
     * Following Booknetic AJAX implementation pattern
     */
    public static function restoreBackup()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        // Get and sanitize parameters
        $backup_id = isset($_POST['backup_id']) ? sanitize_text_field($_POST['backup_id']) : '';
        
        // Validate required parameters
        if (empty($backup_id)) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('Backup ID is required.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        try {
            // Restore backup
            $result = Migration::restoreBackup($backup_id);
            
            if ($result['success']) {
                wp_send_json([
                    'status' => true,
                    'data' => [
                        'message' => esc_html__('Database restored successfully.', 'booknetic-onboarding-saas')
                    ]
                ]);
            } else {
                wp_send_json([
                    'status' => false,
                    'error' => $result['message'] ?? esc_html__('Failed to restore backup.', 'booknetic-onboarding-saas')
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for deleting a backup
     * Following Booknetic AJAX implementation pattern
     */
    public static function deleteBackup()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        // Get and sanitize parameters
        $backup_id = isset($_POST['backup_id']) ? sanitize_text_field($_POST['backup_id']) : '';
        
        // Validate required parameters
        if (empty($backup_id)) {
            wp_send_json([
                'status' => false,
                'error' => esc_html__('Backup ID is required.', 'booknetic-onboarding-saas')
            ]);
            return;
        }
        
        try {
            // Delete backup
            $result = Migration::deleteBackup($backup_id);
            
            if ($result['success']) {
                wp_send_json([
                    'status' => true,
                    'data' => [
                        'message' => esc_html__('Backup deleted successfully.', 'booknetic-onboarding-saas')
                    ]
                ]);
            } else {
                wp_send_json([
                    'status' => false,
                    'error' => $result['message'] ?? esc_html__('Failed to delete backup.', 'booknetic-onboarding-saas')
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * AJAX handler for downloading a backup
     * Following Booknetic AJAX implementation pattern
     */
    public static function downloadBackup()
    {
        // Verify nonce
        check_ajax_referer('help_center_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'booknetic-onboarding-saas'));
            return;
        }
        
        // Get and sanitize parameters
        $backup_id = isset($_GET['backup_id']) ? sanitize_text_field($_GET['backup_id']) : '';
        
        // Validate required parameters
        if (empty($backup_id)) {
            wp_die(esc_html__('Backup ID is required.', 'booknetic-onboarding-saas'));
            return;
        }
        
        try {
            // Get backup file path
            $backup_file = Migration::getBackupFilePath($backup_id);
            
            if (!file_exists($backup_file)) {
                wp_die(esc_html__('Backup file not found.', 'booknetic-onboarding-saas'));
                return;
            }
            
            // Set headers for download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backup_file));
            
            // Clear output buffer
            ob_clean();
            flush();
            
            // Read file and output to browser
            readfile($backup_file);
            exit;
        } catch (\Exception $e) {
            wp_die($e->getMessage());
        }
    }
}
