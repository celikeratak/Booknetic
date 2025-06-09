<?php
namespace BookneticApp\Backend;

defined('ABSPATH') or die();


global $wpdb;


/**
 * Class Migration
 * Handles database migrations for the Booknetic Onboarding SaaS plugin
 */
class Migration
{
    /**
     * Current database version
     */
    const CURRENT_VERSION = '3.0.0';

    /**
     * Option name for storing the database version
     */
    const VERSION_OPTION = 'booknetic_onboarding_saas_db_version';

    /**
     * Check if migrations are needed
     *
     * @return array Migration status information
     */
    public static function checkStatus()
    {
        // Get current installed version
        $installed_version = get_option(self::VERSION_OPTION, '1.0.0');
        
        // Compare versions
        $migrations_needed = version_compare($installed_version, self::CURRENT_VERSION, '<');
        
        return [
            'migrations_needed' => $migrations_needed,
            'installed_version' => $installed_version,
            'latest_version' => self::CURRENT_VERSION,
            'pending_migrations' => $migrations_needed ? self::getPendingMigrations($installed_version) : 0
        ];
    }

    /**
     * Get the number of pending migrations
     *
     * @param string $installed_version Currently installed version
     * @return int Number of pending migrations
     */
    private static function getPendingMigrations($installed_version)
    {
        $migrations = self::getMigrations();
        $pending = 0;
        
        foreach ($migrations as $version => $migration) {
            if (version_compare($installed_version, $version, '<')) {
                $pending++;
            }
        }
        
        return $pending;
    }

