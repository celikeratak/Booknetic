<?php
namespace BookneticAddon\ContactUsP\HelpCenter\Extraction;

defined('ABSPATH') or die();

/**
 * Class DocumentExtractor
 * Handles the extraction of content from Booknetic documentation pages
 */
class DocumentExtractor
{
    /**
     * Extract content from a Booknetic documentation URL
     * 
     * @param string $url The URL to extract content from
     * @return array|WP_Error The extracted content or an error
     */
    public function extractContent($url)
    {
        if (empty($url)) {
            return new \WP_Error('invalid_url', 'URL is required');
        }
        
        // Validate that this is a Booknetic documentation or feature URL
        if (!preg_match('/^https?:\/\/(?:www\.)?booknetic\.com\/(documentation|feature)/i', $url)) {
            return new \WP_Error('invalid_url', 'URL must be from Booknetic documentation or feature pages');
        }
        
        // Fetch the content from the URL
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ]);
        
        // Check for errors
        if (is_wp_error($response)) {
            return new \WP_Error('fetch_error', 'Failed to fetch content: ' . $response->get_error_message());
        }
        
        // Check response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new \WP_Error('fetch_error', 'Failed to fetch content: HTTP ' . $response_code);
        }
        
        // Get the body content
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            return new \WP_Error('empty_content', 'No content found');
        }
        
        // Load HTML into DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($html); // @ to suppress warnings for malformed HTML
        $xpath = new \DOMXPath($dom);
        
        // Extract title - typically the main heading
        $title = $this->extractTitle($xpath);
        
        // Extract main content
        $content = $this->extractMainContent($xpath);
        
        if (empty($content)) {
            return new \WP_Error('no_content', 'No content could be extracted from the page');
        }
        
        // Add source attribution
        $content .= '<p class="documentation-source">Source: <a href="' . esc_url($url) . '" target="_blank">' . esc_url($url) . '</a></p>';
        
        return [
            'title' => $title,
            'content' => $content,
            'url' => $url
        ];
    }
    
    /**
     * Extract the title from the document
     * 
     * @param DOMXPath $xpath The XPath object for the document
     * @return string The extracted title
     */
    private function extractTitle($xpath)
    {
        $title = '';
        
        // First, try to find a feature-specific title
        $feature_title = $xpath->query('//h1[contains(@class, "feature-title")]');
        if ($feature_title->length > 0) {
            $title = trim($feature_title->item(0)->textContent);
        }
        
        // If no feature title, try documentation title
        if (empty($title)) {
            $doc_title = $xpath->query('//h1[@class="documentation-title"]');
            if ($doc_title->length > 0) {
                $title = trim($doc_title->item(0)->textContent);
            }
        }
        
        // If still no title, try any h1
        if (empty($title)) {
            $title_elements = $xpath->query('//h1');
            if ($title_elements->length > 0) {
                $title = trim($title_elements->item(0)->textContent);
            }
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
        
        return $title;
    }
    
    /**
     * Extract the main content from the document
     * 
     * @param DOMXPath $xpath The XPath object for the document
     * @return string The extracted content HTML
     */
    private function extractMainContent($xpath)
    {
        $content = '';
        
        // Check for feature page content first
        $feature_content = $xpath->query('//div[@class="feature-inner-text-content content with-image-zoom"]');
        if ($feature_content->length > 0) {
            // Found feature page content
            $content_elements = $feature_content;
        } else {
            // Try documentation page paths
            // Try the specific path first: container > documentation-single-content content-box-lg > row > col-lg-9 > documentation-single-content
            $content_elements = $xpath->query('//div[contains(@class, "container")]//div[contains(@class, "documentation-single-content") and contains(@class, "content-box-lg")]//div[contains(@class, "row")]//div[contains(@class, "col-lg-9")]//div[contains(@class, "documentation-single-content")]');
            
            // If not found, try just the documentation-single-content div
            if ($content_elements->length === 0) {
                $content_elements = $xpath->query('//div[contains(@class, "documentation-single-content")]');
            }
            
            // If still not found, try some fallbacks
            if ($content_elements->length === 0) {
                $content_elements = $xpath->query('//article | //main | //div[contains(@class, "content")]');
            }
        }
        
        if ($content_elements->length > 0) {
            // Get the first matching element
            $content_element = $content_elements->item(0);
            
            // Fix lazy-loaded media elements
            $this->fixLazyLoadedMedia($content_element, $xpath);
            
            // Get the actual HTML content from the documentation-single-content div
            // This preserves the formatting and structure of the original content
            foreach ($content_element->childNodes as $node) {
                $temp = new \DOMDocument();
                $temp->appendChild($temp->importNode($node, true));
                $content .= $temp->saveHTML();
            }
        }
        
        return $content;
    }
    
    /**
     * Helper method to fix lazy-loaded images and videos in the DOM
     * 
     * @param DOMNode $element The DOM element to process
     * @param DOMXPath $xpath The XPath object for querying
     */
    private function fixLazyLoadedMedia($element, $xpath)
    {
        // Fix lazy-loaded images
        $lazy_images = $xpath->query('.//img', $element);
        foreach ($lazy_images as $img) {
            // Check for data-src attribute (common lazy-loading pattern)
            if ($img->hasAttribute('data-src')) {
                $real_src = $img->getAttribute('data-src');
                $img->setAttribute('src', $real_src);
            }
            
            // Check for data-lazy-src attribute (used by some WordPress lazy-loading plugins)
            if ($img->hasAttribute('data-lazy-src')) {
                $real_src = $img->getAttribute('data-lazy-src');
                $img->setAttribute('src', $real_src);
            }
            
            // Handle srcset for responsive images
            if ($img->hasAttribute('data-srcset')) {
                $real_srcset = $img->getAttribute('data-srcset');
                $img->setAttribute('srcset', $real_srcset);
            }
            
            // Remove width and height attributes to ensure images display at full size
            if ($img->hasAttribute('width')) {
                $img->removeAttribute('width');
            }
            if ($img->hasAttribute('height')) {
                $img->removeAttribute('height');
            }
            
            // Set style to width: 100% for all images
            $img->setAttribute('style', 'width: 100%;');
            
            // Remove loading="lazy" attribute to ensure images load in the extracted content
            if ($img->hasAttribute('loading') && $img->getAttribute('loading') === 'lazy') {
                $img->removeAttribute('loading');
            }
            
            // Remove any classes that might interfere with proper display
            if ($img->hasAttribute('class')) {
                $classes = $img->getAttribute('class');
                $classes = preg_replace('/(\s|^)lazy(\s|$)/', ' ', $classes);
                $img->setAttribute('class', trim($classes));
            }
        }
        
        // Fix lazy-loaded iframes (videos)
        $iframes = $xpath->query('.//iframe', $element);
        foreach ($iframes as $iframe) {
            // Check for data-src attribute
            if ($iframe->hasAttribute('data-src')) {
                $real_src = $iframe->getAttribute('data-src');
                $iframe->setAttribute('src', $real_src);
            }
            
            // Remove loading="lazy" attribute
            if ($iframe->hasAttribute('loading') && $iframe->getAttribute('loading') === 'lazy') {
                $iframe->removeAttribute('loading');
            }
            
            // Set width and height to ensure proper display
            $iframe->setAttribute('width', '100%');
            $iframe->setAttribute('height', '400');
            
            // Add style for responsive behavior
            $iframe->setAttribute('style', 'width: 100%; max-width: 100%; display: block; margin: 20px 0;');
            
            // Add frameborder and allow attributes for better compatibility
            $iframe->setAttribute('frameborder', '0');
            $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
            
            // Add special class to identify video embeds
            $iframe->setAttribute('class', 'booknetic-video-embed');
        }
        
        // Fix video elements
        $videos = $xpath->query('.//video', $element);
        foreach ($videos as $video) {
            // Check for data-src attribute
            if ($video->hasAttribute('data-src')) {
                $real_src = $video->getAttribute('data-src');
                $video->setAttribute('src', $real_src);
            }
            
            // Check source elements within video
            $sources = $xpath->query('.//source', $video);
            foreach ($sources as $source) {
                if ($source->hasAttribute('data-src')) {
                    $real_src = $source->getAttribute('data-src');
                    $source->setAttribute('src', $real_src);
                }
            }
        }
        
        // Fix picture elements
        $pictures = $xpath->query('.//picture', $element);
        foreach ($pictures as $picture) {
            $sources = $xpath->query('.//source', $picture);
            foreach ($sources as $source) {
                if ($source->hasAttribute('data-srcset')) {
                    $real_srcset = $source->getAttribute('data-srcset');
                    $source->setAttribute('srcset', $real_srcset);
                }
            }
        }
    }
}
