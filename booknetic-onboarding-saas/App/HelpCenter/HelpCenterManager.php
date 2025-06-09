<?php
namespace BookneticAddon\ContactUsP\HelpCenter;

defined('ABSPATH') or die();

use BookneticAddon\ContactUsP\HelpCenter\Extraction\AjaxHandler;

/**
 * Class HelpCenterManager
 * Manages the Help Center functionality
 */
class HelpCenterManager
{
    /**
     * Initialize the Help Center functionality
     */
    public function init()
    {
        // Initialize the extraction functionality
        $this->initExtractionFeature();
        
        // Initialize feature toggles functionality
        $this->initFeatureToggles();
    }
    
    /**
     * Initialize feature toggles functionality
     */
    private function initFeatureToggles()
    {
        // Include feature toggles AJAX handler
        require_once dirname(__FILE__) . '/../Backend/includes/feature-toggles-ajax.php';
    }
    
    /**
     * Initialize the documentation extraction feature
     */
    private function initExtractionFeature()
    {
        // Register AJAX handlers
        $ajaxHandler = new AjaxHandler();
        $ajaxHandler->registerActions();
    }
}
