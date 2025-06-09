<?php
namespace BookneticAddon\ContactUsP\Backend;

use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

require_once __DIR__ . '/ajax.php';

class Controller extends \BookneticApp\Providers\Core\Controller
{

    public function get_support_link()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get support link from settings
        $link = get_help_setting('support_link', null);
        
        // If empty, create a default support link
        if (empty($link)) {
            // Create default support link
            $default_link = (object)[
                'label' => bkntc__('Contact Support'),
                'url' => 'https://support.booknetic.com',
                'active' => 1,
                'id' => 1
            ];
            update_help_setting('support_link', $default_link);
            $link = $default_link;
        }
        
        Helper::response(true, ['data' => $link]);
    }

    public function save_support_link()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        $label = Helper::_post('label', '', 'string');
        $url = Helper::_post('url', '', 'string');
        $active = Helper::_post('active', 1, 'int');

        if (empty($label) || empty($url)) {
            Helper::response(false, ['error' => bkntc__('Please fill all required fields')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        $data = (object)[
            'label' => $label,
            'url' => $url,
            'active' => (int)$active,
            'id' => 1 // Always use ID 1 for the support link
        ];

        // Save to settings
        $result = update_help_setting('support_link', $data);
        
        if (!$result) {
            Helper::response(false, ['error' => bkntc__('Failed to save support link')]);
        }

        Helper::response(true, ['message' => bkntc__('Support link saved successfully!')]);
    }

    public function get_social_media()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        $id = Helper::_post('id', 0, 'int');
        if (!$id) {
            Helper::response(false, ['error' => bkntc__('Invalid ID')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get all social media links from settings
        $social_media_links = get_help_setting('social_media_links', []);
        
        // Find the link with the specified ID
        $link = null;
        foreach ($social_media_links as $social_link) {
            if (isset($social_link->id) && $social_link->id == $id) {
                $link = $social_link;
                break;
            }
        }
        
        if (!$link) {
            Helper::response(false, ['error' => bkntc__('Link not found')]);
        }
        
        Helper::response(true, ['link' => $link]);
    }

    public function save_social_media()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        $id = Helper::_post('id', 0, 'int');
        $platform = Helper::_post('platform', '', 'string');
        $icon = Helper::_post('icon', '', 'string');
        $url = Helper::_post('url', '', 'string');
        $display_order = Helper::_post('display_order', 0, 'int');
        $active = Helper::_post('active', 1, 'int');

        if (empty($platform) || empty($icon) || empty($url)) {
            Helper::response(false, ['error' => bkntc__('Please fill all required fields')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get existing social media links
        $social_media_links = get_help_setting('social_media_links', []);
        
        // Create data object for the new/updated link
        $data = (object)[
            'platform' => $platform,
            'icon' => $icon,
            'url' => $url,
            'display_order' => $display_order,
            'active' => $active
        ];

        if ($id > 0) {
            // Update existing link
            $updated = false;
            foreach ($social_media_links as $key => $link) {
                if (isset($link->id) && $link->id == $id) {
                    $data->id = $id; // Preserve the ID
                    $social_media_links[$key] = $data;
                    $updated = true;
                    break;
                }
            }
            
            if (!$updated) {
                Helper::response(false, ['error' => bkntc__('Link not found')]);
            }
        } else {
            // Add new link
            $data->id = time(); // Generate a unique ID
            $social_media_links[] = $data;
        }

        // Save updated links back to settings
        update_help_setting('social_media_links', $social_media_links);

        Helper::response(true);
    }

    public function toggle_social_media()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        $id = Helper::_post('id', 0, 'int');
        $active = Helper::_post('active', 0, 'int');

        if (!$id) {
            Helper::response(false, ['error' => bkntc__('Invalid ID')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get existing social media links
        $social_media_links = get_help_setting('social_media_links', []);
        
        // Find and update the link with the specified ID
        $updated = false;
        foreach ($social_media_links as $key => $link) {
            if (isset($link->id) && $link->id == $id) {
                $social_media_links[$key]->active = $active;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            Helper::response(false, ['error' => bkntc__('Link not found')]);
        }
        
        // Save updated links back to settings
        update_help_setting('social_media_links', $social_media_links);

        Helper::response(true);
    }

    public function delete_social_media()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        $id = Helper::_post('id', 0, 'int');

        if (!$id) {
            Helper::response(false, ['error' => bkntc__('Invalid ID')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get existing social media links
        $social_media_links = get_help_setting('social_media_links', []);
        
        // Filter out the link with the specified ID
        $filtered_links = [];
        $found = false;
        
        foreach ($social_media_links as $link) {
            if (isset($link->id) && $link->id != $id) {
                $filtered_links[] = $link;
            } else {
                $found = true;
            }
        }
        
        if (!$found) {
            Helper::response(false, ['error' => bkntc__('Link not found')]);
        }
        
        // Save updated links back to settings
        update_help_setting('social_media_links', $filtered_links);

        Helper::response(true);
    }

    public function save_livechat_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        $enabled = Helper::_post('enabled', 0, 'int');
        $provider = Helper::_post('provider', '', 'string');
        $embed_code = Helper::_post('embed_code', '', 'string');
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        $data = (object)[
            'enabled' => $enabled,
            'provider' => $provider,
            'embed_code' => $embed_code,
            'id' => 1 // Always use ID 1 for livechat settings
        ];
        
        // Save to settings
        update_help_setting('livechat_settings', $data);
        
        Helper::response(true);
    }
    
    public function get_livechat_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => 'Invalid security token']);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get livechat settings from help_settings table
        $settings = get_help_setting('livechat_settings', null);
        
        // If empty, create default settings
        if (empty($settings)) {
            $settings = (object)[
                'enabled' => 0,
                'provider' => '',
                'embed_code' => '',
                'id' => 1
            ];
            
            // Save default settings
            update_help_setting('livechat_settings', $settings);
        }
        
        Helper::response(true, ['data' => $settings]);
    }

    public function save_livechat_page_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get and sanitize form data
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $subtitle = isset($_POST['subtitle']) ? sanitize_text_field($_POST['subtitle']) : '';
        $embed_code = isset($_POST['embed_code']) ? wp_unslash($_POST['embed_code']) : '';
        $icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : 'fas fa-comments';
        
        // Validate required fields
        if (empty($title)) {
            Helper::response(false, ['error' => bkntc__('Title is required')]);
        }

        // Save settings
        update_help_setting('livechat_title', $title);
        update_help_setting('livechat_subtitle', $subtitle);
        update_help_setting('livechat_embed_code', $embed_code);
        update_help_setting('livechat_icon', $icon);
        
        Helper::response(true);
    }
    
    public function get_livechat_page_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get livechat page settings from help_settings table
        $title = get_help_setting('livechat_title', '');
        $subtitle = get_help_setting('livechat_subtitle', '');
        $embed_code = get_help_setting('livechat_embed_code', '');
        $icon = get_help_setting('livechat_icon', 'fas fa-comments');
        
        $settings = [
            'title' => $title,
            'subtitle' => $subtitle,
            'embed_code' => $embed_code,
            'icon' => $icon
        ];
        
        Helper::response(true, ['data' => $settings]);
    }

    public function save_custom_css()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get and sanitize form data
        $custom_css = isset($_POST['custom_css']) ? wp_strip_all_tags($_POST['custom_css']) : '';

        // Save settings with fallback
        if (function_exists('update_help_setting')) {
            update_help_setting('custom_css', $custom_css);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            // Check if option exists
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE option_name = %s",
                    'custom_css'
                )
            );
            
            if ($existing) {
                // Update existing option
                $wpdb->update(
                    $table_name,
                    ['option_value' => $custom_css],
                    ['option_name' => 'custom_css'],
                    ['%s'],
                    ['%s']
                );
            } else {
                // Insert new option
                $wpdb->insert(
                    $table_name,
                    [
                        'option_name' => 'custom_css',
                        'option_value' => $custom_css
                    ],
                    ['%s', '%s']
                );
            }
        }

        Helper::response(true);
    }

    public function save_menu_link()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get and sanitize form data
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $order = isset($_POST['order']) ? intval($_POST['order']) : 1;

        // Validate required fields
        if (empty($label) || empty($url)) {
            Helper::response(false, ['error' => bkntc__('Label and URL are required')]);
        }

        // Get existing menu links
        $menu_links = get_help_setting('menu_links', []);
        if (!is_array($menu_links)) {
            $menu_links = [];
        }

        // Generate a new ID if this is a new link
        if (empty($id)) {
            $id = uniqid('menu_');
            $active = true;
        } else {
            // Find the existing link to update
            $existing_link_index = -1;
            foreach ($menu_links as $index => $link) {
                if ($link->id === $id) {
                    $existing_link_index = $index;
                    break;
                }
            }
            
            if ($existing_link_index >= 0) {
                $active = $menu_links[$existing_link_index]->active;
                // Remove the old link (we'll add the updated one)
                array_splice($menu_links, $existing_link_index, 1);
            } else {
                $active = true;
            }
        }

        // Create the link object
        $link = (object) [
            'id' => $id,
            'label' => $label,
            'url' => $url,
            'order' => $order,
            'active' => $active
        ];

        // Add the new/updated link
        $menu_links[] = $link;

        // Sort by order
        usort($menu_links, function($a, $b) {
            return $a->order - $b->order;
        });

        // Save to database
        if (function_exists('update_help_setting')) {
            update_help_setting('menu_links', $menu_links);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            // Check if option exists
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE option_name = %s",
                    'menu_links'
                )
            );
            
            // Serialize the data
            $serialized_value = maybe_serialize($menu_links);
            
            if ($existing) {
                // Update existing option
                $wpdb->update(
                    $table_name,
                    ['option_value' => $serialized_value],
                    ['option_name' => 'menu_links'],
                    ['%s'],
                    ['%s']
                );
            } else {
                // Insert new option
                $wpdb->insert(
                    $table_name,
                    [
                        'option_name' => 'menu_links',
                        'option_value' => $serialized_value
                    ],
                    ['%s', '%s']
                );
            }
        }

        Helper::response(true, ['data' => $link]);
    }

    public function get_menu_links()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get menu links with fallback
        if (function_exists('get_help_setting')) {
            $menu_links = get_help_setting('menu_links', []);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            $option_value = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM $table_name WHERE option_name = %s",
                    'menu_links'
                )
            );
            
            $menu_links = $option_value ? maybe_unserialize($option_value) : [];
        }
        
        // Sort by order
        if (is_array($menu_links)) {
            usort($menu_links, function($a, $b) {
                return $a->order - $b->order;
            });
        } else {
            $menu_links = [];
        }

        Helper::response(true, ['data' => $menu_links]);
    }

    public function delete_menu_link()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get the link ID
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        if (empty($id)) {
            Helper::response(false, ['error' => bkntc__('Link ID is required')]);
        }
        
        // Get existing menu links with fallback
        if (function_exists('get_help_setting')) {
            $menu_links = get_help_setting('menu_links', []);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            $option_value = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM $table_name WHERE option_name = %s",
                    'menu_links'
                )
            );
            
            $menu_links = $option_value ? maybe_unserialize($option_value) : [];
        }
        
        if (!is_array($menu_links)) {
            $menu_links = [];
        }
        
        // Find and remove the link
        $found = false;
        foreach ($menu_links as $index => $link) {
            if ($link->id == $id) { // Using loose comparison to handle both string and integer IDs
                array_splice($menu_links, $index, 1);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            Helper::response(false, ['error' => bkntc__('Link not found')]);
        }
        
        // Save the updated links with fallback
        if (function_exists('update_help_setting')) {
            update_help_setting('menu_links', $menu_links);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            // Serialize the data
            $serialized_value = maybe_serialize($menu_links);
            
            $wpdb->update(
                $table_name,
                ['option_value' => $serialized_value],
                ['option_name' => 'menu_links'],
                ['%s'],
                ['%s']
            );
        }
        
        Helper::response(true);
    }

    public function toggle_menu_link()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }

        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get parameters
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $active = isset($_POST['active']) ? (bool)intval($_POST['active']) : false;
        
        if (empty($id)) {
            Helper::response(false, ['error' => bkntc__('Link ID is required')]);
        }
        
        // Get existing menu links with fallback
        if (function_exists('get_help_setting')) {
            $menu_links = get_help_setting('menu_links', []);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            $option_value = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM $table_name WHERE option_name = %s",
                    'menu_links'
                )
            );
            
            $menu_links = $option_value ? maybe_unserialize($option_value) : [];
        }
        
        if (!is_array($menu_links)) {
            $menu_links = [];
        }
        
        // Find and update the link
        $found = false;
        foreach ($menu_links as $index => $link) {
            if ($link->id === $id) {
                $menu_links[$index]->active = $active;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            Helper::response(false, ['error' => bkntc__('Link not found')]);
        }
        
        // Save the updated links with fallback
        if (function_exists('update_help_setting')) {
            update_help_setting('menu_links', $menu_links);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            // Serialize the data
            $serialized_value = maybe_serialize($menu_links);
            
            $wpdb->update(
                $table_name,
                ['option_value' => $serialized_value],
                ['option_name' => 'menu_links'],
                ['%s'],
                ['%s']
            );
        }
        
        Helper::response(true);
    }

    public function save_copyright_text()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';

        
        // Get and sanitize form data
        $copyright_text = isset($_POST['copyright_text']) ? sanitize_text_field($_POST['copyright_text']) : '';
        
        // Validate required fields
        if (empty($copyright_text)) {
            Helper::response(false, ['error' => bkntc__('Copyright text is required')]);
        }
        
        // Save settings with fallback
        if (function_exists('update_help_setting')) {
            update_help_setting('copyright_text', $copyright_text);
        } else {
            // Fallback if function doesn't exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'bkntc_help_settings';
            
            // Check if option exists
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE option_name = %s",
                    'copyright_text'
                )
            );
            
            if ($existing) {
                // Update existing option
                $wpdb->update(
                    $table_name,
                    ['option_value' => $copyright_text],
                    ['option_name' => 'copyright_text'],
                    ['%s'],
                    ['%s']
                );
            } else {
                // Insert new option
                $wpdb->insert(
                    $table_name,
                    [
                        'option_name' => 'copyright_text',
                        'option_value' => $copyright_text
                    ],
                    ['%s', '%s']
                );
            }
        }
        
        Helper::response(true);
    }

    public function index()
    {
        $this->view( 'index' );
    }

    public function save_category()
    {
        if (!isset($_POST['manage_category_nonce']) || !wp_verify_nonce($_POST['manage_category_nonce'], 'manage_category')) {
            wp_die(__('Nonce verification failed', 'booknetic'));
        }

        global $wpdb;

        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : 'fa-book';

        if ($category_id > 0) {
            // Update existing category
            $wpdb->update(
                "{$wpdb->prefix}bkntc_help_categories",
                [
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon
                ],
                ['id' => $category_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new category
            $wpdb->insert(
                "{$wpdb->prefix}bkntc_help_categories",
                [
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon
                ],
                ['%s', '%s', '%s']
            );
        }

        // Redirect after saving
        wp_redirect(admin_url('admin.php?page=booknetic-saas&module=help-center&view=categories'));
        exit;
    }

    /**
     * Import dummy data for the help center
     */
    public function import_dummy_data()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            Helper::response(false, ['error' => bkntc__('You do not have permission to perform this action.')]);
        }
        
        // Get clear existing option
        $clear_existing = isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'yes';
        
        // Include dummy data functions
        require_once dirname(__DIR__, 2) . '/dummy-data.php';
        
        // Import dummy data
        $result = booknetic_help_center_add_dummy_data($clear_existing);
        
        if ($result['status']) {
            Helper::response(true, ['message' => $result['message']]);
        } else {
            Helper::response(false, ['error' => $result['message']]);
        }
    }
    
    /**
     * Create database tables for the help center
     */
    public function create_tables()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            Helper::response(false, ['error' => bkntc__('You do not have permission to perform this action.')]);
        }
        
        // Get force recreate option
        $force_recreate = isset($_POST['force_recreate']) && $_POST['force_recreate'] === 'yes';
        
        // If force recreate is enabled, drop existing tables first
        if ($force_recreate) {
            global $wpdb;
            $tables = [
                $wpdb->prefix . 'bkntc_topic_feedback',
                $wpdb->prefix . 'bkntc_help_topics',
                $wpdb->prefix . 'bkntc_help_categories',
                $wpdb->prefix . 'bkntc_search_logs',
                $wpdb->prefix . 'bkntc_help_settings'
            ];
            
            foreach ($tables as $table) {
                $wpdb->query("DROP TABLE IF EXISTS $table");
            }
        }
        
        // Include table creation function
        require_once dirname(__DIR__, 2) . '/install-tables.php';
        
        // Create tables
        $result = booknetic_help_center_create_tables();
        
        if ($result['status']) {
            Helper::response(true, ['message' => $result['message']]);
        } else {
            Helper::response(false, ['error' => $result['message']]);
        }
    }

    /**
     * Get color settings
     */
    public function get_color_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Get color settings from the database
        $color_settings = get_help_setting('color_settings', [
            'primary_color' => '#4050B5',
            'secondary_color' => '#6C757D'
        ]);
        
        Helper::response(true, ['data' => $color_settings]);
    }
    
    /**
     * Save color settings
     */
    public function save_color_settings()
    {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'help_center_nonce')) {
            Helper::response(false, ['error' => bkntc__('Invalid security token')]);
        }
        
        // Get and sanitize parameters
        $color_preset = isset($_POST['color_preset']) ? sanitize_text_field($_POST['color_preset']) : '';
        $primary_color = isset($_POST['primary_color']) ? sanitize_text_field($_POST['primary_color']) : '';
        $secondary_color = isset($_POST['secondary_color']) ? sanitize_text_field($_POST['secondary_color']) : '';
        
        // Color presets
        $color_presets = [
            'preset1' => ['primary_color' => '#4050B5', 'secondary_color' => '#6C757D'],
            'preset2' => ['primary_color' => '#28a745', 'secondary_color' => '#dc3545'],
            'preset3' => ['primary_color' => '#007bff', 'secondary_color' => '#17a2b8'],
        ];

        // If a color preset is selected, use its values
        if (!empty($color_preset) && isset($color_presets[$color_preset])) {
            $primary_color = $color_presets[$color_preset]['primary_color'];
            $secondary_color = $color_presets[$color_preset]['secondary_color'];
        }
        
        // Validate required parameters
        if (empty($primary_color) || empty($secondary_color)) {
            Helper::response(false, ['error' => bkntc__('Please provide both primary and secondary colors')]);
            return;
        }
        
        // Validate color format (simple hex validation)
        $color_regex = '/#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/';
        if (!preg_match($color_regex, $primary_color) || !preg_match($color_regex, $secondary_color)) {
            Helper::response(false, ['error' => bkntc__('Please enter valid hex color codes (e.g., #4050B5)')]);
            return;
        }
        
        // Include data provider functions
        require_once __DIR__ . '/includes/data-provider.php';
        
        // Create data object for the color settings
        $color_settings = [
            'primary_color' => $primary_color,
            'secondary_color' => $secondary_color
        ];
        
        // Save to settings
        $result = update_help_setting('color_settings', $color_settings);
        
        if (!$result) {
            Helper::response(false, ['error' => bkntc__('Failed to save color settings')]);
        }
        
        Helper::response(true, ['message' => bkntc__('Color settings saved successfully!')]);
    }

    
}
