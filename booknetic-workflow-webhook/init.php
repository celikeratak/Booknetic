<?php

/*
 * Plugin Name: Webhook action for Booknetic workflows
 * Description: Trigger webhook to build your workflow.
 * Version: 1.2.0
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-workflow-webhook
 */

use BookneticAddon\WebhookWorkflow\WebhookWorkflowAddon;

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter( 'bkntc_addons_load', function ( $addons ) {
    $addons[ WebhookWorkflowAddon::getAddonSlug() ] = new WebhookWorkflowAddon();

    return $addons;
} );
