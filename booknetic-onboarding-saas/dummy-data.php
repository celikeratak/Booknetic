<?php
/**
 * Booknetic Help Center Dummy Data Generator
 *
 * This script adds sample data to the Booknetic Help Center including:
 * - Categories
 * - Topics
 * - Views
 * - Search logs
 */

defined('ABSPATH') or die('Direct access not allowed!');

// Only administrators should access this page
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

/**
 * Add dummy data to the Booknetic Help Center
 */
function booknetic_help_center_add_dummy_data() {
    global $wpdb;
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    // Fix topic_feedback table structure
    fix_topic_feedback_table();
    
    try {
        // Clear existing data if requested
        if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'yes') {
            // First delete from search_logs (no foreign key constraints)
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}bkntc_search_logs");
            
            // Then delete from help_topics (has foreign key to help_categories)
            $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_help_topics");
            
            // Finally delete from help_categories
            $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_help_categories");
            
            // Reset auto-increment values
            $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_help_categories AUTO_INCREMENT = 1");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_help_topics AUTO_INCREMENT = 1");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_search_logs AUTO_INCREMENT = 1");
        }
        
        // Add categories
        $categories = add_dummy_categories();
        
        // Add topics
        $topics = add_dummy_topics($categories);
        
        // Add search logs
        $search_logs = add_dummy_search_logs($topics);
        
        // Add help settings (social media links, menu links, support link, footer text, custom CSS, color settings)
        add_dummy_help_settings();
        
        // Add topic feedback data
        add_dummy_feedback_data();
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        return [
            'status' => true,
            'message' => 'Dummy data added successfully!',
            'categories_added' => count($categories),
            'topics_added' => count($topics),
            'settings_added' => true,
            'settings_details' => [
                'social_media_links' => 5,
                'menu_links' => 3,
                'support_link' => 1,
                'color_settings' => 2,
                'custom_css' => 1,
                'footer_text' => 1,
                'livechat_settings' => 4,
                'feedback_data' => 50
            ],
            'search_logs_added' => count($search_logs)
        ];
    } catch (Exception $e) {
        // Rollback transaction on error
        $wpdb->query('ROLLBACK');
        
        return [
            'status' => false,
            'message' => 'Error adding dummy data: ' . $e->getMessage()
        ];
    }
}

/**
 * Add dummy categories
 *
 * @return array Category IDs
 */
function add_dummy_categories() {
    global $wpdb;
    
    // Delete existing categories
    $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_help_categories");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_help_categories AUTO_INCREMENT = 1");
    
    $categories = [
        [
            'name' => 'Getting Started',
            'icon' => 'fa fa-rocket',
            'order_number' => 1
        ],
        [
            'name' => 'Account Management',
            'icon' => 'fa fa-user',
            'order_number' => 2
        ],
        [
            'name' => 'Booking Process',
            'icon' => 'fa fa-calendar',
            'order_number' => 3
        ],
        [
            'name' => 'Payment Options',
            'icon' => 'fa fa-credit-card',
            'order_number' => 4
        ],
        [
            'name' => 'Staff Management',
            'icon' => 'fa fa-users',
            'order_number' => 5
        ],
        [
            'name' => 'Service Configuration',
            'icon' => 'fa fa-cogs',
            'order_number' => 6
        ],
        [
            'name' => 'Customer Experience',
            'icon' => 'fa fa-heart',
            'order_number' => 7
        ],
        [
            'name' => 'Troubleshooting',
            'icon' => 'fa fa-wrench',
            'order_number' => 8
        ],
        [
            'name' => 'Integrations',
            'icon' => 'fa fa-plug',
            'order_number' => 9
        ],
        [
            'name' => 'Mobile App',
            'icon' => 'fa fa-mobile',
            'order_number' => 10
        ],
        [
            'name' => 'Advanced Features',
            'icon' => 'fa fa-star',
            'order_number' => 11
        ],
        [
            'name' => 'Updates & Releases',
            'icon' => 'fa fa-refresh',
            'order_number' => 12
        ]
    ];
    
    $category_ids = [];
    
    foreach ($categories as $category) {
        $wpdb->insert(
            "{$wpdb->prefix}bkntc_help_categories",
            [
                'name' => $category['name'],
                'icon' => $category['icon'],
                'order_number' => $category['order_number']
            ],
            ['%s', '%s', '%d']
        );
        
        $category_ids[] = $wpdb->insert_id;
    }
    
    return $category_ids;
}

/**
 * Add dummy topics
 *
 * @param array $category_ids Category IDs
 * @return array Topic IDs
 */
