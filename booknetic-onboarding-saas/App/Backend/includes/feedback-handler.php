<?php
defined('ABSPATH') or die();

/**
 * Get topic feedback statistics
 * 
 * @param int $topic_id The topic ID
 * @return object|null Statistics object or null
 */
function get_topic_feedback_stats($topic_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'bkntc_topic_feedback';
    
    $stats = $wpdb->get_row($wpdb->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) as helpful
        FROM {$table}
        WHERE topic_id = %d
    ", $topic_id));
    
    return $stats;
}

/**
 * Process topic feedback submission
 * 
 * @return array Result with status and message
 */
function process_topic_feedback() {
    if (!isset($_POST['submit_feedback'])) {
        return ['status' => false, 'message' => ''];
    }
    
    try {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            throw new Exception('Please log in to submit feedback');
        }

        // Verify nonce
        if (!isset($_POST['feedback_nonce']) || !wp_verify_nonce($_POST['feedback_nonce'], 'topic_feedback_action')) {
            throw new Exception('Invalid request');
        }

        // Validate inputs
        $required = ['topic_id', 'feedback'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $topic_id = intval($_POST['topic_id']);
        $feedback = in_array($_POST['feedback'], ['yes', 'no']) ? $_POST['feedback'] : null;
        $user_id = get_current_user_id();

        // Check if user has already voted
        global $wpdb;
        $existing_vote = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bkntc_topic_feedback WHERE topic_id = %d AND user_id = %d",
            $topic_id,
            $user_id
        ));

        if ($existing_vote) {
            throw new Exception('You have already provided feedback for this topic');
        }

        // Check if topic exists
        $topic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bkntc_help_topics WHERE id = %d", 
            $topic_id
        ));

        if (!$topic_exists) {
            throw new Exception('Topic not found');
        }

        // Insert new feedback with user_id
        $insert_result = $wpdb->insert(
            "{$wpdb->prefix}bkntc_topic_feedback",
            [
                'topic_id' => $topic_id,
                'feedback' => $feedback,
                'user_id' => $user_id,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%d', '%s']
        );

        if ($insert_result === false) {
            throw new Exception('Failed to save feedback: ' . $wpdb->last_error);
        }

        // Get updated stats with proper calculation of helpful percentage
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) as helpful,
                ROUND((SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as helpful_percentage
             FROM {$wpdb->prefix}bkntc_topic_feedback
             WHERE topic_id = %d",
            $topic_id
        ));

        if (!$stats) {
            throw new Exception('Failed to retrieve feedback stats');
        }

        return [
            'status' => true, 
            'message' => 'Thank you for your feedback!',
            'stats' => $stats,
            'helpful_percentage' => round(($stats->helpful / $stats->total) * 100),
            'total' => $stats->total
        ];
        
    } catch (Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    }
}

/**
 * AJAX handler for topic feedback
 */
function ajax_process_topic_feedback() {
    // Verify nonce
    check_ajax_referer('help_center_nonce', '_wpnonce');
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['error' => bkntc__('Please log in to submit feedback')]);
        return;
    }

    // Get and sanitize parameters
    $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
    $feedback = isset($_POST['feedback']) ? sanitize_text_field($_POST['feedback']) : '';
    
    // Validate required parameters
    if (!$topic_id) {
        wp_send_json_error(['error' => bkntc__('Invalid topic ID')]);
        return;
    }
    
    if (!in_array($feedback, ['yes', 'no'])) {
        wp_send_json_error(['error' => bkntc__('Invalid feedback value')]);
        return;
    }

    $user_id = get_current_user_id();

    try {
        // Check if user has already voted
        global $wpdb;
        $existing_vote = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bkntc_topic_feedback WHERE topic_id = %d AND user_id = %d",
            $topic_id,
            $user_id
        ));

        if ($existing_vote) {
            wp_send_json_error(['error' => bkntc__('You have already provided feedback for this topic')]);
            return;
        }

        // Check if topic exists
        $topic_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bkntc_help_topics WHERE id = %d", 
            $topic_id
        ));

        if (!$topic_exists) {
            wp_send_json_error(['error' => bkntc__('Topic not found')]);
            return;
        }

        // Insert new feedback with user_id
        $insert_result = $wpdb->insert(
            "{$wpdb->prefix}bkntc_topic_feedback",
            [
                'topic_id' => $topic_id,
                'feedback' => $feedback,
                'user_id' => $user_id,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%d', '%s']
        );

        if ($insert_result === false) {
            wp_send_json_error(['error' => bkntc__('Failed to save feedback: ') . $wpdb->last_error]);
            return;
        }

        // Get updated stats with proper calculation of helpful percentage
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) as helpful,
                ROUND((SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as helpful_percentage
             FROM {$wpdb->prefix}bkntc_topic_feedback
             WHERE topic_id = %d",
            $topic_id
        ));

        if (!$stats) {
            wp_send_json_error(['error' => bkntc__('Failed to retrieve feedback stats')]);
            return;
        }

        $helpful_percentage = round(($stats->helpful / $stats->total) * 100);
        $stats_html = sprintf(
            bkntc__('%d%% of %d people found this helpful'), 
            $helpful_percentage, 
            $stats->total
        );

        wp_send_json_success([
            'message' => bkntc__('Thank you for your feedback!'),
            'stats' => $stats,
            'stats_html' => $stats_html
        ]);

    } catch (Exception $e) {
        wp_send_json_error(['error' => $e->getMessage()]);
    }
}
