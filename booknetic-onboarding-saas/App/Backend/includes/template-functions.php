<?php
defined('ABSPATH') or die();

use function BookneticAddon\ContactUsP\bkntc__;
use BookneticApp\Providers\Helpers\Helper;

// Include feature toggles functionality
require_once dirname(__FILE__) . '/feature-toggles.php';

/**
 * Get allowed HTML tags for topic content
 * 
 * @return array Allowed HTML tags
 */
function get_allowed_html_tags() {
    return array(
        'iframe' => array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allow'           => true,
            'allowfullscreen' => true,
            'class'           => true,
        ),
        'p'      => array(),
        'br'     => array(),
        'b'      => array(),
        'i'      => array(),
        'u'      => array(),
        'strong' => array(),
        'em'     => array(),
        'h1'     => array(),
        'h2'     => array(),
        'h3'     => array(),
        'h4'     => array(),
        'h5'     => array(),
        'h6'     => array(),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'blockquote' => array(),
        'code'   => array(),
        'pre'    => array(),
        'a'      => array(
            'href'   => true,
            'title'  => true,
            'target' => true,
            'rel'    => true,
        ),
        'img'    => array(
            'src'    => true,
            'alt'    => true,
            'width'  => true,
            'height' => true,
            'class'  => true,
            'style'  => true,
        ),
        'span'   => array(
            'class' => true,
            'style' => true,
        ),
        'div'    => array(
            'class' => true,
            'style' => true,
        ),
    );
}

/**
 * Render search results
 * 
 * @param array $topics Topics matching search
 * @param string $search_term Search term
 * @param string $base_url Base URL
 */
