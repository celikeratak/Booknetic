<?php
/*
 * Plugin Name: Advanced Reports for Booknetic
 * Description: Get advanced reports with advanced filter options.
 * Version: 1.0.0
 * Author: Axtra Solutions
 * Author URI: https://www.axtrasolutions.com
 * License: Commercial
 * Text Domain: booknetic-advancedreports
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\AdvancedReports\AdvancedReportsAddon::getAddonSlug() ] = new \BookneticAddon\AdvancedReports\AdvancedReportsAddon();
    return $addons;
});
