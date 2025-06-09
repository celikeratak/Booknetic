<?php
defined('ABSPATH') or die();

use function BookneticAddon\ContactUsP\bkntc__;

/**
 * Register AJAX handlers for feature toggles
 */
function register_feature_toggles_ajax_handlers() {
    add_action('wp_ajax_booknetic_save_feature_toggles', 'handle_save_feature_toggles');
}

/**
 * Handle saving feature toggles
 */
function handle_save_feature_toggles() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => bkntc__('You do not have permission to perform this action.')]);
        return;
    }
    
    // Get and sanitize parameters
    $toggles_json = isset($_POST['toggles']) ? sanitize_text_field($_POST['toggles']) : '';
    
    // Validate required parameters
    if (empty($toggles_json)) {
        wp_send_json_error(['error' => bkntc__('Required parameter missing')]);
        return;
    }
    
    // Decode JSON
    $toggles = json_decode($toggles_json, true);
    
    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['error' => bkntc__('Invalid JSON data')]);
        return;
    }
    
    // Ensure all expected toggles are present
    $default_toggles = [
        'feedback_section' => true,
        'still_need_help' => true,
        'related_articles' => true,
        'livechat' => true,
        'popular_topics' => true
    ];
    
    $toggles = array_merge($default_toggles, $toggles);
    
    // Convert boolean values
    foreach ($toggles as $key => $value) {
        $toggles[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    // Save to database
    $result = update_help_setting('feature_toggles', $toggles);
    
    if ($result) {
        wp_send_json_success([
            'status' => true,
            'message' => bkntc__('Feature toggles saved successfully!')
        ]);
    } else {
        wp_send_json_error([
            'error' => bkntc__('Failed to save feature toggles.')
        ]);
    }
}

/**
 * Get feature toggle settings
 * 
 * @param string $toggle_name Name of the toggle to check
 * @param bool $default Default value if toggle is not set
 * @return bool Toggle state
 */
function is_feature_enabled($toggle_name, $default = true) {
    $feature_toggles = get_help_setting('feature_toggles', [
        'feedback_section' => true,
        'still_need_help' => true,
        'related_articles' => true,
        'livechat' => true,
        'popular_topics' => true
    ]);
    
    // Ensure feature_toggles is an array
    if (is_string($feature_toggles)) {
        $feature_toggles = json_decode($feature_toggles, true);
    }
    
    // Handle both object and array types
    if (is_object($feature_toggles)) {
        // If it's an object, check if the property exists
        return isset($feature_toggles->$toggle_name) ? filter_var($feature_toggles->$toggle_name, FILTER_VALIDATE_BOOLEAN) : $default;
    } else if (is_array($feature_toggles)) {
        // If it's an array, check if the key exists
        return isset($feature_toggles[$toggle_name]) ? filter_var($feature_toggles[$toggle_name], FILTER_VALIDATE_BOOLEAN) : $default;
    }
    
    // If it's neither an object nor an array, return the default
    return $default;
}

// Register AJAX handlers
register_feature_toggles_ajax_handlers();
