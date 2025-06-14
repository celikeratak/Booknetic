<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$prefix = $wpdb->prefix . 'bkntc_';

$wpdb->query("DROP TABLE IF EXISTS {$prefix}products");
$wpdb->query("DROP TABLE IF EXISTS {$prefix}product_purchase_logs");

delete_option('bkntc_addon_booknetic-product-inventory_version');
