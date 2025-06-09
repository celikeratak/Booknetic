<?php
/*
 * Plugin Name: Telegram action for Booknetic workflows
 * Description: Get instantly informed about the appointment by the Telegram bot.
 * Version: 1.2.0
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-workflow-telegram
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\TelegramWorkflow\TelegramWorkflowAddon::getAddonSlug() ] = new \BookneticAddon\TelegramWorkflow\TelegramWorkflowAddon();
    return $addons;
});
