<?php
defined('ABSPATH') or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * Register AJAX handlers for the help center functionality
 */
add_action('wp_ajax_booknetic_extract_documentation', 'extract_documentation_content');
// We only need the logged-in version since this is an admin-only feature

/**
 * Extract content from a Booknetic documentation URL
 * 
 * This function handles the AJAX request to extract content from a Booknetic documentation URL
 * and returns the extracted data in JSON format.
 */
function extract_documentation_content() {
    // Verify nonce
    check_ajax_referer('upload_attachment_nonce', '_wpnonce');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Permission denied']);
        return;
    }
    
    // Get and validate URL
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    
    if (empty($url)) {
        wp_send_json_error(['error' => 'URL is required']);
        return;
    }
    
    // Validate that this is a Booknetic documentation URL
    if (!preg_match('/^https?:\/\/(?:www\.)?booknetic\.com\/documentation/i', $url)) {
        wp_send_json_error(['error' => 'URL must be from Booknetic documentation']);
        return;
    }
    
    // Fetch the content from the URL
    $response = wp_remote_get($url, [
        'timeout' => 30,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ]);
    
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(['error' => 'Failed to fetch content: ' . $response->get_error_message()]);
        return;
    }
    
    // Check response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        wp_send_json_error(['error' => 'Failed to fetch content: HTTP ' . $response_code]);
        return;
    }
    
    // Get the body content
    $html = wp_remote_retrieve_body($response);
    
    if (empty($html)) {
        wp_send_json_error(['error' => 'No content found']);
        return;
    }
    
    // Load HTML into DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // @ to suppress warnings for malformed HTML
    $xpath = new DOMXPath($dom);
    
    // Extract title - typically the main heading
    $title = '';
    $title_elements = $xpath->query('//h1');
    if ($title_elements->length > 0) {
        $title = trim($title_elements->item(0)->textContent);
    }
    
    // If no h1, try to get the page title
    if (empty($title)) {
        $title_elements = $xpath->query('//title');
        if ($title_elements->length > 0) {
            $title = trim($title_elements->item(0)->textContent);
            // Remove site name if present (e.g., "Page Title - Booknetic")
            $title = preg_replace('/ - Booknetic$/', '', $title);
        }
    }
    
    // Extract main content
    $content = '';
    $content_elements = $xpath->query('//article | //main | //div[contains(@class, "content")]');
    
    if ($content_elements->length > 0) {
        // Get the first matching element
        $content_element = $content_elements->item(0);
        
        // Extract key points (headings and lists)
        $key_points = [];
        
        // Get all headings (h2, h3, h4)
        $headings = $xpath->query('.//h2 | .//h3 | .//h4', $content_element);
        foreach ($headings as $heading) {
            $key_points[] = trim($heading->textContent);
        }
        
        // Get all list items
        $list_items = $xpath->query('.//ul/li | .//ol/li', $content_element);
        foreach ($list_items as $item) {
            $key_points[] = trim($item->textContent);
        }
        
        // Format content with extracted information
        $content = '<h2>Description</h2>';
        
        // Add first paragraph as description
        $paragraphs = $xpath->query('.//p', $content_element);
        if ($paragraphs->length > 0) {
            $content .= '<p>' . trim($paragraphs->item(0)->textContent) . '</p>';
        }
        
        // Add key points
        if (!empty($key_points)) {
            $content .= '<h2>Key Points</h2><ul>';
            foreach ($key_points as $point) {
                $content .= '<li>' . esc_html($point) . '</li>';
            }
            $content .= '</ul>';
        }
        
        // Add source attribution
        $content .= '<p><em>Source: <a href="' . esc_url($url) . '" target="_blank">Booknetic Documentation</a></em></p>';
    }
    
    // If we couldn't extract content properly
    if (empty($title) || empty($content)) {
        wp_send_json_error(['error' => 'Could not extract content from the provided URL']);
        return;
    }
    
    // Return the extracted data
    wp_send_json_success([
        'title' => $title,
        'content' => $content,
    ]);
}