function render_search_results($topics, $search_term, $base_url) {
    // Group topics by category for better organization
    $categorized_topics = [];
    $categories_info = [];
    
    // Process topics and organize by category
    foreach ($topics as $topic) {
        if (!isset($categorized_topics[$topic->category_id])) {
            $categorized_topics[$topic->category_id] = [];
            $categories_info[$topic->category_id] = isset($topic->category_name) ? $topic->category_name : 'Uncategorized';
        }
        $categorized_topics[$topic->category_id][] = $topic;
    }
    
    // Highlight search terms in content
    $search_words = preg_split('/\s+/', trim($search_term));
    $valid_search_words = [];
    
    foreach ($search_words as $word) {
        if (strlen($word) > 2) {
            $valid_search_words[] = preg_quote($word, '/');
        }
    }
    
    $search_pattern = !empty($valid_search_words) ? '/(' . implode('|', $valid_search_words) . ')/i' : '';
    ?>
    
    <div class="search-results-page">
        <h2><?php echo bkntc__('Search Results for')?>: <span class="search-term"><?php echo esc_html($search_term); ?></span></h2>
        
        <div class="search-results-count">
            <?php 
            $count = count($topics);
            if ($count === 0) {
                echo bkntc__('No topics found matching your search.');
            } else {
                echo sprintf(_n('%d topic found', '%d topics found', $count, 'booknetic-onboarding-saas'), $count);
            }
            ?>
        </div>

        <?php if (!empty($topics)): ?>
            <div class="search-results-list">
                <?php foreach ($categorized_topics as $category_id => $category_topics): ?>
                    <div class="category-section">
                        <?php foreach ($category_topics as $topic): ?>
                            <div class="search-result-card">
                                <span class="category-badge"><?php echo esc_html($categories_info[$category_id]); ?></span>
                                
                                <h3>
                                    <a href="<?php echo esc_url($base_url . '&topic=' . $topic->id); ?>">
                                        <?php 
                                        // Highlight search terms in title
                                        $highlighted_title = $topic->title;
                                        if (!empty($search_pattern)) {
                                            $highlighted_title = preg_replace(
                                                $search_pattern, 
                                                '<strong>$1</strong>', 
                                                $highlighted_title
                                            );
                                        }
                                        echo wp_kses($highlighted_title, ['strong' => []]); 
                                        ?>
                                    </a>
                                </h3>
                                
                                <div class="topic-meta-search">
                                    <span class="topic-views-count">
                                        <i class="fa fa-eye"></i> <?php echo number_format($topic->views); ?> <?php echo bkntc__('views'); ?>
                                    </span>
                                </div>
                                
                                <div class="result-excerpt">
                                    <?php 
                                    // Get excerpt with highlighted search terms
                                    $content = wp_strip_all_tags($topic->content);
                                    
                                    // Find first occurrence of search term for better excerpt
                                    $excerpt_position = 0;
                                    $excerpt_length = 200;
                                    
                                    if (!empty($valid_search_words)) {
                                        foreach ($valid_search_words as $word) {
                                            $pos = stripos($content, trim($word, '\\/'));
                                            if ($pos !== false) {
                                                // Start excerpt a bit before the first match
                                                $excerpt_position = max(0, $pos - 50);
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Create excerpt
                                    $excerpt = substr($content, $excerpt_position, $excerpt_length);
                                    if ($excerpt_position > 0) {
                                        $excerpt = '...' . $excerpt;
                                    }
                                    if (strlen($content) > $excerpt_position + $excerpt_length) {
                                        $excerpt .= '...';
                                    }
                                    
                                    // Highlight search terms in excerpt
                                    if (!empty($search_pattern)) {
                                        $excerpt = preg_replace(
                                            $search_pattern, 
                                            '<strong>$1</strong>', 
                                            $excerpt
                                        );
                                    }
                                    
                                    echo wp_kses($excerpt, ['strong' => []]); 
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results-container">
                <div class="no-results-content">
                    <div class="no-results-icon">
                        <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="40" cy="40" r="38" stroke="var(--primary-color-back, #E0E5FF)" stroke-width="4"/>
                            <path d="M33 33L47 47" stroke="var(--primary-color, #4050B5)" stroke-width="4" stroke-linecap="round"/>
                            <path d="M47 33L33 47" stroke="var(--primary-color, #4050B5)" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h2 class="no-results-title"><?php echo bkntc__('No topics found') ?></h2>
                    <p class="no-results-message"><?php echo bkntc__('We couldn\'t find any results for your search.') ?></p>
                    <div class="no-results-suggestions">
                        <h4><i class="fas fa-question"></i> <?php echo bkntc__('Suggestions:') ?></h4>
                        <ul>
                            <li><i class="fas fa-spell-check"></i> - <?php echo bkntc__('Check your spelling') ?></li>
                            <li><i class="fas fa-atlas"></i> - <?php echo bkntc__('Try more general keywords') ?></li>
                            <li><i class="fas fa-language"></i> - <?php echo bkntc__('Try different keywords') ?></li>
                        </ul>
                    </div>
                    <a class="view-all-results" href="<?php echo esc_url($base_url); ?>">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>  <?php echo bkntc__('Back to Help Center') ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($topics)): ?>
            <a class="view-all-results" href="<?php echo esc_url($base_url); ?>">
                ← <?php echo bkntc__('Back to Help Desk') ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render topic details
 * 
 * @param object $topic_details Topic details
 * @param string $base_url Base URL
 * @param string $error_message Optional error message
 */
function render_topic_details($topic_details, $base_url, $error_message = '') {
    global $wpdb;
    
    // Check if we have a valid topic object
    if (!is_object($topic_details) || !isset($topic_details->id)) {
        return;
    }
    
    // Get the topic's category information
    $category = null;
    
    // First, try to get category using the topic's category_id
    if (isset($topic_details->category_id) && !empty($topic_details->category_id)) {
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkntc_help_categories WHERE id = %d",
            $topic_details->category_id
        ));
    }
    
    // If no category found, check if we need to use a different table name
    if (!$category && isset($topic_details->category_id) && !empty($topic_details->category_id)) {
        // Try with booknetic_help_categories table
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booknetic_help_categories WHERE id = %d",
            $topic_details->category_id
        ));
    }
    
    // If still no category, try to get it from the database with a join query
    if (!$category) {
        // Try with bkntc prefix
        $topic_with_category = $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, c.id as cat_id, c.name as cat_name 
             FROM {$wpdb->prefix}bkntc_help_topics t 
             LEFT JOIN {$wpdb->prefix}bkntc_help_categories c ON t.category_id = c.id 
             WHERE t.id = %d",
            $topic_details->id
        ));
        
        if ($topic_with_category && isset($topic_with_category->cat_id) && !empty($topic_with_category->cat_id)) {
            $category = new stdClass();
            $category->id = $topic_with_category->cat_id;
            $category->name = $topic_with_category->cat_name;
        } else {
            // Try with booknetic prefix
            $topic_with_category = $wpdb->get_row($wpdb->prepare(
                "SELECT t.*, c.id as cat_id, c.name as cat_name 
                 FROM {$wpdb->prefix}booknetic_help_topics t 
                 LEFT JOIN {$wpdb->prefix}booknetic_help_categories c ON t.category_id = c.id 
                 WHERE t.id = %d",
                $topic_details->id
            ));
            
            if ($topic_with_category && isset($topic_with_category->cat_id) && !empty($topic_with_category->cat_id)) {
                $category = new stdClass();
                $category->id = $topic_with_category->cat_id;
                $category->name = $topic_with_category->cat_name;
            }
        }
    }
    
    // If still no category, try to get the category directly from the database
    if (!$category) {
        // Try with bkntc prefix
        $all_categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bkntc_help_categories ORDER BY id ASC LIMIT 1");
        
        if (!empty($all_categories)) {
            $category = $all_categories[0];
        } else {
            // Try with booknetic prefix
            $all_categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booknetic_help_categories ORDER BY id ASC LIMIT 1");
            
            if (!empty($all_categories)) {
                $category = $all_categories[0];
            } else {
                // If no categories found, create a default one
                $category = new stdClass();
                $category->id = 1; // Use 1 instead of 0 for a more realistic ID
                $category->name = bkntc__('General');
            }
        }
    }
    
    ?>
    <!-- Breadcrumb Navigation -->
    <div class="help-breadcrumbs">
        <a href="<?php echo esc_url($base_url); ?>" class="breadcrumb-item">
            <i class="fas fa-home breadcrumb-icon home-icon"></i> <?php echo bkntc__('Help Center'); ?>
        </a>
        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
        
        <?php if ($category): ?>
        <a href="<?php echo esc_url(add_query_arg('category', $category->id, $base_url)); ?>" class="breadcrumb-item">
            <i class="fas <?php echo !empty($category->icon) ? esc_attr($category->icon) : 'fa-folder'; ?> breadcrumb-icon category-icon"></i> <?php echo esc_html($category->name); ?>
        </a>
        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
        
        <span class="breadcrumb-item current">
            <i class="fas fa-book breadcrumb-icon topic-icon"></i> <?php echo esc_html($topic_details->title); ?>
        </span>
    </div>
    <div class="topic-content">
    <h1 class="topic-title"><?php echo esc_html($topic_details->title); ?></h1>
    <div class="topic-meta">
        <?php
        // Calculate and display reading time
        $reading_time = calculate_reading_time($topic_details->content);
        ?>
        <div class="topic-meta-item reading-time">
            <i class="fas fa-clock"></i>
            <span><?php echo sprintf(bkntc__('%d min read'), $reading_time); ?></span>
        </div>
        
        <div class="topic-meta-item topic-views">
            <i class="fa fa-eye"></i>
            <span><?php echo sprintf(bkntc__('%s views'), number_format($topic_details->views)); ?></span>
        </div>
        
        <?php
        // Get the number of likes (positive feedback) for this topic
        $likes_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_topic_feedback WHERE topic_id = %d AND feedback = 'yes'",
            $topic_details->id
        ));
        ?>
        <div class="topic-meta-item topic-likes">
            <i class="fa fa-thumbs-up"></i>
            <span><?php echo sprintf(bkntc__('%s likes'), number_format($likes_count)); ?></span>
        </div>
        
        <div class="topic-meta-item topic-date">
            <i class="fa fa-calendar"></i>
            <span><?php echo bkntc__(date_i18n('M j, Y', strtotime($topic_details->created_at))); ?></span>
        </div>

    </div>
    
        <?php
        // Display the sanitized content
        echo wp_kses($topic_details->content, get_allowed_html_tags());
        ?>

   

            <?php if (is_feature_enabled('feedback_section', true)): ?>
            <div class="article-feedback">
            <?php
        $user_has_voted = false;
        
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $existing_vote = $wpdb->get_var($wpdb->prepare(
                "SELECT feedback FROM {$wpdb->prefix}bkntc_topic_feedback WHERE topic_id = %d AND user_id = %d",
                $topic_details->id,
                $user_id
            ));
            $user_has_voted = !is_null($existing_vote);
        }
        ?>
                <?php if (!is_user_logged_in()): ?>
                    <div class="feedback-message">
                        <i class="fas fa-user-lock"></i>
                        <p><?php echo bkntc__('Please log in to provide feedback'); ?></p>
                    </div>
                <?php elseif ($user_has_voted): ?>
                    <div class="feedback-success-message">
                        <i class="fas fa-check-circle"></i>
                        <p><?php echo bkntc__('Thank you for your feedback!'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="feedback-container">
                        <h3 class="feedback-title"><?php echo bkntc__('Was this article helpful?'); ?></h3>
                        <p class="feedback-subtitle"><?php echo bkntc__('Let us know if you found what you were looking for'); ?></p>
                        
                        <div class="feedback-buttons">
                            <button type="button" class="feedback-button yes" data-feedback="yes" data-topic-id="<?php echo $topic_details->id; ?>">
                                <i class="fa fa-thumbs-up"></i> <?php echo bkntc__('Yes'); ?>
                            </button>
                            <button type="button" class="feedback-button no" data-feedback="no" data-topic-id="<?php echo $topic_details->id; ?>">
                                <i class="fa fa-thumbs-down"></i> <?php echo bkntc__('No'); ?>
                            </button>
                            <input type="hidden" id="feedback_nonce" value="<?php echo wp_create_nonce('help_center_nonce'); ?>">
                        </div>
                        <div class="feedback-message" style="display: none;"></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="feedback-error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p><?php echo esc_html($error_message); ?></p>
                    </div>
                <?php endif; ?>
                <?php endif; // End of feedback_section check ?>
                <?php
                $feedback_stats = get_topic_feedback_stats($topic_details->id);
                if ($feedback_stats && $feedback_stats->total > 0) {
                    $helpful_percentage = round(($feedback_stats->helpful / $feedback_stats->total) * 100);
                    $stats_class = $helpful_percentage >= 70 ? 'positive' : ($helpful_percentage >= 40 ? 'neutral' : 'negative');
                    ?>
                    <div class="feedback-stats <?php echo $stats_class; ?>">
                        <div class="stats-icon">
                            <?php if ($helpful_percentage >= 70): ?>
                                <i class="fas fa-smile"></i>
                            <?php elseif ($helpful_percentage >= 40): ?>
                                <i class="fas fa-meh"></i>
                            <?php else: ?>
                                <i class="fas fa-frown"></i>
                            <?php endif; ?>
                        </div>
                        <div class="stats-text">
                            <?php echo sprintf(bkntc__('%d%% of %d people found this helpful'), $helpful_percentage, $feedback_stats->total); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

    
    <?php
    // Get related topics from the same category if feature is enabled
    if (is_feature_enabled('related_articles', true) && $category && isset($category->id)) {
        $related_topics = get_related_topics($category->id, $topic_details->id, 2);
        
        if (!empty($related_topics)) {
            ?>
            <div class="related-topics-section">
                <h2 class="related-topics-title"><?php echo bkntc__('Related Articles'); ?></h2>
                <div class="related-topics-container">
                    <?php foreach ($related_topics as $related_topic): ?>
                        <a href="<?php echo esc_url(add_query_arg('topic', $related_topic->id, $base_url)); ?>" class="related-topic-card">
                            <div class="related-topic-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="related-topic-content">
                                <h3 class="related-topic-title"><?php echo esc_html($related_topic->title); ?></h3>
                                <div class="related-topic-meta">
                                    <span class="related-topic-views">
                                        <i class="fas fa-eye"></i> <?php echo number_format($related_topic->views); ?> <?php echo bkntc__('views'); ?>
                                    </span>
                                    <span class="related-topic-date">
                                        <i class="fas fa-calendar-alt"></i> <?php echo date_i18n('M j, Y', strtotime($related_topic->created_at)); ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
    <?php
    // Show 'Still need help?' section if feature is enabled
    if (is_feature_enabled('still_need_help', true)) {
        $support_link = get_support_link();
        ?>
        <div class="help-section">
            <strong><?php echo bkntc__('Still need help?'); ?></strong>
            <a href="<?php echo esc_url($support_link->url); ?>" target="_blank" class="support-btn">
                <i class="fas fa-headset"></i>
                <?php echo esc_html($support_link->label ?: 'Contact Support'); ?>
            </a>
        </div>
    <?php } ?>

    <?php
}

/**
 * Render category topics
 * 
 * @param object $category Category object
 * @param array $topics Topics in the category
 * @param string $base_url Base URL
 */
function render_category_topics($category, $topics, $base_url) {
    ?>
    <!-- Breadcrumb Navigation -->
    <div class="help-breadcrumbs">
        <a href="<?php echo esc_url($base_url); ?>" class="breadcrumb-item">
            <i class="fas fa-home breadcrumb-icon home-icon"></i> <?php echo bkntc__('Help Center'); ?>
        </a>
        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
        
        <span class="breadcrumb-item current">
            <i class="fas <?php echo !empty($category->icon) ? esc_attr($category->icon) : 'fas fa-folder'; ?> breadcrumb-icon category-icon"></i> <?php echo esc_html($category->name); ?>
        </span>
    </div>
    
    <h2 style="margin-top: 34px; margin-bottom: 40px; color: var(--primary-color)">
        <?php echo esc_html($category->name); ?>
    </h2>
    <?php if (!empty($topics)): ?>
        <ul class="topics-list">
            <?php foreach ($topics as $topic): ?>
                <li class="topic-item" onclick="window.location.href='<?php echo esc_url($base_url . '&topic=' . $topic->id); ?>'">
                    <div class="topic-header">
                        <h2><?php echo esc_html($topic->title); ?></h2>
                        <span class="topic-views-count">
                            <i class="fa fa-eye"></i> <?php echo number_format($topic->views); ?>
                        </span>
                    </div>
                    <p><?php echo wp_kses_post(wp_trim_words($topic->content, 25, '...')); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="no-topics-message">
            <i class="fas fa-info-circle"></i>
            <p><?php echo bkntc__('There are no topics in this category yet.'); ?></p>
        </div>
    <?php endif;

    // Get random categories
    $random_categories = get_random_categories($category->id);
    
    if (!empty($random_categories)): ?>
        <div class="other-categories-section">
            <h3><?php echo bkntc__('Other Categories'); ?></h3>
            <div class="random-categories-grid">
                <?php foreach ($random_categories as $cat): ?>
                    <div class="category-card" 
                        onclick="window.location.href='<?php echo esc_url($base_url . '&category=' . $cat->id); ?>'">
                        <i class="fas <?php echo !empty($cat->icon) ? esc_attr($cat->icon) : 'fa-book'; ?>"></i>
                        <div class="content">
                            <h2><?php echo esc_html($cat->name); ?></h2>
                            <p><?php echo wp_kses_post(wp_trim_words(trim($cat->description), 20, '...')); ?></p>
                            <div class="topic-count">
                                <?php echo (int)$cat->topic_count; ?> <?php echo bkntc__('Topics'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
  
    <?php
}

/**
 * Render categories grid
 * 
 * @param array $categories All categories
 * @param object $livechat Live chat settings
 * @param string $base_url Base URL
 */
function render_categories_grid($categories, $livechat, $base_url) {
    ?>
    <div class="view-controls">
        <button class="view-switch" data-view="grid" title="<?php echo bkntc__('Grid view'); ?>">
            <i class="fas fa-th-large"></i>
        </button>
        <button class="view-switch" data-view="list" title="<?php echo bkntc__('List view'); ?>">
            <i class="fas fa-list"></i>
        </button>
        <?php if ( current_user_can('manage_options') ) : ?>
            <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'dashboard'], 'admin.php')); ?>" 
               class="view-switch control-panel" 
               title="<?php echo bkntc__('Control Panel'); ?>">
                <i class="fas fa-hammer"></i>
                <span><?php echo bkntc__('Control Panel'); ?></span>
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($categories)): ?>
    <!-- Categories Grid Section -->
    <div class="categories-list grid-view">
        <?php foreach ($categories as $cat): ?>
            <div class="category-card" onclick="window.location.href='<?php echo esc_url($base_url . '&category=' . $cat->id); ?>'">
                <i class="fas <?php echo !empty($cat->icon) ? esc_attr($cat->icon) : 'fa-book'; ?>"></i>
                <div class="content">
                    <h2><?php echo esc_html($cat->name); ?></h2>
                    <p><?php echo wp_kses_post(wp_trim_words(trim($cat->description), 20, '...')); ?></p>
                    <div class="topic-count">
                        <?php echo (int)$cat->topic_count; ?> <?php echo bkntc__('Topics'); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- No Categories Message (Always in List View) -->
    <div class="no-categories-message">
        <i class="fas fa-folder-open"></i>
        <h3><?php echo bkntc__('No categories found.'); ?></h3>
        <?php if (current_user_can('manage_options')): ?>
            <p><?php echo bkntc__('No help categories have been created yet.'); ?></p>
            <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'categories'], 'admin.php')); ?>" class="add-category-button">
                <i class="fas fa-plus"></i> <?php echo bkntc__('Add Category'); ?>
            </a>
        <?php else: ?>
            <p><?php echo bkntc__('Please check back later for help content.'); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Live Chat Section (Full Width) -->
    <?php if (is_feature_enabled('livechat', true) && $livechat): ?>
        <div class="livechat-section">
            <div class="livechat-card-full" onclick="window.location.href='<?php echo esc_url($base_url . '&view=livechat'); ?>'">
                <div class="livechat-content">
                    <div class="livechat-icon">
                        <i class="<?php echo esc_attr($livechat->icon ?: 'fas fa-comments'); ?>"></i>
                    </div>
                    <div class="livechat-text">
                        <h2><?php echo esc_html($livechat->title); ?></h2>
                        <p><?php echo esc_html($livechat->description); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Render page footer
 */
