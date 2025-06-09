<?php
namespace BookneticAddon\ContactUsP\HelpCenter\Extraction;

defined('ABSPATH') or die();

/**
 * Class AjaxHandler
 * Handles AJAX requests for documentation extraction
 */
class AjaxHandler
{
    /**
     * Register AJAX actions
     */
    public function registerActions()
    {
        add_action('wp_ajax_booknetic_extract_documentation', [$this, 'extractDocumentation']);
        // We only need the logged-in version since this is an admin-only feature
    }
    
    /**
     * AJAX handler for extracting documentation content
     */
    public function extractDocumentation()
    {
        // Verify nonce
        check_ajax_referer('upload_attachment_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['error' => 'Permission denied']);
            return;
        }
        
        // Get and validate URL
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        // Use the DocumentExtractor class to extract content
        $extractor = new DocumentExtractor();
        $result = $extractor->extractContent($url);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['error' => $result->get_error_message()]);
            return;
        }
        
        // Return success with extracted data
        // Format response according to Booknetic AJAX pattern
        wp_send_json([
            'status' => true,
            'title' => $result['title'],
            'content' => $result['content']
        ]);
    }
}
