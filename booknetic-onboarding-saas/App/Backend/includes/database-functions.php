<?php
defined('ABSPATH') or die();

/**
 * Create the search logs table if it doesn't exist
 */
function create_search_logs_table() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'bkntc_search_logs';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        search_term varchar(255) NOT NULL,
        results_count int(11) NOT NULL DEFAULT 0,
        user_id bigint(20) DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY search_term (search_term),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Log a search query
 * 
 * @param string $search_term The search term
 * @param int $results_count Number of results found
 * @return int|false The ID of the inserted log or false on failure
 */
function log_search_query($search_term, $results_count) {
    global $wpdb;
    
    // Get current user ID if logged in
    $user_id = get_current_user_id();
    $user_id = $user_id ? $user_id : null;
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Insert the search log
    $result = $wpdb->insert(
        $wpdb->prefix . 'bkntc_search_logs',
        array(
            'search_term' => $search_term,
            'results_count' => $results_count,
            'user_id' => $user_id,
            'ip_address' => $ip_address,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%d', '%d', '%s', '%s')
    );
    
    return $result ? $wpdb->insert_id : false;
}

/**
 * Get popular search terms
 * 
 * @param int $limit Maximum number of results to return
 * @param int $days Number of days to look back
 * @return array Popular search terms with counts
 */
function get_popular_search_terms($limit = 10, $days = 30) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bkntc_search_logs';
    
    // Calculate the date for the time period
    $date_limit = date('Y-m-d H:i:s', strtotime("-$days days"));
    
    $query = $wpdb->prepare(
        "SELECT search_term, COUNT(*) as search_count 
        FROM $table_name 
        WHERE created_at >= %s 
        GROUP BY search_term 
        ORDER BY search_count DESC 
        LIMIT %d",
        $date_limit,
        $limit
    );
    
    return $wpdb->get_results($query);
}
