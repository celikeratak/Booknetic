<?php
defined('ABSPATH') or die();

// Include database functions
require_once dirname(__FILE__) . '/database-functions.php';

/**
 * Prepare data for the help center page
 * 
 * @return array Prepared data for the help center page
 */
function prepare_help_center_data() {
    global $wpdb;
    
    // Set up base variables
    $base_url = isset($_GET['page'], $_GET['module']) 
    ? add_query_arg(['page' => sanitize_text_field($_GET['page']), 'module' => sanitize_text_field($_GET['module'])], 'admin.php') 
    : 'admin.php';

    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category    = isset($_GET['category']) ? $_GET['category'] : '';
    $topic_id    = isset($_GET['topic']) ? $_GET['topic'] : '';

    // Get data based on request type
    $categories = get_help_categories();
    $topics = [];
    $topic_details = null;
    $livechat = get_livechat_settings();

    // Process data based on request parameters
    if ($search_term) {
        $topics = get_topics_by_search($search_term);
    } elseif ($topic_id) {
        $topic_details = get_topic_details($topic_id);
    } elseif ($category && isset($categories[$category])) {
        $topics = get_topics_by_category($category);
    }

    // Process feedback submission if present
    $feedback_result = process_topic_feedback();
    $error_message = ($feedback_result['status'] === false) ? $feedback_result['message'] : '';
    
    // Get popular topics for the homepage
    $popular_topics = [];
    if (!$search_term && !$topic_id && !$category) {
        $popular_topics = get_popular_topics(6);
    }
    
    return [
        'base_url' => $base_url,
        'search_term' => $search_term,
        'category' => $category,
        'topic_id' => $topic_id,
        'categories' => $categories,
        'topics' => $topics,
        'topic_details' => $topic_details,
        'livechat' => $livechat,
        'error_message' => $error_message,
        'popular_topics' => $popular_topics
    ];
}