function add_dummy_topics($category_ids) {
    global $wpdb;
    
    // Delete existing topics
    $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_help_topics");
    
    // Delete existing feedback
    $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_topic_feedback");
    
    // Reset auto increment
    $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_help_topics AUTO_INCREMENT = 1");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_topic_feedback AUTO_INCREMENT = 1");
    
    // Content templates
    $templates = [
        // Template 1: Requires 4 placeholders
        '<h2>Quick Start</h2><p>This quick guide will help you %s in just a few minutes. Follow these simple instructions to get up and running.</p><h2>Prerequisites</h2><ul><li>Active Booknetic subscription</li><li>Administrator access to your WordPress site</li><li>Basic understanding of %s</li></ul><h2>Setup Process</h2><ol><li>Navigate to the Booknetic dashboard</li><li>Select \'%s\' from the menu</li><li>Configure the basic settings</li><li>Add your %s information</li><li>Test the functionality</li></ol><h2>Next Steps</h2><p>After completing the initial setup, consider these next steps:</p><ul><li>Customize the appearance to match your brand</li><li>Set up email notifications</li><li>Configure payment gateways</li><li>Create booking rules</li></ul>',
        
        // Template 2: Requires 6 placeholders
        '<h2>Overview</h2><p>%s is a powerful feature that allows you to %s with ease. This article explains how to make the most of it.</p><h2>Key Features</h2><ul><li><strong>%s:</strong> Enables you to streamline your workflow</li><li><strong>Customization:</strong> Adapt the settings to your specific needs</li><li><strong>Reporting:</strong> Get insights into your %s</li></ul><h2>Configuration</h2><p>To configure %s:</p><ol><li>Go to Settings > %s</li><li>Enable the feature by toggling the switch</li><li>Adjust the parameters according to your preferences</li><li>Click \'Save Changes\'</li></ol><h2>Advanced Tips</h2><p>To get the most out of this feature:</p><ul><li>Regularly update your settings</li><li>Use keyboard shortcuts for faster navigation</li><li>Customize the display options to suit your workflow</li></ul>',
        
        // Template 3: Requires 5 placeholders
        '<h2>Introduction</h2><p>%s is an essential part of managing your booking system effectively. This guide will walk you through the process step by step.</p><h2>Getting Started</h2><ol><li>Log in to your Booknetic dashboard</li><li>Navigate to the %s section</li><li>Review the current settings</li></ol><h2>Key Configuration Options</h2><ul><li><strong>%s:</strong> Define how your %s behaves</li><li><strong>Display Options:</strong> Customize what clients see</li><li><strong>Notification Settings:</strong> Configure alerts for important events</li></ul><h2>Best Practices</h2><p>For optimal results:</p><ul><li>Regularly review and update your settings</li><li>Test the user experience from a client perspective</li><li>Use the %s feature to enhance functionality</li></ul><h2>Troubleshooting</h2><p>If you encounter issues:</p><ol><li>Check your configuration settings</li><li>Clear your browser cache</li><li>Ensure all required fields are completed</li><li>Contact support if problems persist</li></ol>'
    ];
    
    // Topics for each category
    $topics_by_category = [
        // Getting Started
        1 => [
            ['title' => 'Setting Up Your First Service', 'views' => rand(5, 30), 'content_vars' => ['set up your first service', 'service configuration', 'Services', 'service details']],
            ['title' => 'Creating Staff Profiles', 'views' => rand(5, 30), 'content_vars' => ['create staff profiles', 'staff management', 'Staff', 'staff member']],
            ['title' => 'Configuring Business Hours', 'views' => rand(5, 30), 'content_vars' => ['configure business hours', 'scheduling', 'Company Settings', 'working hours']],
            ['title' => 'Setting Up Your Booking Page', 'views' => rand(5, 30), 'content_vars' => ['customize your booking page', 'WordPress integration', 'Appearance', 'booking form']],
            ['title' => 'Understanding the Dashboard', 'views' => rand(5, 30), 'content_vars' => ['navigate the dashboard', 'admin interface', 'Dashboard', 'analytics data']]
        ],
        
        // Account Management
        2 => [
            ['title' => 'Managing User Roles and Permissions', 'views' => rand(5, 30), 'content_vars' => ['User Management', 'control access levels', 'Permission System', 'role assignments', 'User Management', 'Permissions']],
            ['title' => 'Updating Your Account Information', 'views' => rand(5, 30), 'content_vars' => ['Account Settings', 'keep your details current', 'Profile Management', 'contact information', 'Account Settings', 'Profile']],
            ['title' => 'Subscription Management', 'views' => rand(5, 30), 'content_vars' => ['Subscription Control', 'manage your plan', 'Billing', 'payment methods', 'Subscription Control', 'Billing']],
            ['title' => 'Multi-Location Setup', 'views' => rand(5, 30), 'content_vars' => ['Location Management', 'manage multiple business locations', 'Locations', 'address details', 'Location Management', 'Locations']],
            ['title' => 'Data Export and Backup', 'views' => rand(5, 30), 'content_vars' => ['Data Management', 'secure your information', 'Tools', 'backup schedule', 'Data Management', 'Tools']]
        ],
        
        // Booking Process
        3 => [
            ['title' => 'Creating a New Appointment', 'views' => rand(5, 30), 'content_vars' => ['Appointment Creation', 'book appointments', 'New Appointment', 'customer details', 'Appointment Creation']],
            ['title' => 'Managing the Appointment Calendar', 'views' => rand(5, 30), 'content_vars' => ['Calendar Management', 'organize your schedule', 'Calendar View', 'appointment slots', 'Calendar Management']],
            ['title' => 'Setting Up Recurring Appointments', 'views' => rand(5, 30), 'content_vars' => ['Recurring Bookings', 'schedule regular sessions', 'Appointment Options', 'recurrence pattern', 'Recurring Bookings']],
            ['title' => 'Handling Appointment Cancellations', 'views' => rand(5, 30), 'content_vars' => ['Cancellation Management', 'process cancellations efficiently', 'Appointment Actions', 'cancellation policy', 'Cancellation Management']],
            ['title' => 'Group Booking Configuration', 'views' => rand(5, 30), 'content_vars' => ['Group Appointments', 'manage multiple attendees', 'Capacity Settings', 'maximum participants', 'Group Appointments']]
        ],
        
        // Payment Options
        4 => [
            ['title' => 'Setting Up Payment Gateways', 'views' => rand(5, 30), 'content_vars' => ['Payment Integration', 'configure payment methods', 'Payment Settings', 'API credentials', 'Payment Integration', 'Payment Settings']],
            ['title' => 'Creating Custom Price Rules', 'views' => rand(5, 30), 'content_vars' => ['Price Management', 'customize your pricing', 'Price Rules', 'special offers', 'Price Management', 'Price Rules']],
            ['title' => 'Managing Deposits and Full Payments', 'views' => rand(5, 30), 'content_vars' => ['Deposit System', 'handle partial payments', 'Payment Options', 'deposit percentage', 'Deposit System', 'Payment Options']],
            ['title' => 'Setting Up Taxes and Fees', 'views' => rand(5, 30), 'content_vars' => ['Tax Configuration', 'manage additional charges', 'Tax Settings', 'tax rates', 'Tax Configuration', 'Tax Settings']],
            ['title' => 'Processing Refunds', 'views' => rand(5, 30), 'content_vars' => ['Refund Management', 'handle customer refunds', 'Payments', 'refund policy', 'Refund Management', 'Payments']]
        ],
        
        // Staff Management
        5 => [
            ['title' => 'Adding New Staff Members', 'views' => rand(5, 30), 'content_vars' => ['Staff Addition', 'add staff to your system', 'Staff', 'staff profiles', 'Staff Addition', 'Staff']],
            ['title' => 'Setting Staff Working Hours', 'views' => rand(5, 30), 'content_vars' => ['Schedule Management', 'configure availability', 'Working Hours', 'shift patterns', 'Schedule Management', 'Working Hours']],
            ['title' => 'Managing Staff Services', 'views' => rand(5, 30), 'content_vars' => ['Service Assignment', 'connect staff to services', 'Staff Services', 'service capabilities', 'Service Assignment', 'Staff Services']],
            ['title' => 'Staff Performance Reports', 'views' => rand(5, 30), 'content_vars' => ['Performance Analytics', 'track staff metrics', 'Reports', 'booking statistics', 'Performance Analytics', 'Reports']],
            ['title' => 'Setting Up Staff Commissions', 'views' => rand(5, 30), 'content_vars' => ['Commission System', 'manage staff payments', 'Payment Settings', 'commission rates', 'Commission System', 'Payment Settings']]
        ],
        
        // Service Configuration
        6 => [
            ['title' => 'Creating Service Categories', 'views' => rand(5, 30), 'content_vars' => ['Category Organization', 'organize your services', 'Categories', 'grouping strategy', 'Category Organization', 'Categories']],
            ['title' => 'Setting Up Service Extras', 'views' => rand(5, 30), 'content_vars' => ['Add-on Management', 'enhance your offerings', 'Extras', 'additional options', 'Add-on Management', 'Extras']],
            ['title' => 'Configuring Service Duration', 'views' => rand(5, 30), 'content_vars' => ['Time Management', 'set appointment length', 'Duration Settings', 'buffer time', 'Time Management', 'Duration Settings']],
            ['title' => 'Managing Service Availability', 'views' => rand(5, 30), 'content_vars' => ['Availability Control', 'set service hours', 'Availability', 'special hours', 'Availability Control', 'Availability']],
            ['title' => 'Setting Up Capacity and Resources', 'views' => rand(5, 30), 'content_vars' => ['Resource Management', 'manage service capacity', 'Capacity Settings', 'maximum bookings', 'Resource Management', 'Capacity Settings']]
        ],
        
        // Customer Experience
        7 => [
            ['title' => 'Customizing the Booking Form', 'views' => rand(5, 30), 'content_vars' => ['Form Customization', 'enhance user experience', 'Form Builder', 'custom fields', 'Form Customization', 'Form Builder']],
            ['title' => 'Setting Up Email Notifications', 'views' => rand(5, 30), 'content_vars' => ['Notification System', 'communicate with clients', 'Email Templates', 'message content', 'Notification System', 'Email Templates']],
            ['title' => 'Creating Custom Email Templates', 'views' => rand(5, 30), 'content_vars' => ['Template Design', 'personalize communications', 'Email Editor', 'template variables', 'Template Design', 'Email Editor']],
            ['title' => 'Managing Customer Profiles', 'views' => rand(5, 30), 'content_vars' => ['Customer Database', 'organize client information', 'Customers', 'profile details', 'Customer Database', 'Customers']],
            ['title' => 'Setting Up SMS Notifications', 'views' => rand(5, 30), 'content_vars' => ['SMS Alerts', 'send text reminders', 'SMS Settings', 'message templates', 'SMS Alerts', 'SMS Settings']]
        ],
        
        // Troubleshooting
        8 => [
            ['title' => 'Fixing Payment Gateway Errors', 'views' => rand(5, 30), 'content_vars' => ['Payment Troubleshooting', 'troubleshoot payment issues', 'Gateway Settings', 'verify API credentials', 'Payment Troubleshooting', 'Gateway Settings']],
            ['title' => 'Resolving Email Notification Problems', 'views' => rand(5, 30), 'content_vars' => ['Email Debugging', 'fix email delivery', 'Email Settings', 'test different email services', 'Email Debugging', 'Email Settings']],
            ['title' => 'Troubleshooting Booking Form Errors', 'views' => rand(5, 30), 'content_vars' => ['Form Debugging', 'resolve booking issues', 'Validation', 'inspect error messages', 'Form Debugging', 'Validation']],
            ['title' => 'Calendar Synchronization Issues', 'views' => rand(5, 30), 'content_vars' => ['Sync Troubleshooting', 'fix calendar sync', 'Sync Settings', 'reconnect accounts', 'Sync Troubleshooting', 'Sync Settings']],
            ['title' => 'Resolving Plugin Conflicts', 'views' => rand(5, 30), 'content_vars' => ['Compatibility Issues', 'fix plugin compatibility', 'Compatibility', 'disable conflicting plugins', 'Compatibility Issues', 'Compatibility']]
        ],
        
        // Integrations
        9 => [
            ['title' => 'Google Calendar Integration', 'views' => rand(5, 30), 'content_vars' => ['Google Sync', 'sync with Google Calendar', 'Google Settings', 'API connection', 'Google Sync', 'Google Settings']],
            ['title' => 'Zoom Meeting Integration', 'views' => rand(5, 30), 'content_vars' => ['Zoom Meetings', 'connect with Zoom', 'Zoom Settings', 'API credentials', 'Zoom Meetings', 'Zoom Settings']],
            ['title' => 'WooCommerce Integration', 'views' => rand(5, 30), 'content_vars' => ['WooCommerce Connection', 'connect with WooCommerce', 'WooCommerce Settings', 'product linking', 'WooCommerce Connection', 'WooCommerce Settings']],
            ['title' => 'Mailchimp Integration', 'views' => rand(5, 30), 'content_vars' => ['Mailchimp Setup', 'connect with Mailchimp', 'Mailchimp Settings', 'list selection', 'Mailchimp Setup', 'Mailchimp Settings']],
            ['title' => 'Zapier Integration', 'views' => rand(5, 30), 'content_vars' => ['Zapier Automation', 'automate with Zapier', 'Zapier Settings', 'trigger setup', 'Zapier Automation', 'Zapier Settings']]
        ],
        
        // Mobile App
        10 => [
            ['title' => 'Getting Started with the Mobile App', 'views' => rand(5, 30), 'content_vars' => ['Mobile Introduction', 'use the mobile app', 'App Download', 'login credentials', 'Mobile Introduction', 'App Download']],
            ['title' => 'Managing Appointments on Mobile', 'views' => rand(5, 30), 'content_vars' => ['Mobile Booking', 'manage on the go', 'Appointment View', 'mobile interface', 'Mobile Booking', 'Appointment View']],
            ['title' => 'Mobile App Notifications', 'views' => rand(5, 30), 'content_vars' => ['Mobile Alerts', 'stay informed', 'Notification Settings', 'alert preferences', 'Mobile Alerts', 'Notification Settings']],
            ['title' => 'Offline Mode Features', 'views' => rand(5, 30), 'content_vars' => ['Offline Access', 'work without internet', 'Offline Mode', 'data synchronization', 'Offline Access', 'Offline Mode']],
            ['title' => 'Mobile Payment Processing', 'views' => rand(5, 30), 'content_vars' => ['Mobile Payments', 'process transactions', 'Payment Options', 'mobile security', 'Mobile Payments', 'Payment Options']]
        ],
        
        // Advanced Features
        11 => [
            ['title' => 'Custom Fields and Forms', 'views' => rand(5, 30), 'content_vars' => ['Form Customization', 'collect specific information', 'Custom Fields', 'field types', 'Form Customization', 'Custom Fields']],
            ['title' => 'Setting Up Conditional Logic', 'views' => rand(5, 30), 'content_vars' => ['Conditional Rules', 'create dynamic forms', 'Logic Builder', 'if-then scenarios', 'Conditional Rules', 'Logic Builder']],
            ['title' => 'Advanced Booking Rules', 'views' => rand(5, 30), 'content_vars' => ['Booking Restrictions', 'set complex availability', 'Rules Engine', 'time constraints', 'Booking Restrictions', 'Rules Engine']],
            ['title' => 'API Integration Options', 'views' => rand(5, 30), 'content_vars' => ['API Connectivity', 'connect with external systems', 'API Documentation', 'authentication methods', 'API Connectivity', 'API Documentation']],
            ['title' => 'Custom CSS and Styling', 'views' => rand(5, 30), 'content_vars' => ['Visual Customization', 'personalize the appearance', 'CSS Editor', 'style properties', 'Visual Customization', 'CSS Editor']]
        ],
        
        // Updates & Releases
        12 => [
            ['title' => 'Release Notes and Changelog', 'views' => rand(5, 30), 'content_vars' => ['Version History', 'track software changes', 'Changelog', 'feature additions', 'Version History', 'Changelog']],
            ['title' => 'How to Update Booknetic', 'views' => rand(5, 30), 'content_vars' => ['Update Process', 'update your plugin', 'Updates', 'backup before updating', 'Update Process', 'Updates']],
            ['title' => 'Beta Features and Testing', 'views' => rand(5, 30), 'content_vars' => ['Beta Program', 'try new features', 'Beta Settings', 'feedback submission', 'Beta Program', 'Beta Settings']],
            ['title' => 'Deprecated Features', 'views' => rand(5, 30), 'content_vars' => ['Feature Lifecycle', 'understand changes', 'Changelog', 'alternative options', 'Feature Lifecycle', 'Changelog']],
            ['title' => 'Roadmap and Future Releases', 'views' => rand(5, 30), 'content_vars' => ['Future Plans', 'preview coming features', 'Roadmap', 'planned enhancements', 'Future Plans', 'Roadmap']]
        ]
    ];
    
    $topic_ids = [];
    $current_time = current_time('mysql');
    $one_day = 24 * 60 * 60;
    
    foreach ($topics_by_category as $category_id => $topics) {
        foreach ($topics as $topic) {
            // Select a random template
            $template_index = array_rand($templates);
            $template = $templates[$template_index];
            
            // Make sure we have the right number of content variables for the selected template
            $required_vars = 0;
            if ($template_index == 0) {
                $required_vars = 4; // Template 1 requires 4 placeholders
            } elseif ($template_index == 1) {
                $required_vars = 6; // Template 2 requires 6 placeholders
            } elseif ($template_index == 2) {
                $required_vars = 5; // Template 3 requires 5 placeholders
            }
            
            // If we don't have enough content variables for this template, pick a different template
            if (count($topic['content_vars']) < $required_vars) {
                // Find a template that matches our content_vars count
                foreach ([0, 1, 2] as $idx) {
                    $needed = ($idx == 0) ? 4 : (($idx == 1) ? 6 : 5);
                    if (count($topic['content_vars']) >= $needed) {
                        $template_index = $idx;
                        $template = $templates[$idx];
                        $required_vars = $needed;
                        break;
                    }
                }
                
                // If we still don't have a matching template, pad the content_vars array
                if (count($topic['content_vars']) < $required_vars) {
                    while (count($topic['content_vars']) < $required_vars) {
                        $topic['content_vars'][] = 'additional content';
                    }
                }
            }
            
            // Generate content by replacing placeholders with variables
            $content = vsprintf($template, array_slice($topic['content_vars'], 0, $required_vars));
            
            // Calculate a random creation date within the last year
            $random_days_ago = rand(0, 365);
            $created_at = date('Y-m-d H:i:s', strtotime($current_time) - ($random_days_ago * $one_day));
            
            // Generate random feedback counts
            $feedback_yes = rand(5, 50);
            $feedback_no = rand(0, 10);
            
            $wpdb->insert(
                "{$wpdb->prefix}bkntc_help_topics",
                [
                    'title' => $topic['title'],
                    'content' => $content,
                    'category_id' => $category_id,
                    'views' => $topic['views'],
                    'created_at' => $created_at
                ],
                ['%s', '%s', '%d', '%d', '%s']
            );
            
            $topic_id = $wpdb->insert_id;
            $topic_ids[] = $topic_id;
            
            // Add dummy feedback entries to the topic_feedback table
            // Keep track of users who have already voted on this topic
            $voted_users = [];
            
            // Add "yes" feedback
            for ($i = 0; $i < min($feedback_yes, 4); $i++) {
                $random_days_ago = rand(0, 365);
                $feedback_created_at = date('Y-m-d H:i:s', strtotime($current_time) - ($random_days_ago * $one_day));
                
                // Generate a unique user ID that hasn't voted on this topic yet
                do {
                    $user_id = rand(10000, 100000);
                } while (in_array($user_id, $voted_users));
                
                $voted_users[] = $user_id;
                
                $wpdb->insert(
                    "{$wpdb->prefix}bkntc_topic_feedback",
                    [
                        'topic_id' => $topic_id,
                        'feedback' => 'yes',
                        'user_id' => $user_id,
                        'created_at' => $feedback_created_at
                    ],
                    ['%d', '%s', '%d', '%s']
                );
            }
            
            // Add "no" feedback
            for ($i = 0; $i < min($feedback_no, 4); $i++) {
                $random_days_ago = rand(0, 365);
                $feedback_created_at = date('Y-m-d H:i:s', strtotime($current_time) - ($random_days_ago * $one_day));
                
                // Generate a unique user ID that hasn't voted on this topic yet
                do {
                    $user_id = rand(1, 500);
                } while (in_array($user_id, $voted_users));
                
                $voted_users[] = $user_id;
                
                $wpdb->insert(
                    "{$wpdb->prefix}bkntc_topic_feedback",
                    [
                        'topic_id' => $topic_id,
                        'feedback' => 'no',
                        'user_id' => $user_id,
                        'created_at' => $feedback_created_at
                    ],
                    ['%d', '%s', '%d', '%s']
                );
            }
        }
    }
    
    return $topic_ids;
}

