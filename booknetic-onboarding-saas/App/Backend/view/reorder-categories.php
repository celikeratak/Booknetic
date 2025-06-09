<?php
// AJAX handling is now moved to App/Backend/ajax/ajax-actions.php

// For backward compatibility, also handle direct POST to this page
if (isset($_POST['order']) && is_array($_POST['order'])) {
    // If the function exists, call it directly
    if (function_exists('booknetic_handle_reorder_categories')) {
        booknetic_handle_reorder_categories();
    } else {
        // Otherwise, redirect to the admin-ajax.php endpoint
        wp_redirect(admin_url('admin-ajax.php?action=booknetic_reorder_categories'));
        exit;
    }
}

// Start normal page rendering
ob_start(); // Start output buffering for HTML content
defined('ABSPATH') or die();

// Only administrators should access this page.
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

global $wpdb;

use function BookneticAddon\ContactUsP\bkntc__;
use BookneticApp\Providers\DB\DB;
use BookneticAddon\ContactUsP\ContactUsPAddon;

// Ensure we have database access
if (!$wpdb) {
    wp_die('Database connection error');
}

// Determine the base URL based on the incoming GET parameters.
$base_url = (isset($_GET['page']) && $_GET['page'] === 'booknetic' && isset($_GET['module']) && $_GET['module'] === 'help-center')
    ? "admin.php?page=booknetic&module=help-center"
    : "admin.php?page=booknetic-saas&module=help-center";


try {
    // Fetch categories using wpdb
    $table_name = $wpdb->prefix . 'bkntc_help_categories';
    // Don't use prepare if there are no variables to escape
    $categories = $wpdb->get_results(
        "SELECT * FROM `{$table_name}` 
         ORDER BY CAST(COALESCE(order_number, 999999) AS SIGNED) ASC, id ASC"
    );
    
} catch (Exception $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo bkntc__('Reorder Categories') ?></title>
    <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/css/style-admin.css' ); ?>">
    <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/css/manage-categories.css' ); ?>">
    <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/css/reorder-categories.css' ); ?>">
    <?php // FontAwesome now loaded via AssetManager ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Using booknetic.toast for notifications -->
    

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
      <div class="topics-list">
    <div class="button-group">
        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'dashboard'], 'admin.php')); ?>" 
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
           class="button-group-item secondary-button" title="<?php echo bkntc__('Other Add-ons') ?>">
           <i class="fas fa-store"></i> 
        </a>
    </div>
    
    <div class="button-group">
        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'categories'], 'admin.php')); ?>" 
           class="button-group-item secondary-button" id="addCategoryBtn" style="height: 40px;">
           <i class="fas fa-plus" style="margin-right:10px;"></i> <?php echo bkntc__('Add New Category') ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'reorder_categories'], 'admin.php')); ?>" 
           class="button-group-item primary-button" style="height: 40px;">
           <i class="fas fa-sort" style="margin-right:10px;"></i> <?php echo bkntc__('Reorder Categories') ?>
        </a>
    <!-- Search Bar -->
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <input type="hidden" name="module" value="help-center">
            <input type="hidden" name="view" value="reorder_categories">
            <input type="hidden" name="categories" value="yes">
            <input style="height: 40px;" class="button-group-item secondary-button for-hover" type="text" name="search" id="search-input" placeholder="<?php echo bkntc__('Search categories...') ?>" value="<?php echo isset($search_query) ? esc_attr($search_query) : ''; ?>">
        </form>
   
    </div>
<div class="categories-list">
        <div class="reorder-tools">
            <button type="button" id="resetOrderBtn" class="btn btn-secondary">
                <i class="fas fa-undo"></i> <?php echo bkntc__('Reset Order') ?>
            </button>
            <button type="button" id="saveOrderBtn" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo bkntc__('Save Order') ?>
            </button>
        </div>

        <div class="categories-container-wrapper">
            <div id="categories-container" class="categories-container">
                <?php $position = 1; foreach ($categories as $category): ?>
                    <div class="category-item" data-id="<?php echo esc_attr($category->id); ?>" data-original-position="<?php echo esc_attr($position); ?>">
                        <span class="position-indicator"><?php echo esc_html($position++); ?></span>
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <i class="fas <?php echo esc_attr(!empty($category->icon) ? $category->icon : 'fa-book'); ?> category-icon"></i>
                        <span class="category-name"><?php echo esc_html($category->name); ?></span>
                        <div class="item-actions">
                            <button type="button" class="move-up" title="<?php echo bkntc__('Move Up') ?>">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="move-down" title="<?php echo bkntc__('Move Down') ?>">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="saving-indicator">
            <div class="saving-spinner"></div>
            <span><?php echo bkntc__('Saving changes...') ?></span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        // Define translations and options for the JavaScript file
        var booknetic = booknetic || {};
        booknetic.options = booknetic.options || {};
        
        // Define the helpCenterAjax object for AJAX requests
        var helpCenterAjax = {
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('reorder_categories'); ?>'
        };
        
        // Add translation strings for the reorder-categories.js file
        booknetic.options.yes_proceed = '<?php echo esc_js(bkntc__('Yes, proceed')); ?>';
        booknetic.options.cancel = '<?php echo esc_js(bkntc__('Cancel')); ?>';
        booknetic.options.order_saved_success = '<?php echo esc_js(bkntc__('Order saved successfully!')); ?>';
        booknetic.options.error_saving_order = '<?php echo esc_js(bkntc__('Error saving order. Please try again.')); ?>';
        booknetic.options.invalid_response = '<?php echo esc_js(bkntc__('Invalid response from server')); ?>';
        booknetic.options.server_returned_html = '<?php echo esc_js(bkntc__('Server returned HTML instead of JSON. Please check server logs.')); ?>';
        booknetic.options.error_in_server_response = '<?php echo esc_js(bkntc__('Error in server response')); ?>';
        booknetic.options.invalid_json_response = '<?php echo esc_js(bkntc__('Invalid JSON response from server')); ?>';
        booknetic.options.network_error = '<?php echo esc_js(bkntc__('Network error. Please try again.')); ?>';
        booknetic.options.no_changes_to_save = '<?php echo esc_js(bkntc__('No changes to save')); ?>';
        booknetic.options.order_has_been_reset = '<?php echo esc_js(bkntc__('Order has been reset')); ?>';
        booknetic.options.unsaved_changes_warning = '<?php echo esc_js(bkntc__('You have unsaved changes. Are you sure you want to leave?')); ?>';
        
        // helpCenterAjax object is defined above
    </script>
    <script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/reorder-categories.js'); ?>"></script>
            </div>
        </div>
    </div>
</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer.
?>
