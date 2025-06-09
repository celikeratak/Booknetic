<?php
namespace BookneticAddon\ContactUsP\HelpCenter\Translation;

defined('ABSPATH') or die();

/**
 * Initialize the translation functionality
 */
function init_translation()
{
    // Create and register the translation handler
    $translation_handler = new TranslationHandler();
    $translation_handler->registerActions();
}

// Initialize the translation functionality
add_action('init', __NAMESPACE__ . '\\init_translation');
