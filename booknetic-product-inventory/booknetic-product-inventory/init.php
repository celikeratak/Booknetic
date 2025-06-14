<?php
/*
 * Plugin Name: Product Inventory for Booknetic
 * Description: Add and sell products through Booknetic
 * Version: 1.0.4
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-product-inventory
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter( 'bkntc_addons_load', function ( $addons ) {
	$addons[ \BookneticAddon\Inventory\ProductInventoryAddon::getAddonSlug() ] = new \BookneticAddon\Inventory\ProductInventoryAddon();

	return $addons;
} );
