<?php
/**
 * File Upload Handler for Booknetic Onboarding SaaS
 */

defined('ABSPATH') or die();

// Check if the user has permission to access this page
if (!current_user_can('administrator')) {
    wp_send_json_error(['message' => 'Permission denied']);
    exit;
}

// Verify nonce for security
if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'upload_attachment_nonce')) {
    wp_send_json_error(['message' => 'Security check failed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
    wp_send_json_error(['message' => 'No file was uploaded']);
    exit;
}

// Define upload directory
$upload_dir = wp_upload_dir();
$target_dir = $upload_dir['basedir'] . '/booknetic-onboarding-attachments/';

// Create directory if it doesn't exist
if (!file_exists($target_dir)) {
    wp_mkdir_p($target_dir);
}

// Generate a unique filename
$file_name = $_FILES['file']['name'];
$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
$unique_name = uniqid() . '.' . $file_ext;
$target_file = $target_dir . $unique_name;

// Move the uploaded file
if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
    // File was uploaded successfully
    $file_url = $upload_dir['baseurl'] . '/booknetic-onboarding-attachments/' . $unique_name;
    
    // Store file info in database
    global $wpdb;
    $table_name = $wpdb->prefix . 'bkntc_help_attachments';
    
    // Check if the table exists, create it if it doesn't
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
    if (!$table_exists) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
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
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    $result = $wpdb->insert(
        $table_name,
        [
            'file_name' => $file_name,
            'file_path' => $target_file,
            'file_url' => $file_url,
            'file_type' => $_FILES['file']['type'],
            'file_size' => $_FILES['file']['size'],
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    );
    
    if ($result) {
        $attachment_id = $wpdb->insert_id;
        wp_send_json_success([
            'id' => $attachment_id,
            'name' => $file_name,
            'url' => $file_url,
            'type' => $_FILES['file']['type'],
            'size' => $_FILES['file']['size']
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to store file information']);
    }
} else {
    wp_send_json_error(['message' => 'Failed to upload file']);
}

exit;
