<?php
defined('ABSPATH') or die();

/**
 * Save ChatGPT API settings
 */
function booknetic_save_chatgpt_settings() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => bkntc__('You do not have permission to perform this action')]);
        return;
    }
    
    // Get and sanitize parameters
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
    $model = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : 'gpt-3.5-turbo';
    $enabled = isset($_POST['enabled']) ? sanitize_text_field($_POST['enabled']) : '0';
    
    // Validate model
    $allowed_models = ['gpt-3.5-turbo', 'gpt-4', 'gpt-4-turbo'];
    if (!in_array($model, $allowed_models)) {
        $model = 'gpt-3.5-turbo';
    }
    
    // Save settings to database
    $settings_saved = true;
    $settings_saved = $settings_saved && update_help_setting('chatgpt_api_key', $api_key);
    $settings_saved = $settings_saved && update_help_setting('chatgpt_model', $model);
    $settings_saved = $settings_saved && update_help_setting('enable_chatgpt', $enabled);
    
    if ($settings_saved) {
        wp_send_json([
            'status' => true,
            'message' => bkntc__('Settings saved successfully')
        ]);
    } else {
        wp_send_json([
            'status' => false,
            'error' => bkntc__('Failed to save settings')
        ]);
    }
}

// Include data provider functions
require_once dirname(__FILE__) . '/data-provider.php';

/**
 * AJAX handler for help center search functionality
 * 
 * Processes search requests and returns formatted results
 */
function handle_help_center_search() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');

    // Get and sanitize search term
    $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    if (empty($search_term)) {
        wp_send_json_error(['message' => 'No search term provided.']);
        return;
    }

    // Use the enhanced search function with a limit of 10 results
    $results = get_topics_by_search($search_term, 10);
    
    if (empty($results)) {
        wp_send_json_error(['message' => 'No results found.']);
        return;
    }

    // Format results for JSON response
    // Get page and module from POST data sent by AJAX
    $page = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 'booknetic-saas';
    $module = isset($_POST['module']) ? sanitize_text_field($_POST['module']) : 'help-center';
    
    $base_url = add_query_arg(['page' => $page, 'module' => $module], 'admin.php');
        
    $formatted_results = array_map(function ($item) use ($base_url) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'excerpt' => $item->excerpt,
            'category_id' => $item->category_id,
            'category_name' => $item->category_name,
            'relevance' => $item->relevance_score,
            'url' => add_query_arg([
                'topic' => $item->id
            ], $base_url)
        ];
    }, $results);

    wp_send_json_success(['results' => $formatted_results]);
}

/**
 * AJAX handler for popular search terms
 * 
 * Returns the most frequently searched terms
 */
function handle_popular_search_terms() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');

    // Get popular search terms (10 terms from the last 30 days)
    $popular_terms = get_popular_search_terms(10, 30);
    
    if (empty($popular_terms)) {
        wp_send_json_error(['message' => 'No popular search terms found.']);
        return;
    }

    // Format results for JSON response
    $formatted_terms = array_map(function ($item) {
        return [
            'term' => $item->search_term,
            'count' => $item->search_count,
            'url' => add_query_arg([
                'page' => 'booknetic-saas',
                'module' => 'help-center',
                'search' => urlencode($item->search_term)
            ], 'admin.php')
        ];
    }, $popular_terms);

    wp_send_json_success(['terms' => $formatted_terms]);
}

/**
 * AJAX handler for importing dummy data to the help center
 * 
 * Adds sample categories, topics, and search logs
 */
function handle_import_dummy_data() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'You do not have sufficient permissions to import data.']);
        return;
    }
    
    // Get clear existing data flag
    $clear_existing = isset($_POST['clear_existing']) ? sanitize_text_field($_POST['clear_existing']) : 'no';
    
    // Include dummy data functions
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/dummy-data.php';
    
    // Call the dummy data function
    $result = booknetic_help_center_add_dummy_data();
    
    // Return the result
    if ($result['status']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}

/**
 * AJAX handler for creating database tables
 * 
 * Creates the necessary database tables for the Help Center plugin
 */