    /**
     * Run database migrations
     *
     * @param bool $create_backup Whether to create a database backup before migration
     * @return array Migration result
     */
    public static function runMigrations($create_backup = true)
    {
        global $wpdb;
        
        // Get current installed version
        $installed_version = get_option(self::VERSION_OPTION, '1.0.0');
        
        // Check if migrations are needed
        if (version_compare($installed_version, self::CURRENT_VERSION, '>=')) {
            return [
                'success' => true,
                'message' => 'Database is already up to date.',
                'migrations_applied' => 0,
                'backup_created' => false,
                'applied_migrations' => []
            ];
        }
        
        // Create backup if requested
        $backup_created = false;
        if ($create_backup) {
            $backup_created = self::createBackup();
            
            if (!$backup_created) {
                return [
                    'success' => false,
                    'message' => 'Failed to create database backup. Migration aborted.',
                    'migrations_applied' => 0,
                    'backup_created' => false,
                    'applied_migrations' => []
                ];
            }
        }
        
        // Get migrations
        $migrations = self::getMigrations();
        $applied_migrations = [];
        $migrations_applied = 0;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Apply each migration
            foreach ($migrations as $version => $migration) {
                if (version_compare($installed_version, $version, '<')) {
                    // Run migration
                    $result = call_user_func($migration);
                    
                    if (!$result['success']) {
                        // Migration failed, rollback transaction
                        $wpdb->query('ROLLBACK');
                        
                        return [
                            'success' => false,
                            'message' => 'Migration to version ' . $version . ' failed: ' . $result['message'],
                            'migrations_applied' => $migrations_applied,
                            'backup_created' => $backup_created,
                            'applied_migrations' => $applied_migrations
                        ];
                    }
                    
                    // Migration successful
                    $migrations_applied++;
                    $applied_migrations[] = $result['message'];
                }
            }
            
            // Update version
            update_option(self::VERSION_OPTION, self::CURRENT_VERSION);
            
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return [
                'success' => true,
                'message' => 'Database migration completed successfully.',
                'migrations_applied' => $migrations_applied,
                'backup_created' => $backup_created,
                'applied_migrations' => $applied_migrations
            ];
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            
            return [
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'migrations_applied' => 0,
                'backup_created' => $backup_created,
                'applied_migrations' => []
            ];
        }
    }

    
    /**
     * Create a database backup
     *
     * @return array Backup creation result
     */
    public static function createBackup()
    {
        global $wpdb;
        
        // Get tables to backup
        $tables = [
            $wpdb->prefix . 'bkntc_help_categories',
            $wpdb->prefix . 'bkntc_help_topics',
            $wpdb->prefix . 'bkntc_topic_feedback',
            $wpdb->prefix . 'bkntc_search_logs',
            $wpdb->prefix . 'bkntc_help_settings',
            $wpdb->prefix . 'bkntc_help_attachments',
            $wpdb->prefix . 'bkntc_livechat',
            $wpdb->prefix . 'bkntc_social_media',
            $wpdb->prefix . 'bkntc_support_link'
        ];
        
        // Create backup directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/booknetic-backups';
        
        if (!file_exists($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
                return [
                    'success' => false,
                    'message' => 'Failed to create backup directory.'
                ];
            }
        }
        
        // Create .htaccess file to protect backups
        $htaccess_file = $backup_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteRule .* - [F,L]\n</IfModule>\nDeny from all";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Generate backup ID and filename
        $backup_id = uniqid('bkntc_backup_');
        $timestamp = current_time('Y-m-d_H-i-s');
        $backup_file = $backup_dir . '/' . $backup_id . '_' . $timestamp . '.sql';
        
        // Open backup file for writing
        $handle = fopen($backup_file, 'w');
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Failed to create backup file.'
            ];
        }
        
        // Write header to backup file
        fwrite($handle, "-- Booknetic Onboarding SaaS Database Backup\n");
        fwrite($handle, "-- Generated: " . current_time('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Version: " . self::CURRENT_VERSION . "\n\n");
        
        // Backup each table
        foreach ($tables as $table) {
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}';");
            if (!$table_exists) {
                continue; // Skip table if it doesn't exist
            }
            
            // Get table structure
            $create_table = $wpdb->get_row("SHOW CREATE TABLE {$table}", ARRAY_N);
            if (empty($create_table[1])) {
                continue; // Skip table if structure can't be retrieved
            }
            
            // Write table structure to backup file
            fwrite($handle, "\n-- Table structure for table `{$table}`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $create_table[1] . ";\n\n");
            
            // Get table data
            $rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
            if (!empty($rows)) {
                // Get column names
                $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table}", ARRAY_N);
                $column_names = [];
                foreach ($columns as $column) {
                    $column_names[] = "`{$column[0]}`";
                }
                
                // Write table data to backup file
                fwrite($handle, "-- Data for table `{$table}`\n");
                fwrite($handle, "INSERT INTO `{$table}` (" . implode(", ", $column_names) . ") VALUES\n");
                
                // Write rows
                $row_count = count($rows);
                foreach ($rows as $i => $row) {
                    // Escape and quote values
                    $values = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . esc_sql($value) . "'";
                        }
                    }
                    
                    // Write row
                    fwrite($handle, "(" . implode(", ", $values) . ")" . ($i < $row_count - 1 ? "," : "") . "\n");
                }
                
                fwrite($handle, ";\n");
            }
        }
        
        // Close backup file
        fclose($handle);
        
        // Store backup metadata
        $backups = get_option('booknetic_onboarding_saas_backups', []);
        
        
        // Create backup metadata
        $backup_metadata = [
            'id' => $backup_id,
            'name' => 'Backup ' . date('Y-m-d H:i:s'),  // Adding a name field for display
            'filename' => basename($backup_file),
            'date' => current_time('Y-m-d H:i:s'),
            'version' => self::CURRENT_VERSION,
            'size' => self::formatSize(filesize($backup_file)),  // Format size for display
            'tables' => count($tables)
        ];
        
        // Add to backups array
        $backups[$backup_id] = $backup_metadata;
        
        // Update option
        $update_result = update_option('booknetic_onboarding_saas_backups', $backups);
        
        
        return [
            'success' => true,
            'message' => 'Backup created successfully.',
            'backup_id' => $backup_id
        ];
    }

    /**
     * Get available migrations
     *
     * @return array Migrations with version as key and callback as value
     */
    private static function getMigrations()
    {
        return [
            '3.0.0' => [self::class, 'migration_3_0_0']
            // Add future migrations here with their version numbers
            // '2.0.0' => [self::class, 'migration_4_0_0']
        ];
    }
    
    /**
     * Format file size to human readable format
     *
     * @param int $bytes File size in bytes
     * @param int $precision Precision of the result
     * @return string Formatted file size
     */
    private static function formatSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get list of backups
     *
     * @return array List of backups
     */
    public static function getBackups()
    {
        // Get backups from options
        $backups = get_option('booknetic_onboarding_saas_backups', []);
        
        
        // Sort backups by date (newest first)
        uasort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Get backup file path
     *
     * @param string $backup_id Backup ID
     * @return string Backup file path
     */
    public static function getBackupFilePath($backup_id)
    {
        // Get backups from options
        $backups = get_option('booknetic_onboarding_saas_backups', []);
        
        // Check if backup exists
        if (!isset($backups[$backup_id])) {
            throw new \Exception('Backup not found.');
        }
        
        // Get backup file path
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/booknetic-backups';
        $backup_file = $backup_dir . '/' . $backups[$backup_id]['filename'];
        
        return $backup_file;
    }
    
    /**
     * Restore database from backup
     *
     * @param string $backup_id Backup ID
     * @return array Restore result
     */
    public static function restoreBackup($backup_id)
    {
        global $wpdb;
        
        try {
            // Get backup file path
            $backup_file = self::getBackupFilePath($backup_id);
            
            // Check if file exists
            if (!file_exists($backup_file)) {
                return [
                    'success' => false,
                    'message' => 'Backup file not found.'
                ];
            }
            
            // Read backup file
            $sql = file_get_contents($backup_file);
            if (!$sql) {
                return [
                    'success' => false,
                    'message' => 'Failed to read backup file.'
                ];
            }
            
            // Split SQL into statements
            $statements = explode(';', $sql);
            
            // Start transaction
            $wpdb->query('START TRANSACTION');
            
            // Execute each statement
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                $result = $wpdb->query($statement);
                if ($result === false) {
                    // Statement failed, rollback transaction
                    $wpdb->query('ROLLBACK');
                    
                    return [
                        'success' => false,
                        'message' => 'Failed to execute SQL statement: ' . $wpdb->last_error
                    ];
                }
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return [
                'success' => true,
                'message' => 'Database restored successfully.'
            ];
        } catch (\Exception $e) {
            // Rollback transaction if it was started
            $wpdb->query('ROLLBACK');
            
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a backup
     *
     * @param string $backup_id Backup ID
     * @return array Delete result
     */
    public static function deleteBackup($backup_id)
    {
        try {
            // Get backup file path
            $backup_file = self::getBackupFilePath($backup_id);
            
            // Delete backup file
            if (file_exists($backup_file)) {
                if (!unlink($backup_file)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to delete backup file.'
                    ];
                }
            }
            
            // Remove backup from options
            $backups = get_option('booknetic_onboarding_saas_backups', []);
            unset($backups[$backup_id]);
            update_option('booknetic_onboarding_saas_backups', $backups);
            
            return [
                'success' => true,
                'message' => 'Backup deleted successfully.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Migration to version 3.0.0
     * 
     * @return array Migration result
     */
    public static function migration_3_0_0()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $messages = [];
        
        // PART 1: CHECK AND ADD COLUMNS TO EXISTING TABLES
        
        // Check if help_topics table exists and add columns if needed
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_help_topics'");
        
        if ($table_exists) {
            // Table exists, check if views column exists
            $column_exists = $wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}bkntc_help_topics` LIKE 'views'");
            
            if (!$column_exists) {
                $wpdb->query("ALTER TABLE `{$wpdb->prefix}bkntc_help_topics` ADD COLUMN `views` INT NOT NULL DEFAULT 0 AFTER `content`");
                
                if ($wpdb->last_error) {
                    return [
                        'success' => false,
                        'message' => 'Failed to add views column: ' . $wpdb->last_error
                    ];
                }
                
                $messages[] = 'Added views column to help_topics table';
            }
            
            // Check if created_at column exists
            $column_exists = $wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}bkntc_help_topics` LIKE 'created_at'");
            
            if (!$column_exists) {
                $wpdb->query("ALTER TABLE `{$wpdb->prefix}bkntc_help_topics` ADD COLUMN `created_at` DATETIME DEFAULT NULL AFTER `views`");
                
                if ($wpdb->last_error) {
                    return [
                        'success' => false,
                        'message' => 'Failed to add created_at column: ' . $wpdb->last_error
                    ];
                }
                
                // Set created_at for existing topics
                $wpdb->query("UPDATE `{$wpdb->prefix}bkntc_help_topics` SET `created_at` = NOW() WHERE `created_at` IS NULL");
                
                $messages[] = 'Added created_at column to help_topics table';
            }
        }
        
        // PART 2: CREATE NEW TABLES
        
        // Create help_topics table if it doesn't exist
        if (!$table_exists) {
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_help_topics` (
                `id` int NOT NULL AUTO_INCREMENT,
                `category_id` int NOT NULL,
                `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                `views` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME DEFAULT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`),
                KEY `category_id` (`category_id`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                return [
                    'success' => false,
                    'message' => 'Failed to create help_topics table: ' . $wpdb->last_error
                ];
            }
            
            $messages[] = 'Created help_topics table with all required columns';
        }
        
        // Create help_categories table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_help_categories'");
        
        if (!$table_exists) {
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_help_categories` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                return [
                    'success' => false,
                    'message' => 'Failed to create help_categories table: ' . $wpdb->last_error
                ];
            }
            
            $messages[] = 'Created help_categories table';
        }

        // Create topic_feedback table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_topic_feedback'");
        
        if (!$table_exists) {
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_topic_feedback` (
                `id` int NOT NULL AUTO_INCREMENT,
                `topic_id` int NOT NULL,
                `feedback` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `user_id` bigint DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `topic_id` (`topic_id`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                return [
                    'success' => false,
                    'message' => 'Failed to create topic_feedback table: ' . $wpdb->last_error
                ];
            }
            
            $messages[] = 'Created topic_feedback table';
        }
        
        // Create search_logs table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_search_logs'");
        
        if (!$table_exists) {
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_search_logs` (
                `id` bigint NOT NULL AUTO_INCREMENT,
                `search_term` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `results_count` int NOT NULL DEFAULT '0',
                `user_id` bigint DEFAULT NULL,
                `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `search_term` (`search_term`),
                KEY `created_at` (`created_at`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                return [
                    'success' => false,
                    'message' => 'Failed to create search_logs table: ' . $wpdb->last_error
                ];
            }
            
            $messages[] = 'Created search_logs table';
        }
        
        // Create help_attachments table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_help_attachments'");
        
        if (!$table_exists) {
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_help_attachments` (
                `id` int NOT NULL AUTO_INCREMENT,
                `topic_id` int DEFAULT NULL,
                `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `file_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `file_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `file_size` int NOT NULL,
                `uploaded_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `topic_id` (`topic_id`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                return [
                    'success' => false,
                    'message' => 'Failed to create help_attachments table: ' . $wpdb->last_error
                ];
            }
            
            $messages[] = 'Created help_attachments table';
        }
        
        // Create help_settings table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_help_settings'");
        
        if (!$table_exists) {
            // Log that we're creating the help_settings table
            error_log('Creating help_settings table');
            
            $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bkntc_help_settings` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `option_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `option_name` (`option_name`)
            ) $charset_collate");
            
            if ($wpdb->last_error) {
                error_log('Failed to create help_settings table: ' . $wpdb->last_error);
                return [
                    'success' => false,
                    'message' => 'Failed to create help_settings table: ' . $wpdb->last_error
                ];
            }
            
            // Add default settings
            $wpdb->query("INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) VALUES ('help_center_title', 'Help Center')");
            $wpdb->query("INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) VALUES ('help_center_subtitle', 'Find answers to your questions')");
            
            $messages[] = 'Created help_settings table with default settings';
        } else {
            $messages[] = 'Help settings table already exists';
        }
        
        // PART 3: MIGRATE DATA FROM OLD TABLES TO NEW TABLES
        
        // Migrate data from old tables to new help_settings table
        
        // 1. Migrate support_link data
        $support_link_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_support_link'");
        if ($support_link_exists) {
            $support_link_data = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}bkntc_support_link` LIMIT 1", ARRAY_A);
            
            if ($support_link_data) {
                // Create stdClass object for serialization
                $support_link_obj = new \stdClass();
                $support_link_obj->label = isset($support_link_data['label']) ? $support_link_data['label'] : 'Support';
                $support_link_obj->url = isset($support_link_data['url']) ? $support_link_data['url'] : 'https://support.booknetic.com';
                $support_link_obj->active = isset($support_link_data['active']) ? (int)$support_link_data['active'] : 1;
                $support_link_obj->id = isset($support_link_data['id']) ? (int)$support_link_data['id'] : 1;
                
                // Serialize the object
                $serialized_data = serialize($support_link_obj);
                
                // Insert into help_settings
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                    VALUES (%s, %s) 
                    ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                    'support_link',
                    $serialized_data
                ));
                
                if (!$wpdb->last_error) {
                    $messages[] = 'Migrated support_link data to help_settings';
                } else {
                    $messages[] = 'Failed to migrate support_link data: ' . $wpdb->last_error;
                }
            }
        }
        
        // 2. Migrate livechat data
        $livechat_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_livechat'");
        if ($livechat_exists) {
            $livechat_data = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}bkntc_livechat` LIMIT 1", ARRAY_A);
            
            if ($livechat_data) {
                // Insert title
                if (isset($livechat_data['title'])) {
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                        VALUES (%s, %s) 
                        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                        'livechat_title',
                        $livechat_data['title']
                    ));
                }
                
                // Insert subtitle/description
                if (isset($livechat_data['description'])) {
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                        VALUES (%s, %s) 
                        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                        'livechat_subtitle',
                        $livechat_data['description']
                    ));
                }
                
                // Insert content as embed code
                if (isset($livechat_data['content'])) {
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                        VALUES (%s, %s) 
                        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                        'livechat_embed_code',
                        $livechat_data['content']
                    ));
                }
                
                // Insert icon
                if (isset($livechat_data['icon'])) {
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                        VALUES (%s, %s) 
                        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                        'livechat_icon',
                        $livechat_data['icon']
                    ));
                }
                
                if (!$wpdb->last_error) {
                    $messages[] = 'Migrated livechat data to help_settings';
                } else {
                    $messages[] = 'Failed to migrate livechat data: ' . $wpdb->last_error;
                }
            }
        }
        
        // 3. Migrate social_media data
        $social_media_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_social_media'");
        if ($social_media_exists) {
            $social_media_data = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}bkntc_social_media` ORDER BY display_order ASC", ARRAY_A);
            
            if ($social_media_data && is_array($social_media_data)) {
                $social_media_array = [];
                
                foreach ($social_media_data as $index => $item) {
                    $social_obj = new \stdClass();
                    $social_obj->platform = isset($item['platform']) ? $item['platform'] : '';
                    $social_obj->icon = isset($item['icon']) ? $item['icon'] : '';
                    $social_obj->url = isset($item['url']) ? $item['url'] : '';
                    $social_obj->display_order = isset($item['display_order']) ? (int)$item['display_order'] : $index + 1;
                    $social_obj->active = isset($item['active']) ? (int)$item['active'] : 1;
                    $social_obj->id = isset($item['id']) ? (int)$item['id'] : time() + $index;
                    
                    $social_media_array[] = $social_obj;
                }
                
                // Serialize the array
                $serialized_data = serialize($social_media_array);
                
                // Insert into help_settings
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
                    VALUES (%s, %s) 
                    ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
                    'social_media_links',
                    $serialized_data
                ));
                
                if (!$wpdb->last_error) {
                    $messages[] = 'Migrated social_media data to help_settings';
                } else {
                    $messages[] = 'Failed to migrate social_media data: ' . $wpdb->last_error;
                }
            }
        }
        
        // PART 4: DROP OLD TABLES THAT ARE NO LONGER NEEDED
        
        // Tables to drop after migration
        $tables_to_drop = [
            'support_link',
            'livechat',
            'social_media'
        ];
        
        foreach ($tables_to_drop as $table) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_{$table}'");
            
            if ($table_exists) {
                // Log that we're dropping the table
                
                $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}bkntc_{$table}`");
                
                if ($wpdb->last_error) {
                    $messages[] = "Warning: Failed to drop {$table} table: {$wpdb->last_error}";
                } else {
                    $messages[] = "Dropped {$table} table successfully";
                }
            }
        }
        
        // Make sure the help_settings table exists after migration
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_help_settings'");
        if (!$table_exists) {
            error_log('Help settings table still does not exist after migration');
            return [
                'success' => false,
                'message' => 'Failed to create help_settings table during migration.'
            ];
        }
        
        return [
            'success' => true,
            'message' => !empty($messages) ? implode("\n", $messages) : 'Migration 3.0.0 completed successfully'
        ];
    }




}
