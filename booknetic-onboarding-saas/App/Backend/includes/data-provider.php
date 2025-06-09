<?php
defined('ABSPATH') or die();

// Include database functions
require_once dirname(__FILE__) . '/database-functions.php';

/**
 * Calculate estimated reading time for a given text
 * 
 * @param string $content The content to calculate reading time for
 * @param int $words_per_minute Average reading speed (default: 200 words per minute)
 * @return int Estimated reading time in minutes (rounded up)
 */
function calculate_reading_time($content, $words_per_minute = 200) {
    // Strip HTML tags and count words
    $text = strip_tags($content);
    $word_count = str_word_count($text);
    
    // Calculate reading time and round up to nearest minute
    $minutes = ceil($word_count / $words_per_minute);
    
    return $minutes;
}

/**
 * Get all help categories with topic count
 * 
 * @return array Categories with topic count
 */
function get_help_categories() {
    global $wpdb;
    
    $query = "SELECT c.*, COUNT(t.id) as topic_count 
        FROM {$wpdb->prefix}bkntc_help_categories c 
        LEFT JOIN {$wpdb->prefix}bkntc_help_topics t ON c.id = t.category_id 
        GROUP BY c.id 
        ORDER BY CAST(COALESCE(c.order_number, 999999) AS SIGNED) ASC, c.id ASC";
    
    return $wpdb->get_results($query, OBJECT_K);
}

/**
 * Get topics by search term with enhanced relevance scoring
 * 
 * @param string $search_term Search term
 * @param int $limit Maximum number of results to return (0 for unlimited)
 * @return array Topics matching search term with relevance score
 */
function get_topics_by_search($search_term, $limit = 0) {
    global $wpdb;
    
    // Sanitize and prepare search terms
    $search_term = trim($search_term);
    if (empty($search_term)) {
        return [];
    }
    
    // Split search term into words for more precise matching
    $search_words = preg_split('/\s+/', $search_term);
    $search_conditions = [];
    $search_params = [];
    
    // Build dynamic query with relevance scoring
    $relevance_score = "(CASE ";
    
    // Exact title match gets highest score
    $relevance_score .= "WHEN t.title = %s THEN 100 ";
    $search_params[] = $search_term;
    
    // Title starting with search term gets high score
    $relevance_score .= "WHEN t.title LIKE %s THEN 80 ";
    $search_params[] = $wpdb->esc_like($search_term) . '%';
    
    // Words in title get medium-high score
    foreach ($search_words as $word) {
        if (strlen($word) > 2) {
            $relevance_score .= "WHEN t.title LIKE %s THEN 60 ";
            $search_params[] = '% ' . $wpdb->esc_like($word) . ' %';
        }
    }
    
    // Title contains search term gets medium score
    $relevance_score .= "WHEN t.title LIKE %s THEN 50 ";
    $search_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    
    // Content contains exact search term gets medium-low score
    $relevance_score .= "WHEN t.content LIKE %s THEN 40 ";
    $search_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    
    // Words in content get low score
    foreach ($search_words as $word) {
        if (strlen($word) > 2) {
            $relevance_score .= "WHEN t.content LIKE %s THEN 20 ";
            $search_params[] = '%' . $wpdb->esc_like($word) . '%';
        }
    }
    
    $relevance_score .= "ELSE 0 END) AS relevance_score";
    
    // Build search conditions
    $search_conditions[] = "t.title LIKE %s";
    $search_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    
    $search_conditions[] = "t.content LIKE %s";
    $search_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    
    // Add individual word search for better matching
    foreach ($search_words as $word) {
        if (strlen($word) > 2) {
            $search_conditions[] = "t.title LIKE %s";
            $search_params[] = '%' . $wpdb->esc_like($word) . '%';
            
            $search_conditions[] = "t.content LIKE %s";
            $search_params[] = '%' . $wpdb->esc_like($word) . '%';
        }
    }
    
    // Combine all search conditions
    $search_query = "(" . implode(" OR ", $search_conditions) . ")";
    
    // Build the complete query with category information and excerpt generation
    $excerpt_length = 200; // Characters for excerpt
    $excerpt_query = "CASE 
        WHEN t.content LIKE %s THEN CONCAT(LEFT(t.content, $excerpt_length), '...') 
        ELSE CONCAT(LEFT(t.content, $excerpt_length), '...') 
    END AS excerpt";
    $search_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    
    // Prepare the full query
    $query = "SELECT t.id, t.title, t.content, t.category_id, t.views, c.name as category_name, 
              $relevance_score, $excerpt_query
              FROM {$wpdb->prefix}bkntc_help_topics t
              LEFT JOIN {$wpdb->prefix}bkntc_help_categories c ON t.category_id = c.id
              WHERE $search_query
              GROUP BY t.id
              ORDER BY relevance_score DESC, t.title ASC";
    
    // Add limit if specified
    if ($limit > 0) {
        $query .= " LIMIT %d";
        $search_params[] = $limit;
    }
    
    // Prepare and execute the query
    $prepared_query = $wpdb->prepare($query, $search_params);
    $results = $wpdb->get_results($prepared_query);
    
    // Log the search query
    if (function_exists('log_search_query')) {
        log_search_query($search_term, count($results));
    }
    
    return $results;
}

