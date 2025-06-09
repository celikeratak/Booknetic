<?php
defined('ABSPATH') or die();

// This file defines AJAX actions for the Booknetic Help Center plugin
// It's required by the Controller.php file

use BookneticApp\Providers\Helpers\Helper;

// Note: The following AJAX actions are now registered in the ContactUsPAddon.php file:
// - booknetic_contact_us_p_get_support_link
// - booknetic_contact_us_p_save_support_link
// - booknetic_contact_us_p_save_livechat_settings
// - booknetic_contact_us_p_get_livechat_settings
// - booknetic_contact_us_p_save_social_media
// - booknetic_contact_us_p_get_social_media
// - booknetic_contact_us_p_delete_social_media
// - booknetic_contact_us_p_toggle_social_media
// - booknetic_topic_feedback

// The following AJAX actions are still registered here:
add_action('wp_ajax_booknetic_contact_us_p_save_help_topic', [\BookneticAddon\ContactUsP\Backend\Controller::class, 'save_help_topic']);

add_action('wp_ajax_booknetic_contact_us_p_delete_help_topic', [\BookneticAddon\ContactUsP\Backend\Controller::class, 'delete_help_topic']);

add_action('wp_ajax_booknetic_contact_us_p_save_help_category', [\BookneticAddon\ContactUsP\Backend\Controller::class, 'save_help_category']);

add_action('wp_ajax_booknetic_contact_us_p_delete_help_category', [\BookneticAddon\ContactUsP\Backend\Controller::class, 'delete_help_category']);

add_action('wp_ajax_booknetic_contact_us_p_reorder_categories', [\BookneticAddon\ContactUsP\Backend\Controller::class, 'reorder_categories']);
