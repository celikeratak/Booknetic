<?php
/*
 * Plugin Name: Ready-Made Tenant Templates for Booknetic SaaS
 * Description: Simplify the setup process for tenants with ready-made templates
 * Version: 1.0.9
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-templates
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ( $addons )
{
    if ( ! class_exists( '\BookneticSaaS\Providers\Core\Bootstrap' ) )
        return $addons;

    $addons[ \BookneticAddon\Templates\TemplatesAddon::getAddonSlug() ] = new \BookneticAddon\Templates\TemplatesAddon();
    return $addons;
});
