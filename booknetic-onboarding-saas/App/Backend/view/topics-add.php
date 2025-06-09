    <?php
ob_start(); // Start output buffering immediately.
defined('ABSPATH') or die();

// Only administrators should access this page.
if ( ! current_user_can('manage_options') ) {
    wp_die('You do not have sufficient permissions to access this page.');
}

use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

// Include necessary functions
require_once dirname(__FILE__) . '/../includes/manage-topics.php';

// Process form submission and get data for the template
$data = process_help_topic_submission();

// Extract variables from the data array
extract($data);

// Global variables needed for the template
global $wpdb;

// Create a nonce for file uploads
$upload_nonce = wp_create_nonce('upload_attachment_nonce');

// We can't use wp_enqueue_script in the middle of a page, so we'll include scripts directly
$plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));
$js_url = $plugin_url . 'assets/backend/js/manage-topics.js';

// Prepare localization data
$localization_data = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'upload_nonce' => $upload_nonce,
    'content_placeholder' => bkntc__('Enter your content here...'),
    'select_category' => bkntc__('Select Category'),
    'uploading' => bkntc__('Uploading...'),
    'upload_failed' => bkntc__('Upload failed'),
    'unknown_error' => bkntc__('Unknown error'),
    'invalid_response' => bkntc__('Invalid response from server'),
    'server_error' => bkntc__('Server error'),
    'view_file' => bkntc__('View File'),
    'copy_link' => bkntc__('Copy Link'),
    'copied' => bkntc__('Copied!'),
    // Translation related strings
    'translating' => bkntc__('Translating content...'),
    'translation_success' => bkntc__('Content translated successfully!'),
    'translation_error' => bkntc__('Failed to translate content'),
    'select_language' => bkntc__('Please select a target language'),
    'no_content' => bkntc__('Please extract content first before translating')
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Pro Dashboard</title>
    <?php 
    // Load WordPress header scripts - this ensures wp-includes/js/media-views.js is loaded
    do_action('admin_print_scripts');
    ?>
    <!-- jQuery -->    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Summernote Editor -->    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>
    
    <!-- Select2 -->    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Plugin CSS -->    
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/style-admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/manage-topics.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/index.css'); ?>">
    
    <!-- Plugin JS -->    
    <script type="text/javascript" src="<?php echo ContactUsPAddon::loadAsset('assets/backend/js/index.js'); ?>"></script>
    
    <!-- We'll use a custom file upload solution instead of WordPress Media Library -->
    
    <!-- Icons -->    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <?php // FontAwesome now loaded via AssetManager ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

</head>
<body>

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
            <div class="header">
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

                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'addons'], 'admin.php')); ?>" 
           class="button-group-item secondary-button" title="<?php echo bkntc__('Other Add-ons') ?>">
           <i class="fas fa-store"></i> 
        </a>

                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php')); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-arrow-left" style="margin-right:10px;"></i> <?php echo bkntc__('Back') ?>
                    </a>
                    
                </div>
            </div>
            <div class="button-group">
    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics', 'task' => 'add'], 'admin.php')); ?>" 
       class="button-group-item primary-button" style="height: 40px;">
       <i class="fas fa-plus" style="margin-right:10px;"></i> <?php echo bkntc__('Add New Topic') ?>
    </a>
 <!-- Search Bar -->
    <form method="get" action="">
      <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
      <input type="hidden" name="module" value="help-center">
      <input type="hidden" name="view" value="topics">
     <input class="button-group-item secondary-button for-hover" style="height: 40px;" type="text" name="search" placeholder="<?php echo bkntc__('Search topics...') ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
    </form>
