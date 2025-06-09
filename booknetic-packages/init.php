<?php
/*
 * Plugin Name: Packages for Booknetic
 * Description: Packages addon for Booknetic.
 * Version: 1.4.0
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-packages
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\Packages\PackagesAddon::getAddonSlug() ] = new \BookneticAddon\Packages\PackagesAddon();
    return $addons;
});