/**
 * Display menu links from the database in a formatted list
 *
 * @return string HTML output of the menu links
 */
function display_menu_links() {
    // Start output buffering to capture the HTML output
    ob_start();
    
    // Get menu links from the database
    $menu_links = get_help_setting('menu_links', []);
    
    // Check if we have any menu links to display
    if (!empty($menu_links) && is_array($menu_links)) {
        // Add a logo or brand element to the left side
        echo '<div class="menu-brand">';
        echo '<a href="' . admin_url('admin.php?page=booknetic-saas&module=help-center') . '" class="menu-brand-link">';
        
        // Create a custom icon with a stacked effect
        echo '<div class="menu-brand-icon-wrapper">';
        echo '<i class="fas fa-circle menu-brand-icon-bg"></i>';
        echo '<i class="fas fa-question-circle menu-brand-icon"></i>';
        echo '</div>';
        
        echo '<span class="menu-brand-text">' . Helper::getOption('backend_title', 'Help Center') . '</span>';
        echo '</a>';
        echo '</div>';
        
        echo '<ul class="menu-links-list">';
        
        // Sort menu links by order if available
        usort($menu_links, function($a, $b) {
            return isset($a->order) && isset($b->order) ? $a->order - $b->order : 0;
        });
        
        // Default icons mapping based on common link labels
        $default_icons = [
            'documentation' => 'fa-book',
            'support' => 'fa-headset',
            'contact' => 'fa-envelope',
            'forum' => 'fa-comments',
            'faq' => 'fa-question-circle',
            'blog' => 'fa-rss',
            'home' => 'fa-home',
            'dashboard' => 'fa-tachometer-alt',
            'settings' => 'fa-cog',
            'account' => 'fa-user',
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'register' => 'fa-user-plus',
            'download' => 'fa-download',
            'pricing' => 'fa-tags',
            'features' => 'fa-star',
            'about' => 'fa-info-circle',
            'help' => 'fa-question-circle',
            'guide' => 'fa-map-signs',
            'tutorial' => 'fa-chalkboard-teacher',
            'video' => 'fa-video',
            'knowledge' => 'fa-brain',
            'article' => 'fa-file-alt',
            'resource' => 'fa-bookmark'
        ];
        
        // Display each active menu link
        foreach ($menu_links as $link) {
            if (isset($link->active) && $link->active) {
                // Determine icon based on link label or use a default
                $icon_class = 'fa-external-link-alt'; // Default icon
                
                // Check if we have a predefined icon for this link label
                $label_lower = strtolower($link->label);
                foreach ($default_icons as $keyword => $icon) {
                    if (strpos($label_lower, $keyword) !== false) {
                        $icon_class = $icon;
                        break;
                    }
                }
                
                // Ensure URL is properly formed
                $url = !empty($link->url) ? $link->url : '#';
                
                // Add http:// if the URL doesn't have a protocol
                if ($url !== '#' && !preg_match('~^(?:f|ht)tps?://~i', $url)) {
                    $url = 'http://' . $url;
                }
                
                echo '<li class="menu-link-item">';
                echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">';
                echo '<i class="fas ' . esc_attr($icon_class) . ' menu-icon"></i> ';
                echo '<span>' . esc_html($link->label) . '</span>';
                echo '</a>';
                echo '</li>';
            }
        }
        
        echo '</ul>';
    }
    
    // Return the captured output
    return ob_get_clean();
}

/**
 * Prepare and process help topic form submission
 * 
 * This function handles the entire process of adding or updating a help topic:
 * 1. Creates the attachments table if it doesn't exist
 * 2. Processes form submissions
 * 3. Validates input data
 * 4. Inserts or updates the topic in the database
 * 5. Associates attachments with the topic
 * 6. Redirects after successful submission
 *
 * @return array Returns an array with categories, error message (if any), and return URL
 */