</div>
            <h4 style="display: flex; justify-content: center; align-items: center; margin-top: 20px; "><?php echo bkntc__('Add New Topic'); ?></h4>
    <p class="addons-description" style="display: flex; justify-content: center; align-items: center;"><?php echo bkntc__('Add new topic to your help center.'); ?></p>
    
            
            <?php if ( isset($error) ) : ?>
                <p style="color: red;"><?php echo esc_html($error); ?></p>
            <?php endif; ?>
            
            <!-- Documentation URL Import Section -->
            <div class="documentation-import-section">
                <h5><?php echo bkntc__('Import from Booknetic resources'); ?></h5>
                <p class="import-description"><?php echo bkntc__('Enter a URL from Booknetic documentation or features pages to automatically extract content.'); ?></p>
                <div class="import-form-row">
                    <input type="url" id="documentation_url" class="documentation-url-input" placeholder="<?php echo bkntc__('(e.g.) https://www.booknetic.com/documentation/notifications'); ?>">
                    <button type="button" id="extract_content_button" class="extract-button primary-button-help" style="margin: 0 !important;">
                        <i class="fas fa-download"></i> <?php echo bkntc__('Import'); ?>
                    </button>
                </div>
                <div id="extraction_status" class="extraction-status" style="display: none;"></div>
                
                <!-- Translation Section (initially hidden) -->
                <div id="translation_section" class="translation-section" style="display: none; margin-top: 15px;">
                    <h5><?php echo bkntc__('Translate Content'); ?> (<?php echo bkntc__('Optional'); ?>)</h5>
                    <p class="import-description"><?php echo bkntc__('Select a language to translate the extracted content.'); ?></p>
                    <div class="modern-language-selector">
                        <div class="language-selection-area">
                            <div class="selected-language-display">
                                <span class="selected-language-label"><?php echo bkntc__('Target Language:'); ?></span>
                                <span class="selected-language-value" id="selected_language_display"><?php echo bkntc__('Select language'); ?></span>
                                <input type="hidden" id="target_language" value="">
                            </div>
                            
                            <div class="quick-language-buttons">
                                <button type="button" class="language-btn" data-lang="es" data-name="<?php echo bkntc__('Spanish'); ?>">
                                    <span class="lang-flag">🇪🇸</span> <?php echo bkntc__('ES'); ?>
                                </button>
                                <button type="button" class="language-btn" data-lang="fr" data-name="<?php echo bkntc__('French'); ?>">
                                    <span class="lang-flag">🇫🇷</span> <?php echo bkntc__('FR'); ?>
                                </button>
                                <button type="button" class="language-btn" data-lang="de" data-name="<?php echo bkntc__('German'); ?>">
                                    <span class="lang-flag">🇩🇪</span> <?php echo bkntc__('DE'); ?>
                                </button>
                                <button type="button" class="language-btn" data-lang="it" data-name="<?php echo bkntc__('Italian'); ?>">
                                    <span class="lang-flag">🇮🇹</span> <?php echo bkntc__('IT'); ?>
                                </button>
                                <button type="button" class="language-btn" data-lang="nl" data-name="<?php echo bkntc__('Dutch'); ?>">
                                    <span class="lang-flag">🇳🇱</span> <?php echo bkntc__('NL'); ?>
                                </button>
                                <button type="button" class="language-btn more-langs" id="show_more_languages">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="button" id="translate_content_button" class="primary-button-help translate-button" disabled>
                            <i class="fas fa-language" style="margin-right: 5px;"></i> <?php echo bkntc__('Translate'); ?>
                        </button>
                    </div>
                    
                    <!-- Language Modal -->
                    <div id="language_modal" class="language-modal">
                        <div class="language-modal-content">
                            <div class="language-modal-header">
                                <h5><?php echo bkntc__('Select Language'); ?></h5>
                                <button type="button" class="close-modal"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="language-modal-body">
                                <div class="language-search">
                                    <input type="text" id="language_search" placeholder="<?php echo bkntc__('Search languages...'); ?>">
                                    <i class="fas fa-search search-icon"></i>
                                </div>
                                <div class="language-list">
                                    <div class="language-item" data-lang="ar" data-name="<?php echo bkntc__('Arabic'); ?>">
                                        <span class="lang-flag">🇦🇪</span>
                                        <span class="lang-name"><?php echo bkntc__('Arabic'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="zh" data-name="<?php echo bkntc__('Chinese'); ?>">
                                        <span class="lang-flag">🇨🇳</span>
                                        <span class="lang-name"><?php echo bkntc__('Chinese'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="nl" data-name="<?php echo bkntc__('Dutch'); ?>">
                                        <span class="lang-flag">🇳🇱</span>
                                        <span class="lang-name"><?php echo bkntc__('Dutch'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="en" data-name="<?php echo bkntc__('English'); ?>">
                                        <span class="lang-flag">🇬🇧</span>
                                        <span class="lang-name"><?php echo bkntc__('English'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="fr" data-name="<?php echo bkntc__('French'); ?>">
                                        <span class="lang-flag">🇫🇷</span>
                                        <span class="lang-name"><?php echo bkntc__('French'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="de" data-name="<?php echo bkntc__('German'); ?>">
                                        <span class="lang-flag">🇩🇪</span>
                                        <span class="lang-name"><?php echo bkntc__('German'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="hi" data-name="<?php echo bkntc__('Hindi'); ?>">
                                        <span class="lang-flag">🇮🇳</span>
                                        <span class="lang-name"><?php echo bkntc__('Hindi'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="it" data-name="<?php echo bkntc__('Italian'); ?>">
                                        <span class="lang-flag">🇮🇹</span>
                                        <span class="lang-name"><?php echo bkntc__('Italian'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="ja" data-name="<?php echo bkntc__('Japanese'); ?>">
                                        <span class="lang-flag">🇯🇵</span>
                                        <span class="lang-name"><?php echo bkntc__('Japanese'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="ko" data-name="<?php echo bkntc__('Korean'); ?>">
                                        <span class="lang-flag">🇰🇷</span>
                                        <span class="lang-name"><?php echo bkntc__('Korean'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="pl" data-name="<?php echo bkntc__('Polish'); ?>">
                                        <span class="lang-flag">🇵🇱</span>
                                        <span class="lang-name"><?php echo bkntc__('Polish'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="pt" data-name="<?php echo bkntc__('Portuguese'); ?>">
                                        <span class="lang-flag">🇵🇹</span>
                                        <span class="lang-name"><?php echo bkntc__('Portuguese'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="ru" data-name="<?php echo bkntc__('Russian'); ?>">
                                        <span class="lang-flag">🇷🇺</span>
                                        <span class="lang-name"><?php echo bkntc__('Russian'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="es" data-name="<?php echo bkntc__('Spanish'); ?>">
                                        <span class="lang-flag">🇪🇸</span>
                                        <span class="lang-name"><?php echo bkntc__('Spanish'); ?></span>
                                    </div>
                                    <div class="language-item" data-lang="tr" data-name="<?php echo bkntc__('Turkish'); ?>">
                                        <span class="lang-flag">🇹🇷</span>
                                        <span class="lang-name"><?php echo bkntc__('Turkish'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="translation_status" class="translation-status" style="display: none;"></div>
                </div>
            </div>
            
            <form method="post" action="" id="topic_form">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Main content area -->
                        <div class="form-sidebar">

                        <div class="form-row">
                            <label class="form-label" for="title"><?php echo bkntc__('Topic Title')?>:</label>
                            <input type="text" name="title" id="title" class="form-input" placeholder="<?php echo bkntc__('Enter topic title')?>">                            
                        </div>
                        
                        <div class="form-row">
                            <label class="form-label" for="content"><?php echo bkntc__('Content')?>:</label>
                            <textarea name="content" id="content" rows="10"></textarea>
                        </div>
                    </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Sidebar -->
                        <div class="form-sidebar">
                            <div class="form-row">
                                <label class="form-label" for="category_id"><?php echo bkntc__('Category')?>:</label>
                                <select name="category_id" id="category_id" class="category-select">
                                    <option value=""><?php echo bkntc__('Select Category'); ?></option>
                                    <?php
                                    if ($categories) {
                                        foreach ($categories as $cat) {
                                            echo '<option value="' . esc_attr($cat->id) . '">' . esc_html($cat->name) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="0">' . bkntc__('No categories found') . '</option>';
                                    }
                                    ?>
                                </select>
                                <p class="help-text"><?php echo bkntc__('Choose where this topic should appear')?></p>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label"><?php echo bkntc__('Attachments')?>:</label>
                                <div class="file-upload">
                                    <input type="file" id="file_upload_input" name="file_attachments[]" multiple style="display: none;">
                                    <button type="button" id="upload_file_button" class="upload-btn"><?php echo bkntc__('Upload Files')?></button>
                                    <p class="file-info"><?php echo bkntc__('Max file size: 25MB')?></p>
                                </div>
                                <div id="file_attachment_list" class="attachment-list">
                                    <!-- Attachments will be listed here -->
                                </div>
                                <div id="upload_progress" class="upload-progress" style="display: none;">
                                    <div class="progress-bar"></div>
                                    <div class="progress-text">Uploading... 0%</div>
                                </div>
                                <input type="hidden" name="attachments" id="attachments_input" value="">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="<?php echo $list_url; ?>" class="secondary-button-help"><?php echo bkntc__('Cancel')?></a>
                    <button type="submit" class="primary-button-help"><?php echo bkntc__('Publish Topic')?></button>
                </div>
            </form>

    <!-- Load our custom JavaScript file and localization data -->
    <script>
    // Pass PHP localization data to JavaScript using the centralized function
    <?php 
    // Include the centralized localization file if not already included
    if (!function_exists('booknetic_help_output_localization')) {
        require_once dirname(__FILE__) . '/../includes/localization.php';
    }
    
    // Output the localization data
    booknetic_help_output_localization('booknetic_help_i18n', 'topics', [
        'upload_nonce' => isset($upload_nonce) ? $upload_nonce : wp_create_nonce('upload_attachment_nonce'),
        'no_content' => bkntc__('Please extract content first before translating')
    ]);
    ?>
    
    // Document URL extraction functionality
    jQuery(document).ready(function($) {
        // Handle content extraction
        $('#extract_content_button').on('click', function() {
            var documentationUrl = $('#documentation_url').val().trim();
            
            if (!documentationUrl) {
                $('#extraction_status')
                    .text(booknetic_help_i18n.invalid_url)
                    .removeClass('success')
                    .addClass('error')
                    .show();
                return;
            }
            
            // Show loading state
            $('#extraction_status')
                .text(booknetic_help_i18n.extracting)
                .removeClass('success error')
                .addClass('loading')
                .show();
            
            // Get the AJAX URL and nonce from the localization data
            const ajaxUrl = booknetic_help_i18n.ajax_url;
            const nonce = booknetic_help_i18n.upload_nonce;
            
            // Make AJAX request to extract content
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_extract_documentation',
                    _wpnonce: nonce,
                    url: documentationUrl
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#extract_content_button').prop('disabled', true);
                },
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Check status and handle success - support both WordPress standard response format
                        // and legacy Booknetic format
                        if (response.success || (response.status === true)) {
                            // Update form fields with extracted content
                            const data = response.data || response;
                            $('#title').val(data.title);
                            
                            // If using Summernote editor
                            if ($.fn.summernote) {
                                $('#content').summernote('code', data.content);
                            } else {
                                $('#content').val(data.content);
                            }
                            
                            // Show success message
                            $('#extraction_status')
                                .text(booknetic_help_i18n.extraction_success)
                                .removeClass('loading error')
                                .addClass('success')
                                .show();
                                
                            // Hide message after 3 seconds
                            setTimeout(function() {
                                $('#extraction_status').fadeOut();
                            }, 3000);
                            
                            // Show translation section
                            $('#translation_section').show();
                        } else {
                            // Handle error with message from server
                            const errorMsg = (response.data && response.data.error) || 
                                            response.error || 
                                            booknetic_help_i18n.extraction_error;
                            
                            $('#extraction_status')
                                .text(errorMsg)
                                .removeClass('loading success')
                                .addClass('error')
                                .show();
                        }
                    } catch (e) {
                        // Handle parsing errors
                        $('#extraction_status')
                            .text(booknetic_help_i18n.invalid_response)
                            .removeClass('loading success')
                            .addClass('error')
                            .show();
                    }
                },
                error: function() {
                    // Handle AJAX errors
                    $('#extraction_status')
                        .text(booknetic_help_i18n.server_error)
                        .removeClass('loading success')
                        .addClass('error')
                        .show();
                },
                complete: function() {
                    // Re-enable the button
                    $('#extract_content_button').prop('disabled', false);
                }
            });
        });
        
        // Modern Language Selector Functionality
        $('.language-btn').on('click', function() {
            const langCode = $(this).data('lang');
            const langName = $(this).data('name');
            
            if (langCode) {
                // Update hidden input and display
                $('#target_language').val(langCode);
                $('#selected_language_display').text(langName);
                
                // Update active state
                $('.language-btn').removeClass('active');
                $(this).addClass('active');
                
                // Enable translate button
                $('#translate_content_button').prop('disabled', false);
            }
        });
        
        // Show more languages modal
        $('#show_more_languages').on('click', function() {
            $('#language_modal').addClass('show');
        });
        
        // Close modal
        $('.close-modal').on('click', function() {
            $('#language_modal').removeClass('show');
        });
        
        // Close modal when clicking outside
        $(document).on('click', function(e) {
            if ($(e.target).hasClass('language-modal')) {
                $('#language_modal').removeClass('show');
            }
        });
        
        // Language search functionality
        $('#language_search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            $('.language-item').each(function() {
                const langName = $(this).find('.lang-name').text().toLowerCase();
                if (langName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Select language from modal
        $('.language-item').on('click', function() {
            const langCode = $(this).data('lang');
            const langName = $(this).data('name');
            
            // Update hidden input and display
            $('#target_language').val(langCode);
            $('#selected_language_display').text(langName);
            
            // Update active state in quick buttons
            $('.language-btn').removeClass('active');
            $(`.language-btn[data-lang="${langCode}"]`).addClass('active');
            
            // Close modal
            $('#language_modal').removeClass('show');
            
            // Enable translate button
            $('#translate_content_button').prop('disabled', false);
        });
        
        // Handle translation
        $('#translate_content_button').on('click', function() {
            var targetLanguage = $('#target_language').val().trim();
            var title = $('#title').val().trim();
            var content = '';
            
            // Get content from editor
            if ($.fn.summernote) {
                content = $('#content').summernote('code');
            } else {
                content = $('#content').val();
            }
            
            // Validate inputs
            if (!targetLanguage) {
                $('#translation_status')
                    .text(booknetic_help_i18n.select_language)
                    .removeClass('success loading')
                    .addClass('error')
                    .show();
                return;
            }
            
            if (!title || !content) {
                $('#translation_status')
                    .text(booknetic_help_i18n.no_content)
                    .removeClass('success loading')
                    .addClass('error')
                    .show();
                return;
            }
            
            // Show loading state with rotating messages
            $('#translation_status')
                .text(booknetic_help_i18n.translating)
                .removeClass('success error')
                .addClass('loading')
                .show();
                
            // Set up rotating loading messages with more variety and realism
            const loadingMessages = [
                booknetic_help_i18n.translating || 'Translating your content...',
                booknetic_help_i18n.translating_message1 || 'Analyzing language structure...',
                booknetic_help_i18n.translating_message2 || 'Processing semantic context...',
                booknetic_help_i18n.translating_message3 || 'Adapting cultural nuances...',
                booknetic_help_i18n.translating_message4 || 'Refining grammar and syntax...',
                booknetic_help_i18n.translating_message5 || 'Preserving document formatting...',
                booknetic_help_i18n.translating_message6 || 'Optimizing terminology...',
                booknetic_help_i18n.translating_message7 || 'Ensuring natural language flow...',
                booknetic_help_i18n.translating_message8 || 'Finalizing translation...',
                booknetic_help_i18n.translating_message9 || 'Almost there...',
            ];
            
            // Shuffle the array to avoid predictable sequence
            for (let i = loadingMessages.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [loadingMessages[i], loadingMessages[j]] = [loadingMessages[j], loadingMessages[i]];
            }
            
            // Make sure "Translating your content..." is first and "Almost there..." is last
            const firstMessage = booknetic_help_i18n.translating || 'Translating your content...';
            const lastMessage = booknetic_help_i18n.translating_message9 || 'Almost there...';
            
            // Remove these messages from array if they exist
            const filteredMessages = loadingMessages.filter(msg => 
                msg !== firstMessage && msg !== lastMessage
            );
            
            // Reconstruct array with first and last in proper positions
            const orderedMessages = [firstMessage, ...filteredMessages, lastMessage];
            
            let messageIndex = 0;
            const loadingMessageInterval = setInterval(function() {
                // Only advance if we're not at the end
                if (messageIndex < orderedMessages.length - 1) {
                    messageIndex++;
                    $('#translation_status').text(orderedMessages[messageIndex]);
                } else {
                    // At the last message, stay there until translation completes
                    $('#translation_status').text(orderedMessages[messageIndex]);
                }
            }, 2500);
            
            // Get the AJAX URL and nonce
            const ajaxUrl = booknetic_help_i18n.ajax_url;
            const nonce = booknetic_help_i18n.upload_nonce;
            
            // Make AJAX request to translate content
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_translate_topic_content',
                    _wpnonce: nonce,
                    title: title,
                    content: content,
                    target_language: targetLanguage
                },
                beforeSend: function() {
                    // Disable button during translation
                    $('#translate_content_button').prop('disabled', true);
                },
                success: function(response) {
                    // Clear the loading message interval
                    clearInterval(loadingMessageInterval);
                    
                    try {
                        // Parse response if it's a string
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Check status and handle success
                        if (response.success || (response.status === true)) {
                            // Update form fields with translated content
                            const data = response.data || response;
                            $('#title').val(data.title);
                            
                            // Update content in editor
                            if ($.fn.summernote) {
                                $('#content').summernote('code', data.content);
                            } else {
                                $('#content').val(data.content);
                            }
                            
                            // Show success message
                            $('#translation_status')
                                .text(booknetic_help_i18n.translation_success)
                                .removeClass('loading error')
                                .addClass('success')
                                .show();
                                
                            // Hide message after 3 seconds
                            setTimeout(function() {
                                $('#translation_status').fadeOut();
                            }, 3000);
                        } else {
                            // Handle error with message from server
                            const errorMsg = (response.data && response.data.error) || 
                                           response.error || 
                                           booknetic_help_i18n.translation_error;
                            
                            $('#translation_status')
                                .text(errorMsg)
                                .removeClass('loading success')
                                .addClass('error')
                                .show();
                        }
                    } catch (e) {
                        // Clear the interval in case of parsing error
                        clearInterval(loadingMessageInterval);
                        
                        // Handle parsing errors
                        $('#translation_status')
                            .text(booknetic_help_i18n.translation_error)
                            .removeClass('loading success')
                            .addClass('error')
                            .show();
                    }
                },
                error: function() {
                    // Clear the interval in case of AJAX error
                    clearInterval(loadingMessageInterval);
                    
                    // Handle AJAX errors
                    $('#translation_status')
                        .text(booknetic_help_i18n.translation_error)
                        .removeClass('loading success')
                        .addClass('error')
                        .show();
                },
                complete: function() {
                    // Re-enable button
                    $('#translate_content_button').prop('disabled', false);
                }
            });
        });
    });
    </script>
    <script src="<?php echo $js_url; ?>"></script>
    
    <style>
    .documentation-import-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }
    
    .documentation-import-section h5 {
        margin-top: 0;
        font-size: 18px;
        color: #333;
    }
    
    .import-description {
        margin-bottom: 15px;
        color: #6c757d;
    }
    
    .import-form-row {
        display: flex;
        gap: 10px;
    }
    
    .translation-section {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
        margin-top: 15px;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .language-select {
        flex: 1;
    }
    
    .translation-status,
    .extraction-status {
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .loading {
        background-color: #e9f5ff;
        color: #0d6efd;
    }
    
    .success {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    
    .error {
        background-color: #f8d7da;
        color: #842029;
    }
    
    /* Modern Language Selector Styles */
    .modern-language-selector {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .language-selection-area {
        flex: 1;
        margin-right: 15px;
    }
    
    .selected-language-display {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .selected-language-label {
        font-weight: 600;
        margin-right: 10px;
        color: #495057;
    }
    
    .selected-language-value {
        color: var(--primary-color);
        font-weight: 500;
    }
    
    .quick-language-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .language-btn {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        color: #495057;
    }
    
    .language-btn:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .language-btn.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .lang-flag {
        margin-right: 5px;
        font-size: 16px;
    }
    
    .more-langs {
        background-color: #e9ecef;
    }
    
    .translate-button {
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Language Modal Styles */
    .language-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .language-modal.show {
        display: flex;
        animation: fadeIn 0.3s ease;
    }
    
    .language-modal-content {
        background-color: white;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease;
    }
    
    .language-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .language-modal-header h5 {
        margin: 0;
        font-size: 18px;
        color: #212529;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #6c757d;
    }
    
    .language-modal-body {
        padding: 20px;
        max-height: calc(80vh - 70px);
        overflow-y: auto;
    }
    
    .language-search {
        position: relative;
        margin-bottom: 15px;
    }
    
    .language-search input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .language-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    
    .language-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .language-item:hover {
        background-color: #f8f9fa;
    }
    
    .language-item.selected {
        background-color: rgba(var(--primary-color-rgb), 0.1);
        border-left: 3px solid var(--primary-color);
    }
    
    .lang-name {
        margin-left: 8px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .translation-status {
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .translation-status.loading {
        background-color: #e9f5ff;
        color: #0d6efd;
    }
    
    .translation-status.success {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    
    .translation-status.error {
        background-color: #f8d7da;
        color: #842029;
    
        gap: 10px;
    }
    
    .documentation-url-input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    
    .extract-button {
        padding: 10px 15px;
        white-space: nowrap;
    }
    
    .extraction-status {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .extraction-status.loading {
        background-color: #e9ecef;
        color: #495057;
    }
    
    .extraction-status.success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .extraction-status.error {
        background-color: #f8d7da;
        color: #721c24;
    }
    </style>
    </div>
</div>
</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer
