<?php

namespace BookneticAddon\ContactUsP;

use BookneticAddon\ContactUsP\Backend\Controller;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticSaaS\Providers\Core\Route as SaaSRoute;
use BookneticSaaS\Providers\UI\MenuUI as SaaSMenuUI;
use BookneticAddon\ContactUsP\HelpCenter\HelpCenterManager;




function bkntc__($text, $params = [], $esc = true)
{
    return \bkntc__($text, $params, $esc, ContactUsPAddon::getAddonTextDomain());
}

class ContactUsPAddon extends AddonLoader
{

    public function __construct()
    {
        parent::__construct();
        
        // Register activation hook
        $plugin_file = dirname(__DIR__, 2) . '/init.php';
        register_activation_hook($plugin_file, [\BookneticAddon\ContactUsP\App\Backend\Activation::class, 'install']);
    }

    public function init()
    {
        add_action('wp_ajax_booknetic_help_search', [$this, 'ajaxHelpSearch']);
        add_action('wp_ajax_nopriv_booknetic_help_search', [$this, 'ajaxHelpSearch']);
        
        // Register our custom AJAX handler for help center search
        add_action('wp_ajax_help_center_search', [$this, 'ajaxHelpCenterSearch']);
        add_action('wp_ajax_nopriv_help_center_search', [$this, 'ajaxHelpCenterSearch']);
        
        // Register topic feedback AJAX handler
        add_action('wp_ajax_booknetic_topic_feedback', [$this, 'ajaxTopicFeedback']);
        add_action('wp_ajax_nopriv_booknetic_topic_feedback', [$this, 'ajaxTopicFeedback']);
        
        // Register social media AJAX handlers
        add_action('wp_ajax_booknetic_contact_us_p_save_social_media', [$this, 'ajaxSaveSocialMedia']);
        add_action('wp_ajax_booknetic_contact_us_p_get_social_media', [$this, 'ajaxGetSocialMedia']);
        add_action('wp_ajax_booknetic_contact_us_p_delete_social_media', [$this, 'ajaxDeleteSocialMedia']);
        add_action('wp_ajax_booknetic_contact_us_p_toggle_social_media', [$this, 'ajaxToggleSocialMedia']);
        
        // Register database tables creation AJAX handler
        add_action('wp_ajax_booknetic_contact_us_p_create_tables', [$this, 'ajaxCreateTables']);
        
        // Register settings AJAX handlers
        add_action('wp_ajax_booknetic_contact_us_p_get_support_link', [$this, 'ajaxGetSupportLink']);
        add_action('wp_ajax_booknetic_contact_us_p_save_support_link', [$this, 'ajaxSaveSupportLink']);
        add_action('wp_ajax_booknetic_contact_us_p_save_livechat_settings', [$this, 'ajaxSaveLivechatSettings']);
        add_action('wp_ajax_booknetic_contact_us_p_get_livechat_settings', [$this, 'ajaxGetLivechatSettings']);
        
        // Livechat page settings
        add_action('wp_ajax_booknetic_contact_us_p_save_livechat_page_settings', [$this, 'ajaxSaveLivechatPageSettings']);
        add_action('wp_ajax_booknetic_contact_us_p_get_livechat_page_settings', [$this, 'ajaxGetLivechatPageSettings']);
        
        // Copyright text settings
        add_action('wp_ajax_booknetic_contact_us_p_save_copyright_text', [$this, 'ajaxSaveCopyrightText']);
        
        // Custom CSS settings
        add_action('wp_ajax_booknetic_contact_us_p_save_custom_css', [$this, 'ajaxSaveCustomCss']);
        
        // ChatGPT settings AJAX action
        add_action('wp_ajax_booknetic_save_chatgpt_settings', [$this, 'ajaxSaveChatGPTSettings']);
        
        // Menu links AJAX actions
        add_action('wp_ajax_booknetic_contact_us_p_save_menu_link', [$this, 'ajaxSaveMenuLink']);
        add_action('wp_ajax_booknetic_contact_us_p_get_menu_links', [$this, 'ajaxGetMenuLinks']);
        add_action('wp_ajax_booknetic_contact_us_p_delete_menu_link', [$this, 'ajaxDeleteMenuLink']);
        add_action('wp_ajax_booknetic_contact_us_p_toggle_menu_link', [$this, 'ajaxToggleMenuLink']);
        
        // Color settings AJAX actions
        add_action('wp_ajax_booknetic_contact_us_p_save_color_settings', [$this, 'ajaxSaveColorSettings']);
        add_action('wp_ajax_booknetic_contact_us_p_get_color_settings', [$this, 'ajaxGetColorSettings']);
        
        // Dummy data import AJAX action
        add_action('wp_ajax_booknetic_contact_us_p_import_dummy_data', [$this, 'ajaxImportDummyData']);
        
        // Initialize Help Center Manager
        $helpCenterManager = new HelpCenterManager();
        $helpCenterManager->init();

        Capabilities::registerTenantCapability('help-center', bkntc__('Help Center'));

        if (!Capabilities::tenantCan('help-center'))
            return;

        Capabilities::register('help-center', bkntc__('Help Center'));
    }