/**
 * Get topic details by ID
 * 
 * @param int $topic_id Topic ID
 * @return object|null Topic details or null
 */
function get_topic_details($topic_id) {
    global $wpdb;
    
    // Get the topic details
    $query = $wpdb->prepare("SELECT id, title, content, category_id, views, created_at FROM {$wpdb->prefix}bkntc_help_topics WHERE id = %d", $topic_id);
    
    $topic = $wpdb->get_row($query);
    
    if ($topic) {
        // Use a combination of IP address and topic ID to create a unique identifier
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_id = get_current_user_id();
        $unique_id = md5($ip_address . $user_id . $topic_id);
        
        // Create a transient name for this view
        $transient_name = 'bkntc_topic_view_' . $unique_id;
        
        // Check if this view has been recorded in the last 24 hours
        $viewed = get_transient($transient_name);
        
        // Also check the cookie as a fallback
        $cookie_name = 'bkntc_topic_viewed_' . $topic_id;
        $cookie_viewed = isset($_COOKIE[$cookie_name]) ? true : false;
        
        // Only increment view count if this is a new view (not in transient and not in cookie)
        if (!$viewed && !$cookie_viewed) {
            // Set a transient that expires in 24 hours
            set_transient($transient_name, true, 24 * HOUR_IN_SECONDS);
            
            // Also set a cookie as a fallback mechanism
            $secure = is_ssl();
            $httponly = true;
            
            // Use setcookie with appropriate parameters
            if (PHP_VERSION_ID < 70300) {
                setcookie($cookie_name, '1', time() + 24 * HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure, $httponly);
            } else {
                // PHP 7.3+ supports the options array
                setcookie($cookie_name, '1', [
                    'expires' => time() + 24 * HOUR_IN_SECONDS,
                    'path' => COOKIEPATH,
                    'domain' => COOKIE_DOMAIN,
                    'secure' => $secure,
                    'httponly' => $httponly,
                    'samesite' => 'Lax'
                ]);
            }
            
            // Update the views count in the database
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}bkntc_help_topics SET views = views + 1 WHERE id = %d",
                    $topic_id
                )
            );
            
            // Update the views count in the returned object to match what's in the database
            $topic->views = (int)$topic->views + 1;
        }
        
        // Get the category name for the topic
        $category_query = $wpdb->prepare("SELECT name FROM {$wpdb->prefix}bkntc_help_categories WHERE id = %d", $topic->category_id);
        $category = $wpdb->get_var($category_query);
        $topic->category_name = $category ? $category : '';
        
        return $topic;
    }
    
    return null;
}

/**
 * Get topics by category ID
 * 
 * @param int $category_id Category ID
 * @return array Topics in the category
 */