function process_help_topic_submission() {
    global $wpdb;
    $error = '';
    
    // Define the URL to return to the topics list
    $list_url = add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php');

    // Fetch all categories for the dropdown
    $categories = $wpdb->get_results(
        "SELECT id, name FROM {$wpdb->prefix}bkntc_help_categories ORDER BY id ASC",
        OBJECT_K
    );

    // Process the form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        // Check if we're editing an existing topic
        $topic_id = 0;
        if (isset($_GET['topic_id'])) {
            $topic_id = intval($_GET['topic_id']);
        } elseif (isset($_GET['edittopic'])) {
            $topic_id = intval($_GET['edittopic']);
        }

        // Validate required fields first
        if (empty($title)) {
            $error = 'Title is required.';
        } else {
            // Prepare sanitized data
            // Use custom sanitization to allow iframes and video embeds
            $allowed_html = array_merge(
                wp_kses_allowed_html('post'),
                [
                    'iframe' => [
                        'src' => true,
                        'width' => true,
                        'height' => true,
                        'frameborder' => true,
                        'allowfullscreen' => true,
                        'allow' => true,
                        'style' => true,
                        'class' => true,
                    ],
                    'video' => [
                        'src' => true,
                        'width' => true,
                        'height' => true,
                        'controls' => true,
                        'autoplay' => true,
                        'muted' => true,
                        'loop' => true,
                        'poster' => true,
                        'preload' => true,
                        'style' => true,
                        'class' => true,
                    ],
                    'source' => [
                        'src' => true,
                        'type' => true,
                        'srcset' => true,
                        'sizes' => true,
                        'media' => true,
                    ]
                ]
            );
            
            $sanitized_content = wp_kses($content, $allowed_html);
            $sanitized_category = $category_id > 0 ? $category_id : 0;

            if ($topic_id > 0) {
                // Update existing topic
                $result = $wpdb->update(
                    "{$wpdb->prefix}bkntc_help_topics",
                    [
                        'title'       => $title,
                        'content'     => $sanitized_content,
                        'category_id' => $sanitized_category,
                    ],
                    ['id' => $topic_id],
                    ['%s', '%s', '%d'],
                    ['%d']
                );

                if ($result !== false) {
                    // Process attachments if any
                    if (isset($_POST['attachments']) && !empty($_POST['attachments'])) {
                        $attachment_ids = explode(',', sanitize_text_field($_POST['attachments']));
                        
                        if (!empty($attachment_ids)) {
                            // First, get all current attachments for this topic
                            $current_attachments = $wpdb->get_col($wpdb->prepare(
                                "SELECT id FROM {$wpdb->prefix}bkntc_help_attachments WHERE topic_id = %d",
                                $topic_id
                            ));
                            
                            // Find attachments to remove (in current but not in new list)
                            $attachments_to_remove = array_diff($current_attachments, $attachment_ids);
                            
                            // Remove attachments that are no longer associated with this topic
                            if (!empty($attachments_to_remove)) {
                                foreach ($attachments_to_remove as $attachment_id) {
                                    // Set topic_id to NULL instead of deleting to keep the file available
                                    $wpdb->update(
                                        "{$wpdb->prefix}bkntc_help_attachments",
                                        ['topic_id' => NULL],
                                        ['id' => intval($attachment_id)],
                                        ['%s'],
                                        ['%d']
                                    );
                                }
                            }
                            
                            // Update new attachments with this topic ID
                            foreach ($attachment_ids as $attachment_id) {
                                $wpdb->update(
                                    "{$wpdb->prefix}bkntc_help_attachments",
                                    ['topic_id' => $topic_id],
                                    ['id' => intval($attachment_id)],
                                    ['%d'],
                                    ['%d']
                                );
                            }
                        }
                    } else {
                        // If no attachments were submitted, remove all attachments for this topic
                        $wpdb->update(
                            "{$wpdb->prefix}bkntc_help_attachments",
                            ['topic_id' => NULL],
                            ['topic_id' => $topic_id],
                            ['%s'],
                            ['%d']
                        );
                    }

                    // Redirect with success
                    $redirect_url = add_query_arg(['action_status' => 'updated'], $list_url);
                    if (!headers_sent()) {
                        wp_redirect(esc_url_raw($redirect_url));
                        exit;
                    } else {
                        echo '<script type="text/javascript">';
                        echo 'window.location.href="' . html_entity_decode( esc_url( $redirect_url ) ) . '";';
                        echo '</script>';
                        exit;
                    }
                } else {
                    $error = "Error updating topic: " . $wpdb->last_error;
                }
            } else {
                // Insert new topic
                $result = $wpdb->insert(
                    "{$wpdb->prefix}bkntc_help_topics",
                    [
                        'title'       => $title,
                        'content'     => $sanitized_content,
                        'category_id' => $sanitized_category,
                        'created_at'  => current_time('mysql'),
                    ],
                    ['%s', '%s', '%d', '%s']
                );

                if ($result !== false) {
                    $new_topic_id = $wpdb->insert_id;
                    
                    // Process attachments if any
                    if (isset($_POST['attachments']) && !empty($_POST['attachments'])) {
                        $attachment_ids = explode(',', sanitize_text_field($_POST['attachments']));
                        
                        if (!empty($attachment_ids)) {
                            foreach ($attachment_ids as $attachment_id) {
                                // Update the attachment record with the topic ID
                                $wpdb->update(
                                    "{$wpdb->prefix}bkntc_help_attachments",
                                    ['topic_id' => $new_topic_id],
                                    ['id' => intval($attachment_id)],
                                    ['%d'],
                                    ['%d']
                                );
                            }
                        }
                    }

                    // Redirect with success
                    $redirect_url = add_query_arg(['action_status' => 'added'], $list_url);
                    if (!headers_sent()) {
                        wp_redirect(esc_url_raw($redirect_url));
                        exit;
                    } else {
                        echo '<script type="text/javascript">';
                        echo 'window.location.href="' . html_entity_decode( esc_url( $redirect_url ) ) . '";';
                        echo '</script>';
                        exit;
                    }
                } else {
                    $error = 'Error: Could not add the topic. Database error: ' . $wpdb->last_error;
                }
            }
        }
    }
    
    // Return data for the template
    return [
        'categories' => $categories,
        'error' => $error,
        'list_url' => $list_url
    ];
}