/**
 * Add dummy help settings
 * 
 * This function adds dummy data for all required settings including:
 * - Social media links
 * - Menu links
 * - Support link
 * - Footer text
 * - Custom CSS
 * - Color settings
 */
function add_dummy_help_settings() {
    global $wpdb;
    
    // Clear existing settings if needed
    if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'yes') {
        $wpdb->query("DELETE FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name NOT IN ('help_center_title', 'help_center_subtitle')");
    }
    
    // 1. Social Media Links
    $social_media_links = [
        [
            'platform' => 'Facebook',
            'icon' => 'fab fa-facebook-f',
            'url' => 'https://facebook.com/booknetic',
            'display_order' => 1,
            'active' => 1,
            'id' => 1
        ],
        [
            'platform' => 'Twitter',
            'icon' => 'fab fa-twitter',
            'url' => 'https://twitter.com/booknetic',
            'display_order' => 2,
            'active' => 1,
            'id' => 2
        ],
        [
            'platform' => 'Instagram',
            'icon' => 'fab fa-instagram',
            'url' => 'https://instagram.com/booknetic',
            'display_order' => 3,
            'active' => 1,
            'id' => 3
        ],
        [
            'platform' => 'LinkedIn',
            'icon' => 'fab fa-linkedin-in',
            'url' => 'https://linkedin.com/company/booknetic',
            'display_order' => 4,
            'active' => 1,
            'id' => 4
        ],
        [
            'platform' => 'YouTube',
            'icon' => 'fab fa-youtube',
            'url' => 'https://youtube.com/c/booknetic',
            'display_order' => 5,
            'active' => 1,
            'id' => 5
        ]
    ];
    
    // Convert to objects for serialization
    $social_media_objects = [];
    foreach ($social_media_links as $link) {
        $obj = new \stdClass();
        foreach ($link as $key => $value) {
            $obj->$key = $value;
        }
        $social_media_objects[] = $obj;
    }
    
    // Insert social media links
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'social_media_links',
        serialize($social_media_objects)
    ));
    
    // 2. Menu Links
    $menu_links = [
        [
            'label' => 'Documentation',
            'url' => 'https://booknetic.com/documentation/',
            'target' => '_blank',
            'order' => 1,
            'active' => 1,
            'id' => 1
        ],
        [
            'label' => 'Video Tutorials',
            'url' => 'https://booknetic.com/tutorials/',
            'target' => '_blank',
            'order' => 2,
            'active' => 1,
            'id' => 2
        ],
        [
            'label' => 'Blog',
            'url' => 'https://booknetic.com/blog/',
            'target' => '_blank',
            'order' => 3,
            'active' => 1,
            'id' => 3
        ]
    ];
    
    // Convert to objects for serialization
    $menu_link_objects = [];
    foreach ($menu_links as $link) {
        $obj = new \stdClass();
        foreach ($link as $key => $value) {
            $obj->$key = $value;
        }
        $menu_link_objects[] = $obj;
    }
    
    // Insert menu links
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'menu_links',
        serialize($menu_link_objects)
    ));
    
    // 3. Support Link
    $support_link = new \stdClass();
    $support_link->label = 'Contact Support';
    $support_link->url = 'https://booknetic.com/support/';
    $support_link->active = 1;
    $support_link->id = 1;
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'support_link',
        serialize($support_link)
    ));
    
    // 4. Footer Text
    $footer_text = '© ' . date('Y') . ' Booknetic. All rights reserved. <a href="https://booknetic.com/terms/">Terms of Service</a> | <a href="https://booknetic.com/privacy/">Privacy Policy</a>';
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'footer_text',
        $footer_text
    ));
    
    // 5. Custom CSS
    $custom_css = <<<CSS
