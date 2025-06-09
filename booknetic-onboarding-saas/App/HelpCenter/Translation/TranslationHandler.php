<?php
namespace BookneticAddon\ContactUsP\HelpCenter\Translation;

defined('ABSPATH') or die();

// Include the data provider functions
require_once dirname(__FILE__, 4) . '/App/Backend/includes/data-provider.php';

/**
 * Class TranslationHandler
 * Handles translation of content using ChatGPT API
 */
class TranslationHandler
{
    // ChatGPT API endpoint
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    
    // API key (retrieved from settings)
    private $api_key = null;
    
    /**
     * Register AJAX actions
     */
    public function registerActions()
    {
        add_action('wp_ajax_booknetic_translate_topic_content', [$this, 'translateContent']);
        // We only need the logged-in version since this is an admin-only feature
    }
    
    /**
     * AJAX handler for translating content
     */
    public function translateContent()
    {
        // Verify nonce
        check_ajax_referer('upload_attachment_nonce', '_wpnonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['error' => 'Permission denied']);
            return;
        }
        
        // Get and sanitize parameters
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $target_language = isset($_POST['target_language']) ? sanitize_text_field($_POST['target_language']) : '';
        
        // Validate parameters
        if (empty($title) || empty($content) || empty($target_language)) {
            wp_send_json_error(['error' => 'Missing required parameters']);
            return;
        }
        
        // Get the API key from settings
        $api_key = $this->getApiKey();
        
        // Check if API key is configured
        if (empty($api_key)) {
            wp_send_json_error(['error' => 'ChatGPT API key is not configured']);
            return;
        }
        
        
        // Translate the content
        $result = $this->translateUsingChatGPT($title, $content, $target_language, $api_key);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['error' => $result->get_error_message()]);
            return;
        }
        
        // Return success with translated data
        wp_send_json([
            'status' => true,
            'title' => $result['title'],
            'content' => $result['content']
        ]);
    }
    
    /**
     * Get the API key from settings
     * 
     * @return string The API key
     */
    private function getApiKey()
    {
        // First check if the key is already set in the class
        if (!empty($this->api_key)) {
            return $this->api_key;
        }
        
        // Check if defined as a constant
        if (defined('BOOKNETIC_CHATGPT_API_KEY')) {
            $this->api_key = BOOKNETIC_CHATGPT_API_KEY;
            return $this->api_key;
        }
        
        // Get from help settings
        if (function_exists('get_help_setting')) {
            $api_key = get_help_setting('chatgpt_api_key', '');
            if (!empty($api_key)) {
                $this->api_key = $api_key;
                return $api_key;
            }
        } else {
        }
        
        // Try direct database query as a fallback
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkntc_help_settings';
        $api_key = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM $table_name WHERE option_name = %s LIMIT 1",
            'chatgpt_api_key'
        ));
        
        if (!empty($api_key)) {
            $this->api_key = $api_key;
            return $api_key;
        }
        
        return '';
    }
    
    /**
     * Get model from settings
     * 
     * @return string Model name
     */
    private function getModel()
    {
        // Get from help settings
        if (function_exists('get_help_setting')) {
            $model = get_help_setting('chatgpt_model', 'gpt-3.5-turbo');
            if (!empty($model)) {
                return $model;
            }
        }
        
        // Fallback to default
        return 'gpt-3.5-turbo';
    }
    
    /**
     * Translate content using ChatGPT API
     * 
     * @param string $title The title to translate
     * @param string $content The content to translate
     * @param string $target_language The target language code
     * @param string $api_key The ChatGPT API key
     * @return array|WP_Error The translated content or an error
     */
    private function translateUsingChatGPT($title, $content, $target_language, $api_key)
    {
        // Get the language name from code
        $language_name = $this->getLanguageName($target_language);
        
        // Prepare the prompt for ChatGPT
        $prompt = "Translate the following text from English to {$language_name}. Maintain HTML formatting and structure. Return only the translated text without any additional comments.\n\nTitle: {$title}\n\nContent: {$content}";
        
        // Prepare the request data
        $request_data = [
            'model' => $this->getModel(),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate the provided text from English to {$language_name}, maintaining all HTML formatting and structure."
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.6,
            'max_tokens' => 4000
        ];
        
        // Make the API request
        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($request_data),
            'timeout' => 60
        ]);
        
        // Check for errors
        if (is_wp_error($response)) {
            return new \WP_Error('api_error', 'Failed to connect to ChatGPT API: ' . $response->get_error_message());
        }
        
        // Check response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'HTTP ' . $response_code;
            return new \WP_Error('api_error', 'ChatGPT API error: ' . $error_message);
        }
        
        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new \WP_Error('api_error', 'Invalid response from ChatGPT API');
        }
        
        // Extract the translated content
        $translated_text = $data['choices'][0]['message']['content'];
        
        // Parse the translated text to extract title and content
        $result = $this->parseTranslatedText($translated_text);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return $result;
    }
    
    /**
     * Parse the translated text to extract title and content
     * 
     * @param string $translated_text The translated text from ChatGPT
     * @return array|WP_Error The parsed title and content or an error
     */
    private function parseTranslatedText($translated_text)
    {
        // Try to extract title and content using regex - more robust pattern
        if (preg_match('/(?:Title:)?\s*([^\n]+)(?:\n+)(?:Content:)?\s*(.*)/s', $translated_text, $matches)) {
            return [
                'title' => trim($matches[1]),
                'content' => trim($matches[2])
            ];
        }
        
        // If the first regex fails, try a more specific pattern
        if (preg_match('/Title:\s*(.*?)(?:\n\n|\r\n\r\n)Content:\s*(.*)/s', $translated_text, $matches)) {
            return [
                'title' => trim($matches[1]),
                'content' => trim($matches[2])
            ];
        }
        
        // If regex fails, try a simpler approach - split by double newline
        $parts = explode("\n\n", $translated_text, 2);
        
        if (count($parts) === 2) {
            // Check if the first part contains "Title:"
            if (strpos($parts[0], 'Title:') !== false) {
                $title = trim(str_replace('Title:', '', $parts[0]));
                $content = trim(str_replace('Content:', '', $parts[1]));
                
                return [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        // Last resort: if we can't parse properly, at least try to get something
        $lines = explode("\n", $translated_text);
        if (count($lines) > 1) {
            $title = trim($lines[0]);
            array_shift($lines);
            $content = trim(implode("\n", $lines));
            
            return [
                'title' => $title,
                'content' => $content
            ];
        }
        
        // If all parsing attempts fail, return the whole text as content and keep the original title
        return [
            'title' => '',  // Will be filled with original title in the AJAX handler
            'content' => $translated_text
        ];
    }
    
    /**
     * Get language name from language code
     * 
     * @param string $language_code The language code
     * @return string The language name
     */
    private function getLanguageName($language_code)
    {
        $languages = [
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'nl' => 'Dutch',
            'fr' => 'French',
            'de' => 'German',
            'hi' => 'Hindi',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'es' => 'Spanish',
            'tr' => 'Turkish'
        ];
        
        return isset($languages[$language_code]) ? $languages[$language_code] : 'English';
    }
}
