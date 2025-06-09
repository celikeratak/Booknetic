<?php
defined('ABSPATH') or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use function BookneticAddon\ContactUsP\bkntc__;

require_once dirname(__FILE__) . '/../includes/data-provider.php';
require_once dirname(__FILE__) . '/../includes/color-presets.php';
require_once dirname(__FILE__) . '/../includes/localization.php';

if( !Permission::isAdministrator() )
{
    die();
}



// Get social media links, support link, and feature toggles from help_settings table
$social_links = get_help_setting('social_media_links', []);
$support_link = get_help_setting('support_link', null);
$feature_toggles = get_help_setting('feature_toggles', null);

// Define social media platforms
$social_media_platforms = [
    'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
    'twitter' => ['label' => 'Twitter', 'icon' => 'fab fa-twitter'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
    'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'fab fa-youtube'],
    'pinterest' => ['label' => 'Pinterest', 'icon' => 'fab fa-pinterest']
];

// Get menu links from database
$menu_links = get_help_setting('menu_links', []);
if (empty($menu_links)) {
    $menu_links = [];
}

// If feature toggles are not set, initialize with defaults
if (empty($feature_toggles)) {
    $feature_toggles = (object)[
        'feedback_section' => true,
        'still_need_help' => true,
        'related_articles' => true,
        'livechat' => true,
        'popular_topics' => true
    ];
}

// Ensure support_link is an object
if (is_string($support_link)) {
    $support_link = json_decode($support_link);
} elseif (is_array($support_link)) {
    $support_link = (object) $support_link;
}

// If support link is not in settings, try to get from the old table for migration
if (empty($support_link)) {
    // Create a default support link instead of trying to query a non-existent table
    $support_link = (object)[
        'label' => bkntc__('Contact Support'),
        'url' => 'https://support.booknetic.com',
        'active' => 1,
        'id' => 1
    ];
    
    // Save the default support link to settings
    update_help_setting('support_link', $support_link);
}

// Create a nonce for AJAX requests
$help_center_nonce = wp_create_nonce('help_center_nonce');

// Fetch Live Chat content.
$livechat = get_help_setting('livechat', null);

// Determine the base URL based on GET parameters
$base_url = isset($_GET['page']) && $_GET['page'] === 'booknetic' && isset($_GET['module']) && $_GET['module'] === 'help-center'
    ? "admin.php?page=booknetic&module=help-center"
    : "admin.php?page=booknetic-saas&module=help-center";
?>
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/style-admin.css'); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/manage-categories.css'); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/settings.css'); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/migration.css'); ?>">
  <?php // FontAwesome now loaded via AssetManager ?>
  
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
           class="button-group-item primary-button">
           <i class="fas fa-cogs" style="margin-right:10px;"></i> <?php echo bkntc__('Settings') ?>
        </a>

        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'addons'], 'admin.php')); ?>" 
           class="button-group-item secondary-button" title="<?php echo bkntc__('Other Add-ons') ?>">
           <i class="fas fa-store"></i> 
        </a>
    </div>  

    <!-- Settings Tabs Navigation -->
    <div class="settings-tabs-container mt-4">
      <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="appearance-tab" data-toggle="tab" href="#appearance" role="tab">
            <i class="fas fa-palette mr-2"></i><?php echo bkntc__('Appearance') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="content-tab" data-toggle="tab" href="#content" role="tab">
            <i class="fas fa-edit mr-2"></i><?php echo bkntc__('Content') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="links-tab" data-toggle="tab" href="#links" role="tab">
            <i class="fas fa-link mr-2"></i><?php echo bkntc__('Links') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="advanced-tab" data-toggle="tab" href="#advanced" role="tab">
            <i class="fas fa-cog mr-2"></i><?php echo bkntc__('Advanced') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="update-tab" data-toggle="tab" href="#update" role="tab">
            <i class="fas fa-cloud-download-alt mr-2"></i><?php echo bkntc__('Update') ?>
          </a>
        </li>
      </ul>
    </div>





    <!-- Tab Content -->
    <div class="tab-content mt-3">
      <!-- Appearance Tab -->
      <div class="tab-pane fade show active" id="appearance" role="tabpanel">
        <!-- Color Settings Section -->
        <div class="color-settings-section mb-4" id="color-settings-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-palette text-primary"></i>
                    <h5 class="card-title"><?php echo bkntc__('Color Settings')?></h5>
                </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="primaryColor"><?php echo bkntc__('Primary Color')?></label>
                            <div class="input-group color-picker-group">
                                <input type="text" class="form-control" id="primaryColor" name="primaryColor" placeholder="#4050B5">
                                <div class="input-group-append">
                                    <span class="input-group-text p-0">
                                        <input type="color" class="form-control border-0" id="primaryColorPicker" style="height: 38px; cursor: pointer;">
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted"><?php echo bkntc__('Used for buttons, links, headers, and primary elements')?></small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label for="secondaryColor"><?php echo bkntc__('Secondary Color')?></label>
                            <div class="input-group color-picker-group">
                                <input type="text" class="form-control" id="secondaryColor" name="secondaryColor" placeholder="#6C757D">
                                <div class="input-group-append">
                                    <span class="input-group-text p-0">
                                        <input type="color" class="form-control border-0" id="secondaryColorPicker" style="height: 38px; cursor: pointer;">
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted"><?php echo bkntc__('Used for secondary text, borders, and accents')?></small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label><?php echo bkntc__('Color Presets'); ?></label>
                                <!-- Color Preset Categories Tabs -->
                                <ul class="nav nav-tabs" id="colorPresetTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="material-tab" data-toggle="tab" href="#material-colors" role="tab">
                                            <?php echo bkntc__('Material'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="flat-tab" data-toggle="tab" href="#flat-colors" role="tab">
                                            <?php echo bkntc__('Flat'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dark-tab" data-toggle="tab" href="#dark-colors" role="tab">
                                            <?php echo bkntc__('Dark'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="brand-tab" data-toggle="tab" href="#brand-colors" role="tab">
                                            <?php echo bkntc__('Brand'); ?>
                                        </a>
                                    </li>
                                </ul>
                                
                                <!-- Color Swatches Content -->
                                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="colorPresetTabContent" style="background-color: #f8f9fa;">
                                    <!-- Material Design Colors -->
                                    <div class="tab-pane fade show active" id="material-colors" role="tabpanel">
                                        <div class="color-swatch-container">
                                            <?php
                                            $presets_by_category = get_color_presets_by_category();
                                            
                                            // Display Material Design colors
                                            foreach ($presets_by_category['material'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            
                                            // Display Gradient colors in the Material tab
                                            foreach ($presets_by_category['gradient'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Flat UI Colors -->
                                    <div class="tab-pane fade" id="flat-colors" role="tabpanel">
                                        <div class="color-swatch-container">
                                            <?php
                                            // Display Flat UI colors
                                            foreach ($presets_by_category['flat'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            
                                            // Display Nature colors in the Flat tab
                                            foreach ($presets_by_category['nature'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            
                                            // Display Vibrant colors in the Flat tab
                                            foreach ($presets_by_category['vibrant'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    
                                    <!-- Brand Colors -->
                                    <div class="tab-pane fade" id="brand-colors" role="tabpanel">
                                        <div class="color-swatch-container">
                                            <?php
                                            // Display Booknetic brand color first
                                            $booknetic = get_color_preset('booknetic');
                                            if ($booknetic) {
                                                echo '<div class="color-swatch" data-preset="booknetic" data-primary="' . esc_attr($booknetic['primary']) . '" data-secondary="' . esc_attr($booknetic['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($booknetic['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($booknetic['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            
                                            // Display Brand colors
                                            foreach ($presets_by_category['brand'] as $key => $preset) {
                                                if ($key === 'booknetic') continue; // Skip Booknetic as it's already displayed
                                                
                                                // Special case for Instagram which uses a gradient
                                                if ($key === 'brand_instagram') {
                                                    echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                    echo '<div class="swatch-preview" style="background: linear-gradient(45deg, ' . esc_attr($preset['primary']) . ', ' . esc_attr($preset['secondary']) . ', #fcb045);"></div>';
                                                    echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                    echo '</div>';
                                                } else {
                                                    echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                    echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                    echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                    echo '</div>';
                                                }
                                            }
                                            
                                            // Display Tech colors in the Brand tab
                                            foreach ($presets_by_category['tech'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <!-- Dark Colors -->
                                    <div class="tab-pane fade" id="dark-colors" role="tabpanel">
                                        <div class="color-swatch-container">
                                            <?php
                                            // Display Dark colors
                                            foreach ($presets_by_category['dark'] as $key => $preset) {
                                                echo '<div class="color-swatch" data-preset="' . esc_attr($key) . '" data-primary="' . esc_attr($preset['primary']) . '" data-secondary="' . esc_attr($preset['secondary']) . '">';
                                                echo '<div class="swatch-preview" style="background-color: ' . esc_attr($preset['primary']) . ';"></div>';
                                                echo '<div class="swatch-name">' . esc_html($preset['name']) . '</div>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                        </div>



                        </div>

                        
                    </div>



                    <div class="col-md-6">
                        <div class="color-preview p-3 border rounded">
                            <h6 class="mb-3"><?php echo bkntc__('Live Preview')?></h6>
                            
                            <!-- Category Card Preview -->
                            <div class="preview-section mb-4">
                                <h6 class="preview-title"><?php echo bkntc__('Category Card')?></h6>
                                <div class="preview-category-card">
                                    <i class="fas fa-book"></i>
                                    <div class="content">
                                        <h2><?php echo bkntc__('Getting Started')?></h2>
                                        <p><?php echo bkntc__('Learn the basics of using the booking system...')?></p>
                                        <div class="topic-count">
                                          <a>  12 <?php echo bkntc__('Topics')?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Topic Card Preview -->
                            <div class="preview-section">
                                <h6 class="preview-title"><?php echo bkntc__('Topic Item')?></h6>
                                <div class="preview-topic-item">
                                    <div class="topic-header">
                                        <h2><?php echo bkntc__('How to create your first booking')?></h2>
                                        <span class="topic-views-count">
                                            <i class="fa fa-eye"></i> 42
                                        </span>
                                    </div>
                                    <p><?php echo bkntc__('Learn how to set up and create your first appointment booking...')?></p>
                                </div>
                            </div>
                            
                            <!-- Buttons Preview -->
                            <div class="preview-section mt-4">
                                <h6 class="preview-title"><?php echo bkntc__('Buttons')?></h6>
                                <div class="d-flex mt-2">
                                    <button type="button" class="btn btn-secondary" id="previewPrimaryBtn"><?php echo bkntc__('Primary')?></button>
                                    <button type="button" class="btn btn-secondary" id="previewSecondaryBtn"><?php echo bkntc__('Secondary')?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            
                <button type="button" class="btn btn-primary" id="saveColorSettings">
                    <i class="fas fa-save" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Save Changes')?>
                </button>
            </div>
        </div>



    <!-- Custom CSS Section -->
    <div class="custom-css-section mb-4" id="custom-css-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-code text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('Custom CSS')?></h5>
            </div>
            <div class="card-body">
                <form id="customCssForm">
                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Add your custom CSS rules')?></label>
                    <textarea class="form-control" name="custom_css" id="custom_css" rows="8" style="font-family: monospace;"><?php echo esc_textarea(get_help_setting('custom_css', '')); ?></textarea>
                    <small class="form-text text-muted"><?php echo bkntc__('These CSS rules will be applied to the Help Center pages.')?></small>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="saveCustomCss">
                        <i class="fas fa-save mr-2" style="color: white;"></i><?php echo bkntc__('Save Changes') ?>
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>
      </div><!-- End Appearance Tab -->
      
      <!-- Content Tab -->
      <div class="tab-pane fade" id="content" role="tabpanel">
       
      <?php require_once dirname(__FILE__) . '/modals/custom-page-modal.php'; ?>

 
      <div class="row">
    <!-- Copyright Text Section -->
    <div class="copyright-section mb-4 col-md-6" id="copyright-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-copyright text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('Edit Footer Text')?></h5>
            </div>
            <div class="card-body">
                <form id="copyrightForm">
                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Footer Text')?></label>
                    <input type="text" placeholder="All rights reserved." class="form-control" name="copyright_text" id="copyright_text" value="<?php echo esc_attr(get_help_setting('copyright_text', 'Booknetic. All rights reserved.')); ?>" required>
                    <small class="form-text text-muted"><?php echo bkntc__('This text will appear in the footer. Example: © 2025') . ' ' . esc_html(\BookneticApp\Providers\Helpers\Helper::getOption('backend_title', 'Help Center')); ?> - Your text</small>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="saveCopyright">
                        <i class="fas fa-save mr-2" style="color: white;"></i><?php echo bkntc__('Save Changes') ?>
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Dummy Data Section -->
    <div class="dummy-data-section mb-4 col-md-12" id="dummy-data-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-database text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('Dummy Data')?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-3"><?php echo bkntc__('Import sample data to quickly populate your Help Center with categories, topics, views, and search logs.')?></p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><?php echo bkntc__('Warning: If you choose to clear existing data, all current help center content will be permanently deleted. Make sure to back up your data first if needed.')?></span>
                        </div>
                        
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="clearExistingData">
                                <label class="custom-control-label" for="clearExistingData"><?php echo bkntc__('Clear existing help center data before adding dummy data')?></label>
                                <div class="form-text text-muted mt-1"><?php echo bkntc__('This will remove all existing help center content')?></div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary" id="importDummyData">
                            <i class="fas fa-database" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Import Dummy Data')?>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5><?php echo bkntc__('What will be imported?')?></h5>
                                <ul class="pl-3">
                                    <li><?php echo bkntc__('12 help center categories with icons')?></li>
                                    <li><?php echo bkntc__('100+ help topics with sample content')?></li>
                                    <li><?php echo bkntc__('400-600 sample search logs')?></li>
                                    <li><?php echo bkntc__('Color settings and theme customizations')?></li>
                                    <li><?php echo bkntc__('Social media links and menu configuration')?></li>
                                    <li><?php echo bkntc__('Live chat settings')?></li>
                                    <li><?php echo bkntc__('Sample user feedback data')?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="dummyDataResult" class="mt-4" style="display: none;"></div>
                
                <!-- Results will be displayed here after import -->
                <div id="dummyDataDisplay" class="mt-4">
                    <!-- The settings preview and data tables will be shown here after successful import -->
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Success and error messages -->
    <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
    <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>

    


    <!-- Database Tables Section -->
      </div><!-- End Content Tab -->
      
      <!-- Links Tab -->
      <div class="tab-pane fade" id="links" role="tabpanel">
      

        <!-- Menu Links Section -->
        <div class="menu-links-section mb-4" id="menu-links-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bars text-primary"></i>
                    <h5 class="card-title"><?php echo bkntc__('Menu Links')?></h5>
                    <div>
                        <button type="button" class="btn btn-primary" id="addMenuLink">
                            <i class="fas fa-plus" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Add New')?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo bkntc__('Label')?></th>
                                    <th><?php echo bkntc__('URL')?></th>
                                    <th><?php echo bkntc__('Order')?></th>
                                    <th><?php echo bkntc__('Actions')?></th>
                                </tr>
                            </thead>
                            <tbody id="menuLinksTableBody">
                                <?php foreach ($menu_links as $link): ?>
                                <tr data-id="<?php echo esc_attr($link->id); ?>">
                                    <td><?php echo esc_html($link->label); ?></td>
                                    <td><a href="<?php echo esc_url($link->url); ?>" target="_blank"><?php echo esc_url($link->url); ?></a></td>
                                    <td><?php echo esc_html(isset($link->order) ? $link->order : 0); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-menu-link" data-id="<?php echo esc_attr($link->id); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-menu-link" data-id="<?php echo esc_attr($link->id); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Link Section -->
        <div class="support-link-section mb-4" id="support-link-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-life-ring text-primary"></i>
                    <h5 class="card-title"><?php echo bkntc__('Support Link')?></h5>
                </div>
                <div class="card-body">
                    <form id="supportLinkForm">
                        <div class="form-group">
                            <label for="support_url"><?php echo bkntc__('Support URL')?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-link text-muted"></i>
                                </span>
                                <input type="url" class="form-control" id="support_url" name="support_url" 
                                    value="<?php echo esc_attr(get_help_setting('support_url', '')); ?>" 
                                    placeholder="https://">
                            </div>
                            <small class="form-text text-muted"><?php echo bkntc__('This link will be used for the "Contact Support" button')?></small>
                        </div>

                        <div class="form-group">
                            <label for="support_text"><?php echo bkntc__('Support Text')?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-font text-muted"></i>
                                </span>
                                <input type="text" class="form-control" id="support_text" name="support_text" 
                                    value="<?php echo esc_attr(get_help_setting('support_text', 'Contact Support')); ?>">
                            </div>
                            <small class="form-text text-muted"><?php echo bkntc__('Text to display on the support button')?></small>
                        </div>

                        <button type="button" class="btn btn-primary" id="saveSupportLink">
                            <i class="fas fa-save" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Save Changes')?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
          <!-- Social Media Links Section -->
    <div class="social-media-list mb-6" id="social-media-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-share-alt text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('Social Media Links')?></h5>
                <div>
                    <button type="button" class="btn btn-primary" id="addSocialLink">
                        <i class="fas fa-plus" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Add New')?>
                    </button>
                </div>
            </div>
            <div class="card-body">
               
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo bkntc__('Icon')?></th>
                                <th><?php echo bkntc__('Platform')?></th>
                                <th><?php echo bkntc__('URL')?></th>
                                <th><?php echo bkntc__('Actions')?></th>
                            </tr>
                        </thead>
                        <tbody id="socialMediaTableBody">
                            <?php if (!empty($social_links)): ?>
                                <?php foreach ($social_links as $link): ?>
                                    <tr data-id="<?php echo esc_attr($link->id); ?>">
                                        <td>
                                            <i class="<?php echo esc_attr($link->icon); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_html($link->platform ?? ''); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url($link->url); ?>" target="_blank"><?php echo esc_html($link->url); ?></a>
                                        </td>
                                        
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-social-link" data-id="<?php echo esc_attr($link->id); ?>" data-platform="<?php echo esc_attr($link->platform ?? ''); ?>" data-icon="<?php echo esc_attr($link->icon); ?>" data-url="<?php echo esc_attr($link->url); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-social-link" data-id="<?php echo esc_attr($link->id); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="no-data-row">
                                    <td colspan="4" class="text-center"><?php echo bkntc__('No social media links added yet.')?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
      </div><!-- End Links Tab -->
      
      
      <!-- Advanced Tab -->
      <div class="tab-pane fade" id="advanced" role="tabpanel">


    <!-- Feature Toggles Section -->
    <div class="feature-toggles-section mb-4" id="feature-toggles-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-toggle-on text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('Manage Options')?></h5>
            </div>
            <div class="card-body">
            <div class="alert alert-info" style="margin-bottom: 30px;">
                    <i class="fas fa-info-circle"></i>
                    <?php echo bkntc__('Enable or disable specific features in the Help Center. Changes will be applied immediately.'); ?>
                </div>

                <div class="feature-options-list">
                    <!-- Feedback Section Toggle -->
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="feedback_section" name="feedback_section" <?php echo $feature_toggles->feedback_section ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="feedback_section"><?php echo bkntc__('Feedback Section'); ?></label>
                            <div class="form-text text-muted mt-1"><?php echo bkntc__('Allow users to provide feedback on help articles (thumbs up/down)'); ?></div>
                        </div>
                    </div>

                    <!-- Still Need Help Toggle -->
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="still_need_help" name="still_need_help" <?php echo $feature_toggles->still_need_help ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="still_need_help">  <?php echo bkntc__('Still Need Help? Section'); ?></label>
                            <div class="form-text text-muted mt-1"><?php echo bkntc__('Display the "Still need help?" section with support button at the bottom of articles'); ?></div>
                        </div>
                    </div>

                    <!-- Related Articles Toggle -->
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="related_articles" name="related_articles" <?php echo $feature_toggles->related_articles ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="related_articles"><?php echo bkntc__('Related Articles Section'); ?></label>
                            <div class="form-text text-muted mt-1"><?php echo bkntc__('Show related articles at the bottom of each help topic'); ?></div>
                        </div>
                    </div>

                    <!-- Live Chat Toggle -->
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="livechat" name="livechat" <?php echo $feature_toggles->livechat ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="livechat"><?php echo bkntc__('Live Chat Section'); ?></label>
                            <div class="form-text text-muted mt-1"><?php echo bkntc__('Enable the live chat widget in the help center'); ?></div>
                        </div>
                    </div>

                    <!-- Popular Topics Toggle -->
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="popular_topics" name="popular_topics" <?php echo $feature_toggles->popular_topics ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="popular_topics"><?php echo bkntc__('Popular Topics Section'); ?></label>
                            <div class="form-text text-muted mt-1"><?php echo bkntc__('Display popular topics under the search box on the main page'); ?></div>
                        </div>
                    </div>
                </div>

                

                <button type="button" class="btn btn-primary mt-4" id="saveFeatureToggles">
                    <i class="fas fa-save" style="color: white; margin-right:5px;"></i> <?php echo bkntc__('Save Changes')?>
                </button>
            </div>
        </div>
    </div>

 <!-- ChatGPT API Settings Section -->
 <div class="chatgpt-api-section mb-4" id="chatgpt-api-section">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-robot text-primary"></i>
                <h5 class="card-title"><?php echo bkntc__('ChatGPT API Settings')?></h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="chatgptApiKey"><?php echo bkntc__('API Key')?></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="chatgptApiKey" name="chatgptApiKey" 
                            value="<?php echo esc_attr(get_help_setting('chatgpt_api_key', '')); ?>" 
                            placeholder="sk-...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?php echo bkntc__('Your OpenAI API key for ChatGPT integration')?></small>
                </div>
                
                <div class="form-group mt-3">
                    <label for="chatgptModel"><?php echo bkntc__('Model')?></label>
                    <select class="form-control" id="chatgptModel" name="chatgptModel">
                        <option value="gpt-3.5-turbo" <?php selected(get_help_setting('chatgpt_model', 'gpt-3.5-turbo'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        <option value="gpt-4" <?php selected(get_help_setting('chatgpt_model', 'gpt-3.5-turbo'), 'gpt-4'); ?>>GPT-4</option>
                        <option value="gpt-4-turbo" <?php selected(get_help_setting('chatgpt_model', 'gpt-3.5-turbo'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                    </select>
                    <small class="form-text text-muted"><?php echo bkntc__('Select which OpenAI model to use')?></small>
                </div>
                
                <div class="form-group mt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="enableChatGPT" name="enableChatGPT" <?php echo get_help_setting('enable_chatgpt', '0') == '1' ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="enableChatGPT"><?php echo bkntc__('Enable ChatGPT Integration'); ?></label>
                        <div class="form-text text-muted mt-1"><?php echo bkntc__('Turn on/off the ChatGPT integration features'); ?></div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-primary mt-3" id="saveChatGPTSettings">
                    <i class="fas fa-save"></i> <?php echo bkntc__('Save Settings')?>
                </button>
            </div>
        </div>
    </div>


<div class="row">
       
    
        </div>



    

    </div><!-- End Advanced Tab -->
      
      <!-- Update Tab -->
      <div class="tab-pane fade" id="update" role="tabpanel">
        <?php include_once dirname(__FILE__) . '/migration-section.php'; ?>

      </div>
      
</div><!-- End Tab Content -->
                            </div>
<!-- Include Social Link Modal -->
<?php require_once dirname(__FILE__) . '/modals/social-link-modal.php'; ?>


<!-- Include Menu Link Modal -->
<?php require_once dirname(__FILE__) . '/modals/menu-link-modal.php'; ?>

<!-- Initialize JavaScript variables and libraries -->
<script>
    // Set up AJAX URL and nonce for JavaScript files
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var help_center_nonce = '<?php echo wp_create_nonce('help_center_nonce'); ?>';
    
    // Initialize helpCenterAjax object for AJAX calls
    var helpCenterAjax = {
        ajaxUrl: ajaxurl,
        nonce: help_center_nonce
    };
    
    // Define bookneticAjaxUrl for the main Booknetic plugin's ping functionality
    var bookneticAjaxUrl = '<?php echo admin_url("admin.php?page=booknetic-saas&ajax=1"); ?>';
    
    // Intercept AJAX requests to fix ping functionality
    (function($) {
        var originalAjax = $.ajax;
        $.ajax = function(options) {
            // Check if this is a ping request from the main Booknetic plugin
            if (options && options.data && typeof options.data === 'object' && options.data.action === 'ping') {
                // Override the URL to use the Booknetic format instead of admin-ajax.php
                options.url = bookneticAjaxUrl;
            }
            return originalAjax.apply(this, arguments);
        };
    })(jQuery);
</script>

<!-- JavaScript files are included at the bottom of the page -->

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
      
      // Convert hex to RGB for box-shadow and other effects
      $hex = ltrim($color_settings['primary_color'], '#');
      if (strlen($hex) == 3) {
          $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
          $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
          $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
      } else {
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
      }
      echo "--primary-color-rgb: " . $r . ", " . $g . ", " . $b . ";\n";
      ?>
    }
    
</style>


<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="iconPickerModalLabel"><?php echo bkntc__('Select Icon')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="iconSearch" placeholder="<?php echo bkntc__('Search icons...')?>">
                </div>
                <div class="row row-cols-2 row-cols-md-4 g-3" id="iconGrid">
                    <!-- Icons will be populated here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Include JavaScript files -->
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/color-settings.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/color-presets.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/social-media.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/support-link.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/menu-links.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/custom-css.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/copyright.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/dummy-data.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/livechat.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/database-tables.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/chatgpt-settings.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/feature-toggles.js'); ?>"></script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/icon-picker.js'); ?>"></script>
<script>
// Define ajaxurl if not already defined
var ajaxurl = ajaxurl || '<?php echo admin_url('admin-ajax.php'); ?>';

<?php
// Include the centralized localization file
require_once dirname(__FILE__) . '/../includes/localization.php';

// Initialize the booknetic object and load all context-specific translations
echo "var booknetic = booknetic || {};\n";

// Load all context-specific translations
$contexts = ['general', 'help-center', 'categories', 'social-media', 'support-link', 'livechat'];
$all_translations = [];

foreach ($contexts as $context) {
    $all_translations = array_merge($all_translations, get_booknetic_help_localization($context));
}

// Output the combined translations
echo "booknetic.localization = " . json_encode($all_translations) . ";\n";

// Create the helpCenterAjax object for AJAX requests
echo "var helpCenterAjax = { \n";
echo "    ajaxUrl: " . json_encode(admin_url('admin-ajax.php')) . ", \n";
echo "    nonce: " . json_encode(wp_create_nonce('help_center_nonce')) . " \n";
echo "};\n";

// Create the booknetic_help_i18n object for backward compatibility
// This ensures any code still using booknetic_help_i18n will continue to work
echo "var booknetic_help_i18n = " . json_encode($all_translations) . ";\n";
?>


</script>
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/migration.js'); ?>"></script>
<!-- Migration script is loaded via WordPress enqueue system -->
<script>
jQuery(document).ready(function($) {
    // Check if migration elements exist
    if ($('#runMigration').length) {
        // Direct event binding as a fallback
        $('#runMigration').on('click', function() {
            // Get AJAX URL and nonce
            const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : '<?php echo admin_url("admin-ajax.php"); ?>';
            const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '<?php echo wp_create_nonce("help_center_nonce"); ?>';
            
            // Get backup option
            const createBackup = $('#backupBeforeMigration').is(':checked');
            
            // Show loading
            if (typeof booknetic !== 'undefined' && typeof booknetic.loading === 'function') {
                booknetic.loading(true);
            }
            
            // Make AJAX request
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_run_migration',
                    _wpnonce: nonce,
                    create_backup: createBackup
                },
                success: function(response) {
                    if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                        booknetic.toast('Migration completed', 'success');
                    } else {
                        alert('Migration completed');
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                        booknetic.toast('Migration failed: ' + error, 'error');
                    } else {
                        alert('Migration failed: ' + error);
                    }
                },
                complete: function() {
                    if (typeof booknetic !== 'undefined' && typeof booknetic.loading === 'function') {
                        booknetic.loading(false);
                    }
                }
            });
        });
    } else {
    }
});
</script>

<!-- Initialize the tabs -->
<script>
jQuery(document).ready(function($) {
    // Initialize Bootstrap tabs
    $('#settingsTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Handle URL hash for direct tab access
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const tabId = `#${hash}-tab`;
        if ($(tabId).length) {
            $(tabId).tab('show');
        }
    }
    
    // Update URL hash when tab changes
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href').substring(1);
        history.replaceState(null, null, `#${target}`);
    });
});
</script>