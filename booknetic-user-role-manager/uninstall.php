<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
$prefix = $wpdb->prefix . 'bkntc_';

$wpdb->query("DROP TABLE IF EXISTS {$prefix}user_role_manager");

delete_option('bkntc_addon_booknetic-user-role-manager_version');