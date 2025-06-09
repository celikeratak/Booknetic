<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX action for saving feature toggles
add_action('wp_ajax_booknetic_save_feature_toggles', 'booknetic_save_feature_toggles_handler');

/**
 * AJAX handler for saving feature toggles
 */
function booknetic_save_feature_toggles_handler() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => __('You do not have permission to perform this action.', 'booknetic')]);
        return;
    }
    
    // Get individual toggle parameters
    $feedback_section = isset($_POST['feedback_section']) ? (int)$_POST['feedback_section'] : 1;
    $still_need_help = isset($_POST['still_need_help']) ? (int)$_POST['still_need_help'] : 1;
    $related_articles = isset($_POST['related_articles']) ? (int)$_POST['related_articles'] : 1;
    $livechat = isset($_POST['livechat']) ? (int)$_POST['livechat'] : 1;
    $popular_topics = isset($_POST['popular_topics']) ? (int)$_POST['popular_topics'] : 1;
    
    
    // Create toggles object
    $toggles = [
        'feedback_section' => (bool)$feedback_section,
        'still_need_help' => (bool)$still_need_help,
        'related_articles' => (bool)$related_articles,
        'livechat' => (bool)$livechat,
        'popular_topics' => (bool)$popular_topics
    ];
    
    // Validate toggle values and convert to proper boolean values
    $valid_toggles = (object)[
        'feedback_section' => isset($toggles['feedback_section']) ? (bool) $toggles['feedback_section'] : true,
        'still_need_help' => isset($toggles['still_need_help']) ? (bool) $toggles['still_need_help'] : true,
        'related_articles' => isset($toggles['related_articles']) ? (bool) $toggles['related_articles'] : true,
        'livechat' => isset($toggles['livechat']) ? (bool) $toggles['livechat'] : true,
        'popular_topics' => isset($toggles['popular_topics']) ? (bool) $toggles['popular_topics'] : true
    ];
    
    // Save feature toggles
    $result = update_help_setting('feature_toggles', $valid_toggles);
    
    if ($result) {
        wp_send_json_success([
            'status' => true,
            'message' => __('Feature toggles saved successfully!', 'booknetic')
        ]);
    } else {
        wp_send_json_error([
            'status' => false,
            'error' => __('Failed to save feature toggles.', 'booknetic')
        ]);
    }
}