function get_topics_by_category($category_id) {
    global $wpdb;
    
    return $wpdb->get_results(
        $wpdb->prepare("SELECT id, title, content, views FROM {$wpdb->prefix}bkntc_help_topics WHERE category_id = %d", $category_id),
        OBJECT_K
    );
}

/**
 * Get random categories excluding specified category
 * 
 * @param int $exclude_category_id Category ID to exclude
 * @param int $limit Number of categories to return
 * @return array Random categories
 */
function get_random_categories($exclude_category_id, $limit = 2) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT c.*, COUNT(t.id) as topic_count 
        FROM {$wpdb->prefix}bkntc_help_categories c 
        LEFT JOIN {$wpdb->prefix}bkntc_help_topics t ON c.id = t.category_id 
        WHERE c.id != %d 
        GROUP BY c.id 
        ORDER BY RAND() 
        LIMIT %d",
        $exclude_category_id,
        $limit
    ));
}

/**
 * Get livechat settings
 * 
 * @return object|null Livechat settings or null
 */
function get_livechat_settings() {
    // Create a standard object with the livechat data from help_settings table
    $title = get_help_setting('livechat_title', '');
    $subtitle = get_help_setting('livechat_subtitle', '');
    $embed_code = get_help_setting('livechat_embed_code', '');
    $icon = get_help_setting('livechat_icon', 'fas fa-comments');
    
    // Only return livechat data if we have a title and embed code
    if (empty($title) || empty($embed_code)) {
        return null;
    }
    
    $livechat = new stdClass();
    $livechat->title = $title;
    $livechat->description = $subtitle;
    $livechat->icon = $icon;
    $livechat->embed_code = $embed_code;
    
    return $livechat;
}

/**
 * Get support link
 * 
 * @return object|null Support link or null with label and url properties
 */
function get_support_link() {
    // Use the help_settings table instead of the old support_link table
    $support_link = get_help_setting('support_link', null);
    
    // If support_link is not an object or doesn't have the expected properties, create a default
    if (!is_object($support_link) || !isset($support_link->url) || !isset($support_link->label)) {
        $support_link = (object)[
            'label' => bkntc__('Contact Support'),
            'url' => 'https://support.booknetic.com',
            'active' => 1,
            'id' => 1
        ];
    }
    
    return $support_link;
}

/**
 * Get a setting from the help_settings table
 * 
 * @param string $option_name The name of the option to retrieve
 * @param mixed $default Default value if option doesn't exist
 * @return mixed The option value or default
 */
function get_help_setting($option_name, $default = false) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bkntc_help_settings';
    
    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM $table_name WHERE option_name = %s LIMIT 1",
        $option_name
    ));
    
    if ($value === null) {
        return $default;
    }
    
    $unserialized = maybe_unserialize($value);
    
    // Handle the case where the value is a JSON string
    if (is_string($unserialized) && (strpos($unserialized, '{') === 0 || strpos($unserialized, '[') === 0)) {
        $json_decoded = json_decode($unserialized);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_decoded;
        }
    }
    
    return $unserialized;
}

/**
 * Update a setting in the help_settings table
 * 
 * @param string $option_name The name of the option to retrieve
 * @param mixed $option_value The new value for the option
 * @return bool True on success, false on failure
 */
function update_help_setting($option_name, $option_value) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bkntc_help_settings';
    $serialized_value = maybe_serialize($option_value);
    
    // Check if the option already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE option_name = %s",
        $option_name
    ));
    
    if ($exists) {
        // Update existing option
        $result = $wpdb->update(
            $table_name,
            ['option_value' => $serialized_value],
            ['option_name' => $option_name],
            ['%s'],
            ['%s']
        );
    } else {
        // Insert new option
        $result = $wpdb->insert(
            $table_name,
            [
                'option_name' => $option_name,
                'option_value' => $serialized_value
            ],
            ['%s', '%s']
        );
    }
    
    return $result !== false;
}

/**
 * Get social media links
 * 
 * @return array Social media links
 */
