<?php
/**
 * Centralized localization data for Booknetic Help Center
 * 
 * This file contains all the translation strings used across JavaScript files
 * in the Booknetic Help Center plugin.
 */

defined('ABSPATH') or die();

use function BookneticAddon\ContactUsP\bkntc__;

/**
 * Get localization data for JavaScript
 * 
 * @param string $context The context for which to get localization data (e.g., 'topics', 'help-center')
 * @param array $additional_data Additional data to merge with the localization data
 * @return array The localization data
 */
function get_booknetic_help_localization($context = 'general', $additional_data = []) {
    // Common localization data used across all contexts
    $common_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('help_center_nonce'),
        
        // Common messages
        'loading' => bkntc__('Loading...'),
        'saving' => bkntc__('Saving...'),
        'saved_successfully' => bkntc__('Saved successfully!'),
        'failed_to_save' => bkntc__('Failed to save'),
        'invalid_response' => bkntc__('Invalid response from server'),
        'invalid_response_from_server' => bkntc__('Invalid response from server'),
        'server_error' => bkntc__('Server error'),
        'network_error' => bkntc__('Network error. Please check your internet connection.'),
        'unknown_error' => bkntc__('Unknown error'),
        'failed_to_process_request' => bkntc__('Failed to process request'),
        
        // Migration-related translations
        'checking_status' => bkntc__('Checking status...'),
        'please_wait' => bkntc__('Please wait while we check your database.'),
        'update_required' => bkntc__('Update Required'),
        'database_needs_update' => bkntc__('Your database needs to be updated to the latest version.'),
        'up_to_date' => bkntc__('Up to Date'),
        'database_up_to_date' => bkntc__('Your database is up to date.'),
        'running' => bkntc__('Running...'),
        'migration_successful' => bkntc__('Migration Successful'),
        'migration_failed' => bkntc__('Migration Failed'),
        'run_migration' => bkntc__('Run Migration'),
        'creating' => bkntc__('Creating...'),
        'backup_created' => bkntc__('Backup Created'),
        'backup_failed' => bkntc__('Backup Failed'),
        'create_backup' => bkntc__('Create Backup'),
        'loading_backups' => bkntc__('Loading Backups...'),
        'failed_to_load_backups' => bkntc__('Failed to load backups'),
        'no_backups' => bkntc__('No backups found'),
        'restore' => bkntc__('Restore'),
        'download' => bkntc__('Download'),
        'delete' => bkntc__('Delete'),
        'confirm_restore_backup' => bkntc__('Are you sure you want to restore this backup? This will replace your current database.'),
        'backup_restored' => bkntc__('Backup Restored'),
        'restore_failed' => bkntc__('Restore Failed'),
        'confirm_delete_backup' => bkntc__('Are you sure you want to delete this backup?'),
        'delete_failed' => bkntc__('Delete Failed'),
        'error' => bkntc__('Error'),
    );
    
    // Context-specific localization data
    $context_data = array();
    
    switch ($context) {
        case 'help-center':
            $context_data = array(
                // Image viewer translations
                'zoom_in' => bkntc__('Zoom In'),
                'zoom_out' => bkntc__('Zoom Out'),
                'reset_zoom' => bkntc__('Reset Zoom'),
                'previous_image' => bkntc__('Previous Image'),
                'next_image' => bkntc__('Next Image'),
                'close' => bkntc__('Close'),
            );
            break;
            
        case 'support-link':
            $context_data = array(
                'please_enter_valid_url' => bkntc__('Please enter a valid URL'),
                'saving' => bkntc__('Saving...'),
                'failed_to_save_support_link' => bkntc__('Failed to save support link'),
                'invalid_response_from_server' => bkntc__('Invalid response from server'),
                'failed_to_process_request' => bkntc__('Failed to process request'),
                'save_changes' => bkntc__('Save Changes'),
                'support_link_saved_successfully' => bkntc__('Support link saved successfully!'),
            );
            break;
            
        case 'livechat':
            $context_data = array(
                'saving' => bkntc__('Saving...'),
                'livechat_page_settings_saved_successfully' => bkntc__('Livechat page settings saved successfully!'),
                'saved_successfully' => bkntc__('Saved successfully!'),
                'failed_to_save_livechat_page_settings' => bkntc__('Failed to save livechat page settings'),
                'failed_to_save' => bkntc__('Failed to save'),
                'invalid_response_from_server' => bkntc__('Invalid response from server'),
                'failed_to_process_request' => bkntc__('Failed to process request'),
                'save_changes' => bkntc__('Save Changes'),
                'failed_to_load_livechat_page_settings' => bkntc__('Failed to load livechat page settings'),
            );
            break;
            
        case 'dashboard':
            $context_data = array(
                'search_placeholder' => bkntc__('Search for help...'),
                'no_results_found' => bkntc__('No results found'),
                'popular_topics' => bkntc__('Popular Topics'),
                'view_all_topics' => bkntc__('View All Topics'),
                'loading_results' => bkntc__('Loading results...'),
                'search_suggestions' => bkntc__('Search Suggestions'),
                'popular_searches' => bkntc__('Popular Searches'),
                'view_all_results' => bkntc__('View all results'),
                'no_results_for' => bkntc__('We couldn\'t find any results for'),
                'check_spelling' => bkntc__('Check your spelling and try again'),
                'try_general_keywords' => bkntc__('Try using more general keywords'),
                'searching' => bkntc__('Searching...'),
            );
            break;
            
        case 'manage-topics':
            $context_data = array(
                'add_new_topic' => bkntc__('Add New Topic'),
                'edit_topic' => bkntc__('Edit Topic'),
                'delete_topic' => bkntc__('Delete Topic'),
                'confirm_delete' => bkntc__('Are you sure you want to delete this topic?'),
                'topic_deleted' => bkntc__('Topic deleted successfully'),
                'search_topics' => bkntc__('Search topics...'),
                'no_topics_found' => bkntc__('No topics found'),
            );
            break;
            
        case 'manage-categories':
            $context_data = array(
                'add_new_category' => bkntc__('Add New Category'),
                'edit_category' => bkntc__('Edit Category'),
                'delete_category' => bkntc__('Delete Category'),
                'confirm_delete' => bkntc__('Are you sure you want to delete this category? All topics in this category will be moved to Uncategorized.'),
                'category_deleted' => bkntc__('Category deleted successfully'),
                'search_categories' => bkntc__('Search categories...'),
                'no_categories_found' => bkntc__('No categories found'),
            );
            break;
            
        case 'reorder-categories':
            $context_data = array(
                'drag_to_reorder' => bkntc__('Drag categories to reorder'),
                'save_order' => bkntc__('Save Order'),
                'order_saved' => bkntc__('Order saved successfully'),
                'failed_to_save_order' => bkntc__('Failed to save order'),
            );
            break;
            
        case 'menu-links':
            $context_data = array(
                'add_menu_link' => bkntc__('Add Menu Link'),
                'edit_menu_link' => bkntc__('Edit Menu Link'),
                'save_changes' => bkntc__('Save'),
                'saving' => bkntc__('Saving...'),
                'menu_link_saved_successfully' => bkntc__('Menu link saved successfully!'),
                'failed_to_save_menu_link' => bkntc__('Failed to save menu link.'),
                'menu_link_deleted_successfully' => bkntc__('Menu link deleted successfully!'),
                'failed_to_delete_menu_link' => bkntc__('Failed to delete menu link.'),
                'failed_to_update_menu_link_status' => bkntc__('Failed to update menu link status.'),
                'no_menu_links_added_yet' => bkntc__('No menu links added yet.'),
                'loading' => bkntc__('Loading...'),
                'failed_to_load_menu_links' => bkntc__('Failed to load menu links'),
                'invalid_response_from_server' => bkntc__('Invalid response from server.'),
                'failed_to_process_request' => bkntc__('Failed to process request.'),
                'confirm_delete_menu_link' => bkntc__('Are you sure you want to delete this menu link?'),
            );
            break;
            
        case 'topics':
            $context_data = array(
                'upload_nonce' => wp_create_nonce('upload_attachment_nonce'),
                'content_placeholder' => bkntc__('Enter your content here...'),
                'select_category' => bkntc__('Select Category'),
                'uploading' => bkntc__('Uploading...'),
                'upload_failed' => bkntc__('Upload failed'),
                'view_file' => bkntc__('View File'),
                'copy_link' => bkntc__('Copy Link'),
                'copied' => bkntc__('Copied!'),
                'unknown_error' => bkntc__('Unknown error'),
                'invalid_response' => bkntc__('Invalid response from server'),
                'server_error' => bkntc__('Server error'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'confirm_delete' => bkntc__('Are you sure you want to delete this attachment?'),
                
                // Translation-specific messages
                'translating' => bkntc__('Translating content...'),
                'translation_success' => bkntc__('Content translated successfully!'),
                'translation_error' => bkntc__('Failed to translate content'),
                'api_key_missing' => bkntc__('API key is missing. Please configure it in the settings.'),
                'api_error' => bkntc__('Error communicating with translation service'),
                'rate_limit_error' => bkntc__('Rate limit exceeded. Please try again later.'),
                'content_too_long' => bkntc__('Content is too long for translation. Please reduce the content size.'),
                'select_language' => bkntc__('Please select a target language'),
                'no_content' => bkntc__('Please provide content before translating'),
                
                // Translation progress messages
                'translating_message1' => bkntc__('Analyzing language structure...'),
                'translating_message2' => bkntc__('Processing semantic context...'),
                'translating_message3' => bkntc__('Adapting cultural nuances...'),
                'translating_message4' => bkntc__('Refining grammar and syntax...'),
                'translating_message5' => bkntc__('Preserving document formatting...'),
                'translating_message6' => bkntc__('Optimizing terminology...'),
                'translating_message7' => bkntc__('Ensuring natural language flow...'),
                'translating_message8' => bkntc__('Finalizing translation...'),
                'translating_message9' => bkntc__('Almost there...'),
                
                // URL extraction messages
                'extracting' => bkntc__('Extracting content...'),
                'extraction_success' => bkntc__('Content extracted successfully!'),
                'extraction_error' => bkntc__('Failed to extract content'),
                'invalid_url' => bkntc__('Please enter a valid URL')
            );
            break;
            
        case 'help-center':
            $context_data = array(
                'search_suggestions' => bkntc__('Search Suggestions'),
                'popular_searches' => bkntc__('Popular Searches'),
                'view_all_results' => bkntc__('View all results'),
                'no_results_found' => bkntc__('No results found'),
                'no_results_for' => bkntc__('We couldn\'t find any results for'),
                'check_spelling' => bkntc__('Check your spelling and try again'),
                'try_general_keywords' => bkntc__('Try using more general keywords'),
                'searching' => bkntc__('Searching...'),
                
                // Image viewer translations
                'previous_image' => bkntc__('Previous Image'),
                'next_image' => bkntc__('Next Image'),
                'close' => bkntc__('Close'),
                'zoom_in' => bkntc__('Zoom In'),
                'zoom_out' => bkntc__('Zoom Out'),
                'reset_zoom' => bkntc__('Reset Zoom')
            );
            break;
            
        case 'categories':
            $context_data = array(
                'failed_to_delete_categories' => bkntc__('Failed to delete categories.'),
                'delete_confirmation' => bkntc__('You are about to delete'),
                'delete_confirmation_end' => bkntc__('categories. This action cannot be undone.'),
                'category_saved' => bkntc__('Category saved successfully!'),
                'failed_to_save_category' => bkntc__('Failed to save category'),
                'category_deleted' => bkntc__('Category deleted successfully!'),
                'failed_to_delete_category' => bkntc__('Failed to delete category'),
                'categories_reordered' => bkntc__('Categories reordered successfully!'),
                'failed_to_reorder_categories' => bkntc__('Failed to reorder categories')
            );
            break;
            
        case 'social-media':
            $context_data = array(
                'edit_social_media_link' => bkntc__('Edit Social Media Link'),
                'add_social_media_link' => bkntc__('Add Social Media Link'),
                'delete_confirmation' => bkntc__('Are you sure you want to delete this social media link?'),
                'link_deleted' => bkntc__('Social media link deleted successfully!'),
                'failed_to_delete_link' => bkntc__('Failed to delete social media link'),
                'platform_required' => bkntc__('Platform name is required'),
                'url_required' => bkntc__('URL is required'),
                'icon_required' => bkntc__('Icon is required'),
                'invalid_url' => bkntc__('Please enter a valid URL'),
                'link_saved' => bkntc__('Social media link saved successfully!'),
                'failed_to_save_link' => bkntc__('Failed to save social media link'),
                'save' => bkntc__('Save')
            );
            break;
            
        case 'support-link':
            $context_data = array(
                'link_saved' => bkntc__('Support link saved successfully!'),
                'failed_to_save_link' => bkntc__('Failed to save support link'),
                'save_changes' => bkntc__('Save Changes')
            );
            break;
            
        case 'livechat':
            $context_data = array(
                'settings_saved' => bkntc__('Livechat page settings saved successfully!'),
                'failed_to_save_settings' => bkntc__('Failed to save livechat page settings'),
                'failed_to_load_settings' => bkntc__('Failed to load livechat page settings')
            );
            break;
    }
    
    // Merge common data with context-specific data and additional data
    return array_merge($common_data, $context_data, $additional_data);
}

/**
 * Output the JavaScript localization object
 * 
 * @param string $object_name The name of the JavaScript object to create
 * @param string $context The context for which to get localization data
 * @param array $additional_data Additional data to merge with the localization data
 */
function booknetic_help_output_localization($object_name = 'booknetic_help_i18n', $context = 'general', $additional_data = []) {
    $localization_data = get_booknetic_help_localization($context, $additional_data);
    
    echo "var {$object_name} = " . json_encode($localization_data) . ";\n";
    
    // Also create a helpCenterAjax object for AJAX requests
    echo "var helpCenterAjax = { 
        ajaxUrl: " . json_encode($localization_data['ajax_url']) . ", 
        nonce: " . json_encode($localization_data['nonce']) . " 
    };\n";
}
