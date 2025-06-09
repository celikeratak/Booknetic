<?php
/*
 * Plugin Name: Help Center for Booknetic SaaS
 * Description: Enhance your tenant experience with the Help Center Add-on.
 * Version: 3.0.0
 * Author: Tahir
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-onboarding-saas
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\ContactUsP\ContactUsPAddon::getAddonSlug() ] = new \BookneticAddon\ContactUsP\ContactUsPAddon();
    return $addons;
});

// Include AJAX actions
require_once __DIR__ . '/App/Backend/ajax/ajax-actions.php';

// Include Translation functionality
require_once __DIR__ . '/App/HelpCenter/Translation/loader.php';

// Initialize Asset Manager
require_once __DIR__ . '/App/Backend/includes/asset-manager.php';
\BookneticAddon\ContactUsP\Backend\Includes\AssetManager::init();

// Initialize Migration feature
require_once __DIR__ . '/App/Backend/Migration.php';
require_once __DIR__ . '/App/Backend/MigrationAjax.php';
BookneticApp\Backend\MigrationAjax::init();
