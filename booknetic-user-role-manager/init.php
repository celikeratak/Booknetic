<?php
/*
 * Plugin Name: User Role Manager add-on for Booknetic
 * Description: Create roles with desired permissions, and assign your staff to these roles
 * Version: 1.4.0
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-user-role-manager
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\UserRoleManager\UserRoleManagerAddon::getAddonSlug() ] = new \BookneticAddon\UserRoleManager\UserRoleManagerAddon();

    return $addons;
});