/* Custom CSS for Help Center */
.help-center-container {
    font-family: 'Poppins', sans-serif;
}

.help-center-header {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
}

.help-center-search input {
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.topic-list-item {
    border-left: 3px solid transparent;
    transition: border-color 0.2s ease;
}

.topic-list-item:hover {
    border-left-color: #2575fc;
}

.social-media-icons a {
    transition: transform 0.2s ease;
}

.social-media-icons a:hover {
    transform: scale(1.2);
}

.help-center-footer {
    background-color: #f8f9fa;
}
CSS;
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'custom_css',
        $custom_css
    ));
    
    // 6. Color Settings
    // Create serialized array with primary and secondary colors
    $color_settings = array(
        'primary_color' => '#9C27B0',
        'secondary_color' => '#BA68C8'
    );
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'color_settings',
        serialize($color_settings)
    ));
    
    // 7. Help Center Title and Subtitle (if they don't exist)
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'help_center_title',
        'Booknetic Help Center'
    ));
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
        VALUES (%s, %s) 
        ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
        'help_center_subtitle',
        'Find answers to all your questions about Booknetic'
    ));
    
    // 8. LiveChat Settings
    $livechat_settings = [
        'livechat_title' => 'Need Help?',
        'livechat_subtitle' => 'Chat with our support team',
        'livechat_embed_code' => '<!-- Livechat embed code would go here --><div id="dummy-livechat-container" style="width:100%;height:400px;border:1px solid #eee;border-radius:5px;padding:15px;text-align:center;"><p style="margin-top:180px;">Livechat Widget Placeholder</p></div>',
        'livechat_icon' => 'fa fa-comment'
    ];
    
    foreach ($livechat_settings as $option_name => $option_value) {
        $wpdb->query($wpdb->prepare(
            "INSERT INTO `{$wpdb->prefix}bkntc_help_settings` (`option_name`, `option_value`) 
            VALUES (%s, %s) 
            ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`), `updated_at` = NOW()",
            $option_name,
            $option_value
        ));
    }
}

/**
 * Add dummy search logs
 *
 * @param array $topic_ids Topic IDs
 */
function add_dummy_search_logs($topic_ids) {
    global $wpdb;
    
    // Common search terms
    $search_terms = [
        'appointment', 'booking', 'calendar', 'staff', 'service', 
        'payment', 'schedule', 'cancel', 'refund', 'email', 
        'notification', 'reminder', 'zoom', 'google calendar', 'integration',
        'customization', 'form', 'template', 'hours', 'availability',
        'wordpress', 'plugin', 'settings', 'configuration', 'setup',
        'installation', 'update', 'error', 'problem', 'help',
        'tutorial', 'guide', 'documentation', 'faq', 'support',
        'mobile', 'responsive', 'discount', 'coupon', 'promotion'
    ];
    
    $current_time = current_time('mysql');
    $one_day = 86400; // 1 day in seconds
    
    // Generate 400-600 search logs spread across the year
    $log_count = rand(400, 600);
    
    // Prepare batch insert data
    $place_holders = [];
    $query_data = [];
    $search_logs = [];
    
    for ($i = 0; $i < $log_count; $i++) {
        // Select a random search term
        $search_term = $search_terms[array_rand($search_terms)];
        
        // Random number of results (0-50)
        $results_count = rand(0, 50);
        
        // Random user ID (null or 1-20)
        $user_id = rand(0, 5) > 0 ? rand(1, 20) : null;
        
        // Random IP address
        $ip_address = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
        
        // Random time within the last 365 days
        $random_days_ago = rand(1, 365);
        $created_at = date('Y-m-d H:i:s', strtotime($current_time) - ($random_days_ago * $one_day));
        
        // Add to batch data
        $place_holders[] = "(%s, %d, %s, %s, %s)";
        array_push(
            $query_data,
            $search_term,
            $results_count,
            $user_id,
            $ip_address,
            $created_at
        );
        
        // Track for return value
        $search_logs[] = [
            'search_term' => $search_term,
            'results_count' => $results_count,
            'user_id' => $user_id,
            'created_at' => $created_at
        ];
    }
    
    // Execute batch insert if we have data
    if (!empty($place_holders)) {
        $query = "INSERT INTO {$wpdb->prefix}bkntc_search_logs 
                 (search_term, results_count, user_id, ip_address, created_at) 
                 VALUES " . implode(', ', $place_holders);
        
        $wpdb->query($wpdb->prepare($query, $query_data));
    }

    return $search_logs;
}

/**
 * Fix topic_feedback table structure
 * 
 * This function ensures the topic_feedback table has AUTO_INCREMENT on the id column
 */
function fix_topic_feedback_table() {
    global $wpdb;
    
    // Check if the table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}bkntc_topic_feedback'");
    if (!$table_exists) {
        return false;
    }
    
    // Check if id column has AUTO_INCREMENT
    $column_info = $wpdb->get_row("SHOW COLUMNS FROM {$wpdb->prefix}bkntc_topic_feedback WHERE Field = 'id'");
    if ($column_info && strpos($column_info->Extra, 'auto_increment') === false) {
        // Add AUTO_INCREMENT to id column
        $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_topic_feedback MODIFY id int NOT NULL AUTO_INCREMENT");
        return true;
    }
    
    return false;
}

/**
 * Add dummy feedback data for topics
 *
 * This function adds positive and negative feedback for topics
 * to simulate user engagement with help center content
 *
 * @param bool $clear_existing Whether to clear existing feedback data
 * @return array Feedback data added
 */
function add_dummy_feedback_data($clear_existing = false) {
    global $wpdb;
    
    // Fix table structure first
    fix_topic_feedback_table();
    
    // Clear existing feedback data if requested
    if ($clear_existing) {
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}bkntc_topic_feedback");
        $wpdb->query("ALTER TABLE {$wpdb->prefix}bkntc_topic_feedback AUTO_INCREMENT = 1");
    }
    
    // Get all topic IDs
    $topics = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}bkntc_help_topics", ARRAY_A);
    if (empty($topics)) {
        return [];
    }
    
    // Get the maximum ID from the feedback table to avoid duplicate primary keys
    $max_id = $wpdb->get_var("SELECT MAX(id) FROM {$wpdb->prefix}bkntc_topic_feedback");
    $start_id = (int)$max_id + 1; // Start from the next available ID
    
    $feedback_data = [];
    $place_holders = [];
    $values = [];
    $feedback_count = 0;
    
    // Generate 50 feedback entries
    for ($i = 0; $i < 50; $i++) {
        // Randomly select a topic
        $topic = $topics[array_rand($topics)];
        $topic_id = $topic['id'];
        
        // Randomly determine if feedback is positive or negative (70% positive, 30% negative)
        $feedback_type = (mt_rand(1, 10) <= 7) ? 'yes' : 'no';
        
        // Generate a random user ID between 1 and 500
        $user_id = mt_rand(1, 500);
        
        // Generate a random date within the last year
        $days_ago = mt_rand(0, 365);
        $date = date('Y-m-d H:i:s', strtotime("-{$days_ago} days"));
        
        // Calculate the current ID (starting from the next available ID)
        $current_id = $start_id + $i;
        
        // Add to batch insert - must include ID since the table doesn't have AUTO_INCREMENT
        $place_holders[] = "(%d, %d, %s, %d, %s)"; 
        array_push($values, $current_id, $topic_id, $feedback_type, $user_id, $date);
        
        $feedback_data[] = [
            'id' => $current_id,
            'topic_id' => $topic_id,
            'feedback' => $feedback_type,
            'user_id' => $user_id,
            'created_at' => $date
        ];
        
        $feedback_count++;
    }
    
    // Execute batch insert if we have data
    if (!empty($place_holders)) {
        $query = "INSERT INTO {$wpdb->prefix}bkntc_topic_feedback 
                 (id, topic_id, feedback, user_id, created_at) 
                 VALUES " . implode(', ', $place_holders);
        
        $wpdb->query($wpdb->prepare($query, $values));
    }
    
    return $feedback_data;
}

/**
 * Display the dummy data generation form
 */
function booknetic_help_center_dummy_data_form() {
    // Check if form was submitted
    if (isset($_POST['generate_dummy_data'])) {
        // Verify nonce
        if (!isset($_POST['dummy_data_nonce']) || !wp_verify_nonce($_POST['dummy_data_nonce'], 'generate_dummy_data')) {
            wp_die('Security check failed. Please try again.');
        }
        
        $result = booknetic_help_center_add_dummy_data();
        
        if ($result['status']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p>';
            echo '<p>Added ' . intval($result['categories_added']) . ' categories and ' . intval($result['topics_added']) . ' topics.</p>';
            
            if (isset($result['settings_added']) && $result['settings_added']) {
                echo '<p>Added the following settings:</p>';
                echo '<ul>';
                if (isset($result['settings_details'])) {
                    foreach ($result['settings_details'] as $setting => $count) {
                        if ($setting === 'feedback_data') {
                            echo '<li><strong>' . esc_html(str_replace('_', ' ', ucfirst($setting))) . '</strong>: ' . intval($count) . ' entries (70% positive, 30% negative)</li>';
                        } else {
                            echo '<li>' . esc_html(str_replace('_', ' ', ucfirst($setting))) . ': ' . intval($count) . '</li>';
                        }
                    }
                }
                echo '</ul>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    // Display the form
    ?>
    <div class="wrap">
        <h1>Booknetic Help Center - Generate Dummy Data</h1>
        <p>This tool will generate sample data for the Booknetic Help Center including categories, topics, views, and search logs.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('generate_dummy_data', 'dummy_data_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="clear_existing">Clear existing data</label></th>
                    <td>
                        <select name="clear_existing" id="clear_existing">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <p class="description">If set to 'Yes', all existing categories, topics, and search logs will be deleted before generating new data.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="generate_dummy_data" class="button button-primary" value="Generate Dummy Data">
            </p>
        </form>
    </div>
    <?php
}

// Register AJAX handler
add_action('wp_ajax_booknetic_contact_us_p_import_dummy_data', 'booknetic_help_center_ajax_add_dummy_data');

/**
 * AJAX handler for adding dummy data
 */
/**
 * Generate HTML for settings preview
 */
function generate_settings_preview_html() {
    global $wpdb;
    ob_start();
    ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cog text-primary"></i>
            <h5 class="card-title"><?php echo bkntc__('Settings Preview') ?></h5>
        </div>
        <div class="card-body">
            <div class="settings-preview" style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px;">
        <?php
        // Get color settings
        $color_settings = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name = 'color_settings'");
        if ($color_settings) {
            $colors = unserialize($color_settings);
            ?>
            <div class="settings-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; width: 300px;">
                <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Color Settings</h5>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($colors as $name => $value): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span><?php echo ucwords(str_replace('_', ' ', $name)); ?></span>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo $value; ?>; border-radius: 3px; border: 1px solid #ddd;"></span>
                                <code><?php echo $value; ?></code>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
        // Get social media links
        $social_links = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name = 'social_media_links'");
        if ($social_links) {
            $links = unserialize($social_links);
            ?>
            <div class="settings-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; width: 300px;">
                <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Social Media Links</h5>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($links as $platform => $url): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span><?php echo ucfirst($platform); ?></span>
                            <a href="<?php echo $url; ?>" target="_blank" style="color: #0073aa; text-decoration: none;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
        // Get menu links
        $menu_links = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name = 'menu_links'");
        if ($menu_links) {
            $links = unserialize($menu_links);
            ?>
            <div class="settings-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; width: 300px;">
                <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Menu Links</h5>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($links as $link): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span><?php echo $link['label']; ?></span>
                            <a href="<?php echo $link['url']; ?>" target="_blank" style="color: #0073aa; text-decoration: none;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
        // Get livechat settings
        $livechat_enabled = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name = 'livechat_enabled'");
        if ($livechat_enabled !== null) {
            ?>
            <div class="settings-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; width: 300px;">
                <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Live Chat Settings</h5>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Enabled</span>
                        <span style="display: inline-block; padding: 3px 8px; border-radius: 3px; background-color: <?php echo $livechat_enabled ? '#e7f7ed' : '#f8e7e7'; ?>; color: <?php echo $livechat_enabled ? '#0a7d33' : '#d63638'; ?>; font-size: 12px; font-weight: 500;">
                            <?php echo $livechat_enabled ? 'Yes' : 'No'; ?>
                        </span>
                    </div>
                    <?php 
                    $livechat_title = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}bkntc_help_settings WHERE option_name = 'livechat_title'");
                    if ($livechat_title) {
                        ?>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span>Title</span>
                            <span><?php echo $livechat_title; ?></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table text-primary"></i>
            <h5 class="card-title"><?php echo bkntc__('Sample Data') ?></h5>
        </div>
        <div class="card-body">
        
        <h5 class="mt-3">Categories (First 5)</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Order</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bkntc_help_categories ORDER BY order_number ASC LIMIT 5");
                foreach ($categories as $category) {
                    echo '<tr>';
                    echo '<td>' . $category->id . '</td>';
                    echo '<td>' . $category->name . '</td>';
                    echo '<td>' . $category->order_number . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        
        <h5 class="mt-3">Topics (First 5)</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $topics = $wpdb->get_results("
                    SELECT t.id, t.title, c.name as category_name
                    FROM {$wpdb->prefix}bkntc_help_topics t
                    JOIN {$wpdb->prefix}bkntc_help_categories c ON t.category_id = c.id
                    ORDER BY t.id ASC
                    LIMIT 5
                ");
                foreach ($topics as $topic) {
                    echo '<tr>';
                    echo '<td>' . $topic->id . '</td>';
                    echo '<td>' . $topic->title . '</td>';
                    echo '<td>' . $topic->category_name . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        
        <h5 class="mt-3">Search Logs (First 5)</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Search Term</th>
                    <th>Results</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search_logs = $wpdb->get_results("
                    SELECT id, search_term, results_count
                    FROM {$wpdb->prefix}bkntc_search_logs
                    ORDER BY id DESC
                    LIMIT 5
                ");
                foreach ($search_logs as $log) {
                    echo '<tr>';
                    echo '<td>' . $log->id . '</td>';
                    echo '<td>' . $log->search_term . '</td>';
                    echo '<td>' . $log->results_count . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler for importing dummy data
 */
function booknetic_help_center_ajax_add_dummy_data() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'You do not have permission to perform this action']);
        return;
    }
    
    // Get clear_existing parameter
    $clear_existing = isset($_POST['clear_existing']) ? sanitize_text_field($_POST['clear_existing']) : 'no';
    
    // Add dummy data
    $result = booknetic_help_center_add_dummy_data($clear_existing === 'yes');
    
    if ($result['status']) {
        // Generate HTML preview of the settings
        $html_content = generate_settings_preview_html();
        
        wp_send_json_success([
            'message' => $result['message'],
            'categories_added' => $result['categories_added'],
            'topics_added' => $result['topics_added'],
            'search_logs_added' => isset($result['search_logs_added']) ? $result['search_logs_added'] : 0,
            'settings_details' => isset($result['settings_details']) ? $result['settings_details'] : [],
            'html_content' => $html_content
        ]);
    } else {
        wp_send_json_error(['error' => $result['message']]);
    }
}

// We no longer need a separate admin page for dummy data generation as it's integrated into the settings page

// The admin page function has been removed as the dummy data functionality is now integrated into the settings page
// The rest of the admin page function has been removed


// If this file is accessed directly, show an error message
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    wp_die('This file cannot be accessed directly. Please access the dummy data functionality through the Booknetic Help Center settings page.');
}
