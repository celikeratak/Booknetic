<?php
/**
 * Asset Manager for Booknetic Onboarding SaaS Addon
 * Centralizes all script and style loading to improve performance
 */

namespace BookneticAddon\ContactUsP\Backend\Includes;

defined('ABSPATH') or exit;

class AssetManager {
    /**
     * Initialize the asset manager
     */
    public static function init() {
        // Register admin scripts and styles
        add_action('admin_enqueue_scripts', [self::class, 'registerAdminAssets']);
    }

    /**
     * Register and enqueue admin scripts and styles
     */
    public static function registerAdminAssets() {
        // Only load on our plugin pages
        if (!self::isPluginPage()) {
            return;
        }

        // Register common libraries (only once)
        self::registerCommonLibraries();
        
        // Register and enqueue admin styles
        wp_register_style(
            'booknetic-help-center-admin',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/css/style-admin.css',
            ['booknetic-fontawesome'],
            filemtime(plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/css/style-admin.css')
        );
        
        wp_enqueue_style('booknetic-help-center-admin');

        // Register and enqueue admin scripts
        wp_register_script(
            'booknetic-help-center-admin',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/js/help-center.js',
            ['jquery'],
            filemtime(plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/js/help-center.js'),
            true
        );

        // Include the localization file
        require_once dirname(__FILE__) . '/localization.php';
        
        // Get help-center specific localization data
        $localization_data = get_booknetic_help_localization('help-center');
        
        // Localize script with nonce, ajax url and translations
        wp_localize_script('booknetic-help-center-admin', 'helpCenterAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('help_center_nonce')
        ]);
        
        // Add translations to booknetic object
        booknetic_help_output_localization('help-center');
        
        wp_enqueue_script('booknetic-help-center-admin');
        
        // Conditionally load page-specific assets
        self::loadPageSpecificAssets();
    }

    /**
     * Check if current page is a plugin page
     */
    private static function isPluginPage() {
        if (!is_admin()) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $module = isset($_GET['module']) ? sanitize_text_field($_GET['module']) : '';
        
        return ($page === 'booknetic' || $page === 'booknetic-saas') && $module === 'help-center';
    }

    /**
     * Register and enqueue page-specific scripts
     */
    public static function enqueuePageScript($scriptName, $dependencies = ['jquery']) {
        if (!self::isPluginPage()) {
            return;
        }

        $scriptPath = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/js/' . $scriptName . '.js';
        $scriptUrl = plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/js/' . $scriptName . '.js';
        
        if (!file_exists($scriptPath)) {
            return;
        }

        wp_enqueue_script(
            'booknetic-' . $scriptName,
            $scriptUrl,
            $dependencies,
            filemtime($scriptPath),
            true
        );

        return 'booknetic-' . $scriptName;
    }

    /**
     * Register and enqueue page-specific styles
     */
    public static function enqueuePageStyle($styleName, $dependencies = ['booknetic-help-center-admin']) {
        if (!self::isPluginPage()) {
            return;
        }

        $stylePath = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/css/' . $styleName . '.css';
        $styleUrl = plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/backend/css/' . $styleName . '.css';
        
        if (!file_exists($stylePath)) {
            return;
        }

        wp_enqueue_style(
            'booknetic-' . $styleName,
            $styleUrl,
            $dependencies,
            filemtime($stylePath)
        );

        return 'booknetic-' . $styleName;
    }

    /**
     * Localize a script with data
     */
    public static function localizeScript($handle, $objectName, $data) {
        wp_localize_script($handle, $objectName, $data);
    }
    
    /**
     * Register common libraries used across the plugin
     */
    private static function registerCommonLibraries() {
        // Register FontAwesome (only once)
        wp_register_style(
            'booknetic-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
            [],
            '6.0.0'
        );
        wp_enqueue_style('booknetic-fontawesome');
        
        // Register Material Design Icons if needed
        wp_register_style(
            'booknetic-mdi',
            'https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css',
            [],
            '6.5.95'
        );
        
        // Use WordPress bundled jQuery
        wp_enqueue_script('jquery');
        
        // Register Chart.js if needed
        wp_register_script(
            'booknetic-chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            ['jquery'],
            '3.9.1',
            true
        );
        
        // Register Select2 if needed
        wp_register_style(
            'booknetic-select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            [],
            '4.1.0-rc.0'
        );
        
        wp_register_script(
            'booknetic-select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            ['jquery'],
            '4.1.0-rc.0',
            true
        );
        
        // Register Summernote if needed
        wp_register_style(
            'booknetic-summernote',
            'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css',
            [],
            '0.8.18'
        );
        
        wp_register_script(
            'booknetic-summernote',
            'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js',
            ['jquery'],
            '0.8.18',
            true
        );
    }
    
    /**
     * Load page-specific assets based on the current view
     */
    private static function loadPageSpecificAssets() {
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'dashboard';
        
        // Common assets for all views
        wp_enqueue_style('booknetic-mdi');
        
        // Load assets based on the current view
        switch ($view) {
            case 'topics-add':
            case 'topics-edit':
                // Load Summernote and Select2 for topic editing
                wp_enqueue_style('booknetic-select2');
                wp_enqueue_script('booknetic-select2');
                wp_enqueue_style('booknetic-summernote');
                wp_enqueue_script('booknetic-summernote');
                self::enqueuePageScript('dummy-data');
                
                // Add topics-specific translations
                booknetic_help_output_localization('topics');
                break;
                
            case 'dashboard':
            case '':
            case 'index':
                // Load Chart.js for dashboard
                wp_enqueue_script('booknetic-chartjs');
                // Load search animation script for main help center page
                self::enqueuePageScript('search-animation');
                
                // Add dashboard-specific translations
                booknetic_help_output_localization('dashboard');
                break;
                
            case 'manage-topics':
            case 'manage-categories':
            case 'reorder-categories':
                // Load specific scripts for these views
                self::enqueuePageStyle(str_replace('-', '_', $view));
                self::enqueuePageScript(str_replace('-', '_', $view));
                
                // Add context-specific translations
                booknetic_help_output_localization($view);
                break;
                
            case 'recommended-addons':
                // Load Chart.js for recommended addons
                wp_enqueue_script('booknetic-chartjs');
                self::enqueuePageScript('manage-addons');
                break;
        }
    }
}
