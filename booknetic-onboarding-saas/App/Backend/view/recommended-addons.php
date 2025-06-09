<?php
defined('ABSPATH') or die();

// Only administrators should access this page.
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

// Include the add-ons management functions
require_once dirname(__DIR__) . '/includes/manage-addons.php';

// Get recommended add-ons
$recommended_addons = get_recommended_addons();

// Get unique categories
$categories = get_addon_categories($recommended_addons);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Pro Dashboard</title>
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/style-admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/manage-addons.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <?php // FontAwesome now loaded via AssetManager ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo ContactUsPAddon::loadAsset('assets/backend/js/manage-addons.js'); ?>"></script>
    
    <!-- Custom CSS for color settings -->
    <style>
        /* Apply color settings from database */
        :root {
          <?php
          // Include data provider functions if not already included
          if (!function_exists('get_help_setting')) {
              require_once dirname(__DIR__) . '/includes/data-provider.php';
          }
          
          // Get color settings
          $color_settings = get_help_setting('color_settings', [
              'primary_color' => '#4050B5',
              'secondary_color' => '#6C757D'
          ]);
          
          // Output color variables
          echo "--primary-color: " . esc_attr($color_settings['primary_color']) . ";\n";
          echo "--primary-color-hover: " . esc_attr($color_settings['primary_color']) . "cc;\n"; // Add transparency for hover
          echo "--primary-color-back: " . esc_attr($color_settings['primary_color']) . "33;\n"; // increased transparency
          echo "--secondary-color: " . esc_attr($color_settings['secondary_color']) . ";\n";
          echo "--secondary-color-hover: " . esc_attr($color_settings['secondary_color']) . "cc;\n"; // Add transparency for hover
          ?>
        }
       
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="contact-us-dashboard">
            <!-- Header Section -->
            <div class="header" style="margin-bottom:35px;">
                <div class="button-group">
                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'dashboard' => 'yes'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-home" style="margin-right:10px;"></i> <?php echo bkntc__('Dashboard') ?>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-file" style="margin-right:10px;"></i> <?php echo bkntc__('Manage Topics') ?>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'categories'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-folder" style="margin-right:10px;"></i> <?php echo bkntc__('Manage Categories') ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'settings'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-cogs" style="margin-right:10px;"></i> <?php echo bkntc__('Settings') ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'updates'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button d-none">
                       <i class="fas fa-cloud-download-alt" style="margin-right:10px;"></i> <?php echo bkntc__('OTA Updates') ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'addons'], 'admin.php')); ?>" 
           class="button-group-item primary-button" title="<?php echo bkntc__('Other Add-ons') ?>">
           <i class="fas fa-store"></i>
        </a>
                </div>
            </div>

    <h4 style="display: flex; justify-content: center; align-items: center;"><?php echo bkntc__('Recommended Add-ons'); ?></h4>
    <p class="addons-description" style="display: flex; justify-content: center; align-items: center;"><?php echo bkntc__('Enhance your Booknetic experience with powerful add-ons.'); ?></p>
    
    <?php 
    // Render category filters
    render_category_filters($categories);
    
    // Render add-ons grid
    render_addons_grid($recommended_addons);
    ?>