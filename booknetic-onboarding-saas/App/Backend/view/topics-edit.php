<?php
ob_start(); // Start output buffering.
defined('ABSPATH') or die();

// Only administrators should access this page.
if ( ! current_user_can('manage_options') ) {
    wp_die('You do not have sufficient permissions to access this page.');
}

use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

require_once dirname(__FILE__) . '/../includes/manage-topics.php';

// First get the topic data for editing
$topic_data = get_topic_data_for_edit();

// Extract variables from the topic data array
$topic_id = $topic_data['topic_id'];
$topicData = $topic_data['topicData'];
$categories = $topic_data['categories'];
$attachments = $topic_data['attachments'];

// Then process any form submission
$data = process_help_topic_submission();

// Extract variables from the data array
extract($data);

// Create a nonce for file uploads
$upload_nonce = wp_create_nonce('upload_attachment_nonce');

// We can't use wp_enqueue_script in the middle of a page, so we'll include scripts directly
$plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));
$js_url = $plugin_url . 'assets/backend/js/manage-topics.js';

// Include the centralized localization file
require_once dirname(__FILE__) . '/../includes/localization.php';

// Get localization data for topics
$localization_data = get_booknetic_help_localization('topics', [
    'upload_nonce' => $upload_nonce // Add the upload nonce specific to this page
]);

// Set up the list URL for the cancel button
$list_url = add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Topic</title>
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
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/translation.css'); ?>">
    
    <!-- Plugin JS -->    
    <script type="text/javascript" src="<?php echo ContactUsPAddon::loadAsset('assets/backend/js/index.js'); ?>"></script>
    
    <!-- Icons -->    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <?php // FontAwesome now loaded via AssetManager ?>
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
    <a href="#" 
       class="button-group-item primary-button" style="height: 40px; margin-bottom: 20px;">
       <i class="fas fa-pen" style="margin-right:10px;"></i> <?php echo bkntc__('Editing topic') ?> - <?php echo esc_attr($topicData->title); ?>
    </a>
 <!-- Search Bar -->
    <form method="get" action="">
      <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
      <input type="hidden" name="module" value="help-center">
      <input type="hidden" name="view" value="topics">
     <input class="button-group-item secondary-button for-hover" style="height: 40px;" type="text" name="search" placeholder="<?php echo bkntc__('Search topics...') ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
    </form>
</div>
           
            <?php if ( isset($error) ) : ?>
                <p style="color: red;"><?php echo esc_html($error); ?></p>
            <?php endif; ?>
            
            <!-- Translation Section -->            
            <div class="translation-section" style="margin-bottom: 20px;">
                <h5><?php echo bkntc__('Translate Content'); ?> (<?php echo bkntc__('Optional'); ?>)</h5>
                <p class="import-description"><?php echo bkntc__('Select a language to translate the content.'); ?></p>
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

            <form method="post" action="">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Main content area -->
                        <div class="form-sidebar">
                            <div class="form-row">
                                <label class="form-label" for="title"><?php echo bkntc__('Topic Title')?>:</label>
                                <input type="text" name="title" id="title" class="form-input" value="<?php echo esc_attr($topicData->title); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label" for="content"><?php echo bkntc__('Content')?>:</label>
                                <textarea name="content" id="content" rows="10" required><?php echo esc_textarea($topicData->content); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Sidebar -->
                        <div class="form-sidebar">
                            <div class="form-row">
                                <label class="form-label" for="category_id"><?php echo bkntc__('Category')?>:</label>
                                <select name="category_id" id="category_id" class="category-select" required>
                                    <?php
                                    if ($categories) {
                                        foreach ($categories as $cat) {
                                            $selected = ($cat->id == $topicData->category_id) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($cat->id) . '" ' . $selected . '>' . esc_html($cat->name) . '</option>';
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
                                <div id="file_attachment_list" class="attachment-list" <?php echo !empty($attachments) ? '' : 'style="display: none;"'; ?>>
                                    <?php if (!empty($attachments)) : ?>
                                        <?php foreach ($attachments as $attachment) : ?>
                                            <div class="attachment-item" data-id="<?php echo esc_attr($attachment->id); ?>">
                                                <?php if (strpos($attachment->file_type, 'image/') === 0) : ?>
                                                    <img src="<?php echo esc_url($attachment->file_url); ?>" class="attachment-thumbnail">
                                                <?php else : ?>
                                                    <i class="<?php echo esc_attr(get_file_icon_class(pathinfo($attachment->file_name, PATHINFO_EXTENSION))); ?> attachment-icon"></i>
                                                <?php endif; ?>
                                                <div class="attachment-info">
                                                    <span class="attachment-name"><?php echo esc_html($attachment->file_name); ?></span>
                                                    <span class="attachment-size"><?php echo esc_html(format_file_size($attachment->file_size)); ?></span>
                                                    <div class="attachment-url">
                                                        <a href="<?php echo esc_url($attachment->file_url); ?>" target="_blank" class="view-file-btn">View File</a>
                                                        <button type="button" class="copy-link-btn" data-url="<?php echo esc_url($attachment->file_url); ?>">Copy Link</button>
                                                    </div>
                                                </div>
                                                <span class="attachment-remove" data-id="<?php echo esc_attr($attachment->id); ?>">×</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div id="upload_progress" class="upload-progress" style="display: none;">
                                    <div class="progress-bar"></div>
                                    <div class="progress-text">Uploading... 0%</div>
                                </div>
                                <input type="hidden" name="attachments" id="attachments_input" value="<?php 
                                    if (!empty($attachments)) {
                                        echo esc_attr(implode(',', array_map(function($a) { return $a->id; }, $attachments)));
                                    }
                                ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="<?php echo esc_url($list_url); ?>" class="secondary-button-help"><?php echo bkntc__('Cancel')?></a>
                    <button type="submit" class="primary-button-help"><?php echo bkntc__('Update Topic')?></button>
                </div>
            </form>

            <!-- Load our custom JavaScript file and localization data -->
            <script>
            // Pass PHP localization data to JavaScript
            <?php booknetic_help_output_localization('booknetic_help_i18n', 'topics', ['upload_nonce' => $upload_nonce]); ?>
            
            // Document ready function
            jQuery(document).ready(function($) {
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
                            .html('<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>' + booknetic_help_i18n.select_language)
                            .removeClass('success loading')
                            .addClass('error')
                            .show();
                        return;
                    }
                    
                    if (!title || !content) {
                        $('#translation_status')
                            .html('<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>' + booknetic_help_i18n.no_content)
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
                                        .html('<i class="fas fa-check-circle" style="margin-right: 8px;"></i>' + booknetic_help_i18n.translation_success)
                                        .removeClass('loading error')
                                        .addClass('success')
                                        .show();
                                        
                                    // Hide success message after 5 seconds
                                    setTimeout(function() {
                                        $('#translation_status').fadeOut();
                                    }, 5000);
                                } else {
                                    // Show error message with more specific details
                                    let errorMsg = booknetic_help_i18n.translation_error;
                                    
                                    // Check for specific error types
                                    if (response.error) {
                                        errorMsg = response.error;
                                    } else if (response.data && response.data.error) {
                                        // Extract error from data object if present
                                        errorMsg = response.data.error;
                                    }
                                    
                                    // Check for specific error keywords and provide more helpful messages
                                    if (errorMsg.includes('API key') || errorMsg.includes('ChatGPT API key is not configured')) {
                                        errorMsg = booknetic_help_i18n.api_key_missing;
                                        $('#translation_status').addClass('api-error');
                                    } else if (errorMsg.includes('rate limit') || errorMsg.includes('quota')) {
                                        errorMsg = booknetic_help_i18n.rate_limit_error;
                                    } else if (errorMsg.includes('too long') || errorMsg.includes('max tokens')) {
                                        errorMsg = booknetic_help_i18n.content_too_long;
                                    }
                                    
                                    // Display the error message
                                    $('#translation_status')
                                        .html('<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>' + errorMsg)
                                        .removeClass('loading success')
                                        .addClass('error')
                                        .show();
                                }
                            } catch (e) {
                                // Handle parsing errors with more details
                                console.error('Translation parsing error:', e);
                                $('#translation_status')
                                    .html('<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>' + booknetic_help_i18n.invalid_response)
                                    .removeClass('loading success')
                                    .addClass('error')
                                    .show();
                            }
                        },
                        error: function() {
                            // Clear the loading message interval
                            clearInterval(loadingMessageInterval);
                            
                            // Show network error message with more details
                            let errorMsg = booknetic_help_i18n.server_error;
                            
                            // Check if it's a network connectivity issue
                            if (!navigator.onLine) {
                                errorMsg = booknetic_help_i18n.network_error;
                                $('#translation_status').addClass('network-error');
                            }
                            
                            $('#translation_status')
                                .html('<i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>' + errorMsg)
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
        </div>
    </div>
</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer.
?>