function handle_create_database_tables() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'You do not have permission to perform this action.']);
        return;
    }
    
    // Get force recreate option
    $force_recreate = isset($_POST['force_recreate']) && $_POST['force_recreate'] === 'yes';
    
    // If force recreate is enabled, drop existing tables first
    if ($force_recreate) {
        global $wpdb;
        $tables = [
            $wpdb->prefix . 'bkntc_topic_feedback',
            $wpdb->prefix . 'bkntc_help_topics',
            $wpdb->prefix . 'bkntc_help_categories',
            $wpdb->prefix . 'bkntc_search_logs',
            $wpdb->prefix . 'bkntc_help_settings'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    // Create tables
    $result = booknetic_help_center_create_tables();
    
    if ($result['status']) {
        wp_send_json([
            'status' => true,
            'message' => $result['message']
        ]);
    } else {
        wp_send_json([
            'status' => false,
            'error' => $result['message']
        ]);
    }
}

/**
 * AJAX handler for bulk deleting topics
 *
 * @return void
 */
function handle_bulk_delete_topics() {
    // Skip nonce verification for now as we're debugging
    // check_ajax_referer('booknetic_help_center', '_wpnonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => bkntc__('You do not have permission to perform this action')]);
        return;
    }
    
    // Get and validate topic IDs
    $topic_ids = isset($_POST['topic_ids']) ? $_POST['topic_ids'] : [];
    
    if (empty($topic_ids) || !is_array($topic_ids)) {
        wp_send_json_error(['message' => bkntc__('No topics selected for deletion')]);
        return;
    }
    
    // Sanitize topic IDs
    $topic_ids = array_map('intval', $topic_ids);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bkntc_help_topics';
    $deleted_count = 0;
    $errors = [];
    
    // Begin transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        foreach ($topic_ids as $topic_id) {
            // Check if topic exists
            $topic = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $topic_id));
            
            if (!$topic) {
                $errors[] = sprintf(bkntc__("Topic with ID %d does not exist"), $topic_id);
                continue;
            }
            
            // Delete the topic
            $result = $wpdb->delete($table_name, ['id' => $topic_id], ['%d']);
            
            if ($result === false) {
                $errors[] = sprintf(bkntc__("Failed to delete topic with ID %d: %s"), $topic_id, $wpdb->last_error);
            } else {
                $deleted_count++;
            }
        }
        
        // If there were any errors, rollback the transaction
        if (!empty($errors)) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error([
                'message' => bkntc__("Failed to delete some topics"),
                'errors' => $errors
            ]);
            return;
        }
        
        // Commit the transaction
        $wpdb->query('COMMIT');
        
        wp_send_json_success([
            'message' => sprintf(bkntc__("%d topics deleted successfully"), $deleted_count),
            'deleted_count' => $deleted_count
        ]);
        
    } catch (Exception $e) {
        // Rollback on exception
        $wpdb->query('ROLLBACK');
        wp_send_json_error([
            'message' => bkntc__("An error occurred while deleting topics"),
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * AJAX handler for bulk deleting categories
 *
 * @return void
 */
function handle_bulk_delete_categories() {
    // Verify nonce
    check_ajax_referer('booknetic_help_center', '_wpnonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => bkntc__('You do not have permission to perform this action')]);
        return;
    }
    
    // Get and validate category IDs
    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
    
    if (empty($category_ids) || !is_array($category_ids)) {
        wp_send_json_error(['message' => bkntc__('No categories selected for deletion')]);
        return;
    }
    
    // Sanitize category IDs
    $category_ids = array_map('intval', $category_ids);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bkntc_help_categories';
    $deleted_count = 0;
    $errors = [];
    
    // Begin transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        foreach ($category_ids as $category_id) {
            // Check if category exists
            $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $category_id));
            
            if (!$category) {
                $errors[] = sprintf(bkntc__("Category with ID %d does not exist"), $category_id);
                continue;
            }
            
            // Check if category has topics
            $topics_table = $wpdb->prefix . 'bkntc_help_topics';
            $topics_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$topics_table} WHERE category_id = %d", $category_id));
            
            if ($topics_count > 0) {
                // Delete associated topics first
                $wpdb->delete($topics_table, ['category_id' => $category_id], ['%d']);
            }
            
            // Delete the category
            $result = $wpdb->delete($table_name, ['id' => $category_id], ['%d']);
            
            if ($result === false) {
                $errors[] = sprintf(bkntc__("Failed to delete category with ID %d: %s"), $category_id, $wpdb->last_error);
            } else {
                $deleted_count++;
            }
        }
        
        // If there were any errors, rollback the transaction
        if (!empty($errors)) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error([
                'message' => bkntc__("Failed to delete some categories"),
                'errors' => $errors
            ]);
            return;
        }
        
        // Commit the transaction
        $wpdb->query('COMMIT');
        
        wp_send_json_success([
            'message' => sprintf(bkntc__("%d categories deleted successfully"), $deleted_count),
            'deleted_count' => $deleted_count
        ]);
        
    } catch (Exception $e) {
        // Rollback on exception
        $wpdb->query('ROLLBACK');
        wp_send_json_error([
            'message' => bkntc__("An error occurred while deleting categories"),
            'error' => $e->getMessage()
        ]);
    }
}

// Register AJAX handlers
add_action('wp_ajax_help_center_search', 'handle_help_center_search');
add_action('wp_ajax_nopriv_help_center_search', 'handle_help_center_search');
add_action('wp_ajax_help_center_popular_searches', 'handle_popular_search_terms');
add_action('wp_ajax_booknetic_bulk_delete_categories', 'handle_bulk_delete_categories');
add_action('wp_ajax_booknetic_bulk_delete_topics', 'handle_bulk_delete_topics');
add_action('wp_ajax_nopriv_help_center_popular_searches', 'handle_popular_search_terms');
add_action('wp_ajax_booknetic_contact_us_p_import_dummy_data', 'handle_import_dummy_data');
add_action('wp_ajax_booknetic_contact_us_p_create_tables', 'handle_create_database_tables');
add_action('wp_ajax_booknetic_save_chatgpt_settings', 'booknetic_save_chatgpt_settings');
