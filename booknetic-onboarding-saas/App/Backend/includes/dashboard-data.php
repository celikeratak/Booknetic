<?php
defined('ABSPATH') or die();

/**
 * Prepare dashboard statistics and data for display
 * 
 * @return array Array containing all dashboard data
 */
function get_dashboard_data() {
    global $wpdb;
    
    // Dashboard Statistics Data
    $stats = [
        'total_topics' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_topics"),
        'total_categories' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_categories"),
        'total_views' => $wpdb->get_var("SELECT SUM(views) FROM {$wpdb->prefix}bkntc_help_topics"),
        // Calculate satisfaction rate based on yes/no feedback
        'satisfaction_rate' => $wpdb->get_var("
            SELECT COALESCE(
                (SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_topic_feedback WHERE feedback = 'yes') / 
                NULLIF(COUNT(*), 0), 
                0
            ) 
            FROM {$wpdb->prefix}bkntc_topic_feedback
        "),
        // Calculate total likes (positive feedback)
        'total_likes' => $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}bkntc_topic_feedback 
            WHERE feedback = 'yes'
        "),
        // Calculate total dislikes (negative feedback)
        'total_dislikes' => $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}bkntc_topic_feedback 
            WHERE feedback = 'no'
        "),
        // Calculate total feedback
        'total_feedback' => $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}bkntc_topic_feedback
        "),
        // Calculate average views per topic
        'avg_views_per_topic' => $wpdb->get_var("
            SELECT COALESCE(AVG(views), 0) 
            FROM {$wpdb->prefix}bkntc_help_topics
        "),
        // Calculate average views percentage (assuming 100 views per topic is 100%)
        'avg_views_percentage' => $wpdb->get_var("
            SELECT COALESCE(
                (SELECT AVG(views) FROM {$wpdb->prefix}bkntc_help_topics) / 100 * 100,
                0
            )
        "),
        // Calculate percentage of topics with feedback
        'topics_with_feedback_percentage' => $wpdb->get_var("
            SELECT COALESCE(
                (SELECT COUNT(DISTINCT topic_id) FROM {$wpdb->prefix}bkntc_topic_feedback) / 
                NULLIF((SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_topics), 0) * 100,
                0
            )
        "),
        // Calculate knowledge health score (weighted average of multiple factors)
        'knowledge_health_score' => 0 // Will be calculated below
    ];
    
    // Calculate knowledge health score (weighted average of multiple factors)
    // Factors: content coverage (30%), feedback rate (30%), satisfaction rate (40%)
    $content_coverage = min(($stats['total_topics'] / 300) * 100, 100); // Assume 300 topics is 100% coverage
    $feedback_rate = min(($stats['total_feedback'] / max($stats['total_topics'] * 5, 1)) * 100, 100); // Assume 5 feedbacks per topic is ideal
    $satisfaction_score = $stats['satisfaction_rate'] * 100;
    
    $stats['knowledge_health_score'] = ($content_coverage * 0.3) + ($feedback_rate * 0.3) + ($satisfaction_score * 0.4);
    
    // Most Viewed Topics (Last 30 Days)
    $most_viewed = $wpdb->get_results("
        SELECT title, views, id 
        FROM {$wpdb->prefix}bkntc_help_topics 
        ORDER BY views DESC 
        LIMIT 10
    ");
    
    $top_rated = $wpdb->get_results("
        SELECT t.title, 
               t.views, 
               t.id,
               COUNT(f.id) AS total_feedback,
               SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) AS positive_feedback,
               (SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) / NULLIF(COUNT(f.id), 0)) AS satisfaction_rate,
               (t.views + COUNT(f.id) + SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END)) AS ranking_score
        FROM {$wpdb->prefix}bkntc_help_topics t
        LEFT JOIN {$wpdb->prefix}bkntc_topic_feedback f ON t.id = f.topic_id
        GROUP BY t.id
        HAVING COUNT(f.id) > 0
        ORDER BY ranking_score DESC, satisfaction_rate DESC
        LIMIT 10
    ");

    // Topic Status Distribution
    $status_distribution = [
        ['status' => 'published', 'count' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_topics")],
    ];

    // Popular Searches (from search logs)
    $popular_searches = $wpdb->get_results("
        SELECT search_term, COUNT(*) AS search_count 
        FROM {$wpdb->prefix}bkntc_search_logs 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_term 
        ORDER BY search_count DESC 
        LIMIT 6
    ");

    // Monthly Topic Trends with Feedback and Views
    $monthly_trends = $wpdb->get_results("
        SELECT 
            DATE_FORMAT(t.created_at, '%Y-%m') AS month, 
            COUNT(DISTINCT t.id) AS topic_count,
            COUNT(DISTINCT f.id) AS feedback_count,
            SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) AS positive_feedback,
            SUM(CASE WHEN f.feedback = 'no' THEN 1 ELSE 0 END) AS negative_feedback,
            SUM(t.views) AS total_views
        FROM {$wpdb->prefix}bkntc_help_topics t
        LEFT JOIN {$wpdb->prefix}bkntc_topic_feedback f ON t.id = f.topic_id
        WHERE t.created_at IS NOT NULL
        GROUP BY DATE_FORMAT(t.created_at, '%Y-%m')
        ORDER BY month DESC 
        LIMIT 6
    ");

    // Reverse the order for chronological display
    $monthly_trends = array_reverse($monthly_trends);

    // Most Helpful Articles (based on positive feedback percentage)
    $most_helpful = $wpdb->get_results("
        SELECT 
            t.id,
            t.title, 
            COUNT(f.id) as total_feedback,
            SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) as positive_feedback,
            (SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) / COUNT(f.id)) * 100 as helpfulness_percentage
        FROM {$wpdb->prefix}bkntc_help_topics t
        JOIN {$wpdb->prefix}bkntc_topic_feedback f ON t.id = f.topic_id
        GROUP BY t.id
        HAVING COUNT(f.id) >= 3
        ORDER BY helpfulness_percentage DESC, total_feedback DESC
        LIMIT 5
    ");

    // Feedback Statistics
    $feedback_stats = $wpdb->get_row("
        SELECT 
            COUNT(*) AS total_feedbacks,
            SUM(CASE WHEN feedback = 'yes' THEN 1 ELSE 0 END) AS positive_feedbacks,
            SUM(CASE WHEN feedback = 'no' THEN 1 ELSE 0 END) AS negative_feedbacks
        FROM {$wpdb->prefix}bkntc_topic_feedback
    ");

    // Calculate helpfulness percentage
    $helpfulness_percentage = $feedback_stats->total_feedbacks > 0 
        ? ($feedback_stats->positive_feedbacks / $feedback_stats->total_feedbacks) * 100 
        : 0;

    // Category Distribution
    $category_distribution = $wpdb->get_results("
        SELECT c.name, COUNT(t.id) AS count 
        FROM {$wpdb->prefix}bkntc_help_categories c
        LEFT JOIN {$wpdb->prefix}bkntc_help_topics t ON c.id = t.category_id
        GROUP BY c.id
    ");
    
    // Get topics with most engagement (views and feedback) for engagement chart
    $engagement_topics = $wpdb->get_results("
        SELECT t.title, t.views, 
               COUNT(f.id) as total_feedback,
               SUM(CASE WHEN f.feedback = 'yes' THEN 1 ELSE 0 END) as positive_feedback,
               SUM(CASE WHEN f.feedback = 'no' THEN 1 ELSE 0 END) as negative_feedback
        FROM {$wpdb->prefix}bkntc_help_topics t
        LEFT JOIN {$wpdb->prefix}bkntc_topic_feedback f ON t.id = f.topic_id
        GROUP BY t.id
        ORDER BY (t.views + COALESCE(total_feedback, 0)) DESC
        LIMIT 7
    ");

    return [
        'stats' => $stats,
        'most_viewed' => $most_viewed,
        'top_rated' => $top_rated,
        'status_distribution' => $status_distribution,
        'popular_searches' => $popular_searches,
        'monthly_trends' => $monthly_trends,
        'most_helpful' => $most_helpful,
        'feedback_stats' => $feedback_stats,
        'helpfulness_percentage' => $helpfulness_percentage,
        'category_distribution' => $category_distribution,
        'engagement_topics' => $engagement_topics
    ];
}
