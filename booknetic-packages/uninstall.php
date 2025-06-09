<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$prefix = $wpdb->prefix . 'bkntc_';

$wpdb->query("DROP TABLE IF EXISTS {$prefix}packages");
$wpdb->query("DROP TABLE IF EXISTS {$prefix}package_bookings");

delete_option('bkntc_addon_booknetic-packages_version');