    public function initBackend()
    {
        if (!Capabilities::tenantCan('help-center'))
            return;

        if (!Capabilities::userCan('help-center')) {
            return;
        }

        Route::get('help-center', Controller::class);

        MenuUI::get('help-center')
            ->setTitle(bkntc__('Help Center'))
            ->setIcon('fa fa-question-circle')
            ->setPriority(20);
    }


    public function initSaaSBackend()
    {
    

        SaaSRoute::get('help-center', Controller::class);

        SaaSMenuUI::get('help-center')
            ->setTitle(bkntc__('Help Center'))
            ->setIcon('fa fa-question-circle')
            ->setPriority(20);
    }
    
    /**
     * AJAX handler for help center search
     */
    public function ajaxHelpCenterSearch()
    {
        require_once dirname(__FILE__) . '/Backend/includes/ajax-handlers.php';
        handle_help_center_search();
    }
    
    /**
     * AJAX handler for topic feedback
     */
    public function ajaxTopicFeedback()
    {
        // Include feedback handler functions
        require_once __DIR__ . '/Backend/includes/feedback-handler.php';
        
        // Call the feedback handler function
        ajax_process_topic_feedback();
    }
    
    /**
     * AJAX handler for saving social media
     */
    public function ajaxSaveSocialMedia()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_social_media();
    }
    
    /**
     * AJAX handler for getting social media
     */
    public function ajaxGetSocialMedia()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_social_media();
    }
    
    /**
     * AJAX handler for deleting social media
     */
    public function ajaxDeleteSocialMedia()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->delete_social_media();
    }
    
    /**
     * AJAX handler for toggling social media
     */
    public function ajaxToggleSocialMedia()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->toggle_social_media();
    }
    
    /**
     * AJAX handler for getting support link
     */
    public function ajaxGetSupportLink()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_support_link();
    }
    
    /**
     * AJAX handler for saving support link
     */
    public function ajaxSaveSupportLink()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_support_link();
    }
    
    /**
     * AJAX handler for saving livechat settings
     */
    public function ajaxSaveLivechatSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_livechat_settings();
    }
    
    /**
     * AJAX handler for getting livechat settings
     */
    public function ajaxGetLivechatSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_livechat_settings();
    }
    
    /**
     * AJAX handler for saving livechat page settings
     */
    public function ajaxSaveLivechatPageSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_livechat_page_settings();
    }
    
    /**
     * AJAX handler for getting livechat page settings
     */
    public function ajaxGetLivechatPageSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_livechat_page_settings();
    }
    
    /**
     * AJAX handler for saving copyright text
     */
    public function ajaxSaveCopyrightText()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_copyright_text();
    }
    
    /**
     * AJAX handler for saving custom CSS
     */
    public function ajaxSaveCustomCss()
    {
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_custom_css();
    }
    
    /**
     * AJAX handler for saving ChatGPT settings
     */
    public function ajaxSaveChatGPTSettings()
    {
        // Include the AJAX handlers file
        require_once dirname(__FILE__) . '/Backend/includes/ajax-handlers.php';
        
        // Call the function directly
        booknetic_save_chatgpt_settings();
    }

    /**
     * AJAX handler for saving menu link
     */
    public function ajaxSaveMenuLink()
    {
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_menu_link();
    }

    /**
     * AJAX handler for getting menu links
     */
    public function ajaxGetMenuLinks()
    {
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_menu_links();
    }

    /**
     * AJAX handler for deleting menu link
     */
    public function ajaxDeleteMenuLink()
    {
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->delete_menu_link();
    }

    /**
     * AJAX handler for toggling menu link status
     */
    public function ajaxToggleMenuLink()
    {
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->toggle_menu_link();
    }
    
    /**
     * AJAX handler for saving color settings
     */
    public function ajaxSaveColorSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->save_color_settings();
    }
    
    /**
     * AJAX handler for getting color settings
     */
    public function ajaxGetColorSettings()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->get_color_settings();
    }
    
    /**
     * AJAX handler for importing dummy data
     */
    public function ajaxImportDummyData()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->import_dummy_data();
    }
    

    
    /**
     * AJAX handler for creating database tables
     */
    public function ajaxCreateTables()
    {
        // Create an instance of the Controller class and call the method
        $controller = new \BookneticAddon\ContactUsP\Backend\Controller();
        $controller->create_tables();
    }
}
