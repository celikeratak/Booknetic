<?php
/**
 * Register AJAX actions for Booknetic Onboarding SaaS
 */

defined('ABSPATH') or die();

// Include AJAX handlers
require_once dirname(__DIR__) . '/includes/ajax-handlers.php';

// Register AJAX action for file uploads
add_action('wp_ajax_booknetic_upload_attachment', 'booknetic_upload_attachment_handler');

/**
 * Handler for file upload AJAX requests
 */
function booknetic_upload_attachment_handler() {
    require_once dirname(__FILE__) . '/upload_attachment.php';
}

// Register AJAX action for reordering categories
add_action('wp_ajax_booknetic_reorder_categories', 'booknetic_handle_reorder_categories');

// Register AJAX action for bulk deleting categories
add_action('wp_ajax_booknetic_bulk_delete_categories', 'handle_bulk_delete_categories');

/**
 * Handle the AJAX request for reordering categories
 */
function booknetic_handle_reorder_categories() {
    // Prevent any output before sending JSON response
    if (ob_get_level()) {
        ob_clean();
    }
    
    try {
        // Verify nonce
        check_ajax_referer('reorder_categories', '_wpnonce');
        
        // Validate required parameters
        if (empty($_POST['order'])) {
            wp_send_json_error(['message' => 'Required parameter missing: order']);
            exit;
        }
        
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkntc_help_categories';
        
        // First, reset all order numbers to ensure clean state
        $wpdb->query("UPDATE `{$table_name}` SET order_number = 0");
        
        // Update orders with 1-based indexing
        foreach ($_POST['order'] as $position => $category_id) {
            // Sanitize input
            $category_id = intval($category_id);
            $order_number = $position + 1; // Convert to 1-based index
            
            
            $result = $wpdb->update(
                $table_name,
                ['order_number' => $order_number],
                ['id' => $category_id],
                ['%d'],
                ['%d']
            );
            
            if ($result === false) {
                throw new Exception('Failed to update category ' . $category_id);
            }
        }
        
        wp_send_json_success(['message' => 'Order saved successfully']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
    exit;
}
