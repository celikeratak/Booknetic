<?php
defined('ABSPATH') or die();

/**
 * Handle routing for different pages in the help center
 * 
 * @return bool True if a route was matched and handled, false otherwise
 */
function handle_help_center_routing() {
    // Get the view parameter
    $view = isset($_GET['view']) ? sanitize_key($_GET['view']) : '';
    
    // Route based on the view parameter
    switch ($view) {
        case 'dashboard':
            include __DIR__ . '/../view/dashboard.php';
            return true;
            
        case 'topics':
            if (isset($_GET['task'])) {
                if ($_GET['task'] === 'add') {
                    // For adding a new topic
                    define('BOOKNETIC_ADDING_NEW_TOPIC', true);
                    include __DIR__ . '/../view/topics-add.php';
                } else if ($_GET['task'] === 'edit' && isset($_GET['topic_id'])) {
                    // For editing an existing topic
                    define('BOOKNETIC_EDITING_TOPIC', true);
                    include __DIR__ . '/../view/topics-edit.php';
                } else if ($_GET['task'] === 'delete' && isset($_GET['topic_id'])) {
                    // For deleting a topic - use manage-topics.php which has the deletion logic
                    include __DIR__ . '/../view/manage-topics.php';
                } else {
                    // Default to the topics list for unknown tasks
                    include __DIR__ . '/../view/manage-topics.php';
                }
            } else {
                // For viewing the topics list
                include __DIR__ . '/../view/manage-topics.php';
            }
            return true;

        case 'reorder_categories':
            include __DIR__ . '/../view/reorder-categories.php';
            return true;
            
        case 'categories':
            if (isset($_GET['task']) && $_GET['task'] === 'reorder') {
                include __DIR__ . '/../view/reorder-categories.php';
            } else {
                include __DIR__ . '/../view/manage-categories.php';
            }
            return true;
            
        case 'settings':
        case 'social_media': // Support both 'settings' and 'social_media' for the same view
            include __DIR__ . '/../view/settings.php';
            return true;
            
        case 'addons':
            include __DIR__ . '/../view/recommended-addons.php';
            return true;
            
        case 'livechat':
            include __DIR__ . '/../view/subpage.php';
            return true;
    }

    return false;
}
    