/**
 * Get appropriate icon class based on file type
 */
function get_file_icon_class($fileType) {
    switch($fileType) {
        case 'pdf':
            return 'fas fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint';
        case 'zip':
        case 'rar':
            return 'fas fa-file-archive';
        case 'txt':
            return 'fas fa-file-alt';
        default:
            return 'fas fa-file';
    }
}

/**
 * Format file size in human-readable format
 */
function format_file_size($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}



/**
 * Get topic data for editing
 *
 * @return array Array containing topic data, categories, and attachments
 */
function get_topic_data_for_edit() {
    global $wpdb;
    
    // Retrieve the topic ID from the URL. Check for either 'topic_id' or 'edittopic'.
    if ( isset($_GET['topic_id']) ) {
        $topic_id = intval($_GET['topic_id']);
    } elseif ( isset($_GET['edittopic']) ) {
        $topic_id = intval($_GET['edittopic']);
    } else {
        $topic_id = 0;
    }

    if ( $topic_id <= 0 ) {
        wp_die("Invalid topic ID.");
    }

    // Fetch the topic data as an object
    $topicData = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}bkntc_help_topics WHERE id = %d", $topic_id)
    );
    
    if ( ! $topicData ) {
        wp_die("Topic not found.");
    }

    // Fetch all categories (keyed by id) for the category dropdown.
    $categories = $wpdb->get_results(
        "SELECT id, name FROM {$wpdb->prefix}bkntc_help_categories ORDER BY id ASC",
        OBJECT_K
    );

    // Fetch existing attachments for this topic
    $attachments = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkntc_help_attachments WHERE topic_id = %d ORDER BY uploaded_at DESC",
            $topic_id
        )
    );
    
    return [
        'topic_id' => $topic_id,
        'topicData' => $topicData,
        'categories' => $categories,
        'attachments' => $attachments
    ];
}