function get_social_media_links() {
    $social_media = get_help_setting('social_media_links', []);
    
    // If empty, check if we need to migrate from the old table
    if (empty($social_media)) {
        $db = \BookneticApp\Providers\DB\DB::DB();
        try {
            // Check if table exists
            $table_exists = $db->get_var("SHOW TABLES LIKE '" . \BookneticApp\Providers\DB\DB::table('social_media') . "'") === \BookneticApp\Providers\DB\DB::table('social_media');
            if ($table_exists) {
                $old_links = $db->get_results("SELECT * FROM `" . \BookneticApp\Providers\DB\DB::table('social_media') . "` ORDER BY display_order ASC");
                
                if (!empty($old_links)) {
                    // Migrate data from old table
                    $social_media = $old_links;
                    update_help_setting('social_media_links', $social_media);
                }
            }
        } catch (Exception $e) {
            // Table doesn't exist or other error
        }
    }
    
    // Ensure all items are objects with consistent properties
    $normalized_links = [];
    if (is_array($social_media)) {
        foreach ($social_media as $link) {
            // Convert to object if it's an array
            if (is_array($link)) {
                $link = (object) $link;
            }
            
            // Ensure all required properties exist
            if (!isset($link->id)) $link->id = time() + count($normalized_links);
            if (!isset($link->active)) $link->active = 1;
            if (!isset($link->display_order)) $link->display_order = count($normalized_links) + 1;
            
            $normalized_links[] = $link;
        }
        
        // Filter active links
        $active_links = array_filter($normalized_links, function($link) {
            return isset($link->active) && (int)$link->active === 1;
        });
        
        // Sort by display order
        usort($active_links, function($a, $b) {
            return (int)$a->display_order - (int)$b->display_order;
        });
        
        return $active_links;
    }
    
    return [];
}

/**
 * Check if user has voted on a topic
 * 
 * @param int $topic_id Topic ID
 * @param int $user_id User ID
 * @return bool True if user has voted, false otherwise
 */
function user_has_voted($topic_id, $user_id) {
    global $wpdb;
    
    $existing_vote = $wpdb->get_var($wpdb->prepare(
        "SELECT feedback FROM {$wpdb->prefix}bkntc_topic_feedback WHERE topic_id = %d AND user_id = %d",
        $topic_id,
        $user_id
    ));
    
    return !is_null($existing_vote);
}

/**
 * Get most popular help topics based on view count
 * 
 * @param int $limit Maximum number of topics to return
 * @return array Popular topics with category information
 */
function get_popular_topics($limit = 6) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT t.id, t.title, t.views, c.id as category_id, c.name as category_name 
        FROM {$wpdb->prefix}bkntc_help_topics t 
        LEFT JOIN {$wpdb->prefix}bkntc_help_categories c ON t.category_id = c.id 
        WHERE t.views > 0 
        ORDER BY t.views DESC 
        LIMIT %d",
        $limit
    ));
}

/**
 * Get related topics from the same category
 * 
 * @param int $category_id Category ID
 * @param int $current_topic_id Current topic ID to exclude
 * @param int $limit Maximum number of topics to return
 * @return array Related topics from the same category
 */
function get_related_topics($category_id, $current_topic_id, $limit = 2) {
    global $wpdb;
    
    // Get topics from the same category, excluding the current topic
    $related_topics = $wpdb->get_results($wpdb->prepare(
        "SELECT id, title, views, created_at 
         FROM {$wpdb->prefix}bkntc_help_topics 
         WHERE category_id = %d AND id != %d 
         ORDER BY views DESC 
         LIMIT %d",
        $category_id,
        $current_topic_id,
        $limit
    ));
    
    // If no topics found, try with booknetic prefix
    if (empty($related_topics)) {
        $related_topics = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title, views, created_at 
             FROM {$wpdb->prefix}booknetic_help_topics 
             WHERE category_id = %d AND id != %d 
             ORDER BY views DESC 
             LIMIT %d",
            $category_id,
            $current_topic_id,
            $limit
        ));
    }
    
    return $related_topics;
}