function render_footer() {
    // Get custom copyright text from help_settings table
    $copyright_text = get_help_setting('copyright_text', bkntc__('All rights reserved.'));
    ?>
    <footer class="help-desk-footer">
        <div class="footer-content">
            <div class="footer-left">
                <p>&copy; <?php echo date('Y'); ?> <?php echo Helper::getOption('backend_title', 'Help Center'); ?> - <?php echo esc_html($copyright_text); ?></p>
            </div>
            <div class="footer-center">
                <div class="footer-menu-links">
                    <?php
                    // Get menu links from the database
                    $menu_links = get_help_setting('menu_links', []);
                    
                    // Check if we have any menu links to display
                    if (!empty($menu_links) && is_array($menu_links)) {
                        // Sort menu links by order if available
                        usort($menu_links, function($a, $b) {
                            return isset($a->order) && isset($b->order) ? $a->order - $b->order : 0;
                        });
                        
                        // Display each active menu link
                        foreach ($menu_links as $link) {
                            if (isset($link->active) && $link->active) {
                                echo '<a href="' . esc_url($link->url) . '" target="_blank" class="footer-menu-link">';
                                echo esc_html($link->label);
                                echo '</a>';
                            }
                        }
                    } else {
                        // Fallback if no menu links are available
                        $support_link = get_support_link();
                        if ($support_link) {
                            echo '<a href="' . esc_url($support_link->url) . '" target="_blank" class="footer-menu-link">';
                            echo esc_html($support_link->label ?: 'Contact Support');
                            echo '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="footer-right">
                <div class="social-links">
                    <?php
                    $social_links = get_social_media_links();
                    
                    foreach ($social_links as $link): 
                        if (!empty($link->url)):
                    ?>
                        <a href="<?php echo esc_url($link->url); ?>" target="_blank" class="social-link">
                            <i class="fab <?php echo esc_attr($link->icon); ?>"></i>
                        </a>
                    <?php 
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </footer>
    <?php
}
