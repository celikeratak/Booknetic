<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
    die;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bkntc_templates" );

delete_option( 'bkntc_addon_booknetic-templates_version' );
delete_option( 'bkntc_templates_initial_setup' );