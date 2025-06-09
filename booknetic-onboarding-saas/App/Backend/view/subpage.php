<?php
defined('ABSPATH') or die();

use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

// Start output buffering to prevent headers already sent errors
ob_start();

global $wpdb;
$base_url = isset($_GET['page']) && $_GET['page'] === 'booknetic' && isset($_GET['module']) && $_GET['module'] === 'help-center' ? "admin.php?page=booknetic&module=help-center" : "admin.php?page=booknetic-saas&module=help-center";

// Include the data provider file to access helper functions
require_once dirname(__FILE__) . '/../includes/data-provider.php';

// Fetch the livechat content from the help_settings table
$title = get_help_setting('livechat_title', 'Live Chat Support');
$subtitle = get_help_setting('livechat_subtitle', 'We\'re here to help you');
$embed_code = get_help_setting('livechat_embed_code', '');
$icon = get_help_setting('livechat_icon', 'fas fa-comments');

// Additional settings for enhanced features
$chat_status = get_help_setting('livechat_status', 'online'); // online, offline, away
$chat_hours = get_help_setting('livechat_hours', 'Monday-Friday: 9AM-5PM EST');
$show_faq = get_help_setting('livechat_show_faq', '1');

// Get color settings from the same source as index.php
$color_settings = get_help_setting('color_settings', [
    'primary_color' => '#4050B5',
    'secondary_color' => '#6C757D'
]);

// Get FAQs if enabled
$faqs = array();
if ($show_faq == '1') {
    // Get top 5 FAQs from the database
    $faqs_query = "SELECT question, answer FROM {$wpdb->prefix}booknetic_help_faqs ORDER BY display_order LIMIT 5";
    $faqs = $wpdb->get_results($faqs_query, ARRAY_A);
    
    // If no FAQs found, provide some default ones
    if (empty($faqs)) {
        $faqs = array(
            array(
                'question' => 'How do I get started with Booknetic?',
                'answer' => 'You can begin by creating your first service and staff member, then set up your working hours.'
            ),
            array(
                'question' => 'How do I integrate payment gateways?',
                'answer' => 'Go to Settings > Payment Methods to configure your preferred payment gateways.'
            ),
            array(
                'question' => 'Can I customize email notifications?',
                'answer' => 'Yes, you can customize all notification templates in the Settings > Notifications section.'
            )
        );
    }
}

// Get current time to determine if chat is available based on business hours
$current_day = date('l');
$current_time = date('H:i');
$is_business_hours = true; // Default to true, would implement actual business hours check in production

// Define allowed HTML tags for the embed code
function get_livechat_allowed_html() {
    return array(
        'iframe' => array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allow'           => true,
            'allowfullscreen' => true,
            'class'           => true,
            'style'           => true,
        ),
        'script' => array(
            'src'             => true,
            'type'            => true,
            'async'           => true,
            'defer'           => true,
            'charset'         => true,
            'crossorigin'     => true,
            'integrity'       => true,
        ),
        'div'    => array(
            'id'              => true,
            'class'           => true,
            'style'           => true,
            'data-*'          => true,
        ),
        'p'      => array(
            'class'           => true,
            'style'           => true,
        ),
        'span'   => array(
            'class'           => true,
            'style'           => true,
        ),
        'a'      => array(
            'href'            => true,
            'target'          => true,
            'rel'             => true,
            'class'           => true,
            'style'           => true,
        ),
        'img'    => array(
            'src'             => true,
            'alt'             => true,
            'class'           => true,
            'style'           => true,
            'width'           => true,
            'height'          => true,
        ),
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo esc_attr($subtitle); ?>">
  <title><?php echo esc_html($title); ?></title>
  <!-- Font Awesome now loaded via AssetManager -->
  <?php // FontAwesome now loaded via AssetManager ?>
  <!-- Bootstrap 5 for responsive design -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Animate.css for animations -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Plugin CSS -->
  
  <!-- Custom CSS from settings -->
  <style>
    <?php echo get_help_setting('custom_css', ''); ?>
  </style>
  
  <style>
    :root {
      <?php
      // Output color variables
      echo "--primary-color: " . esc_attr($color_settings['primary_color']) . ";\n";
      echo "--primary-color-hover: " . esc_attr($color_settings['primary_color']) . "cc;\n"; // Add transparency for hover
      echo "--primary-color-back: " . esc_attr($color_settings['primary_color']) . "33;\n"; // increased transparency
      echo "--secondary-color: " . esc_attr($color_settings['secondary_color']) . ";\n";
      echo "--secondary-color-hover: " . esc_attr($color_settings['secondary_color']) . "cc;\n"; // Add transparency for hover
      ?>
      --status-online: #28a745;
      --status-offline: #dc3545;
      --status-away: #ffc107;
      --alert-warning-bg: #fff3cd;
      --alert-warning-text: #856404;
      --alert-warning-border: #ffeeba;
      --alert-info-bg: #d1ecf1;
      --alert-info-text: #0c5460;
      --alert-info-border: #bee5eb;
      --text-color: #333;
      --text-light: #666;
      --bg-light: #f8f9fa;
      --bg-dark: #343a40;
      --gray-100: #f8f9fa;
      --gray-200: #e9ecef;
      --gray-300: #dee2e6;
      --gray-400: #ced4da;
      --gray-500: #adb5bd;
      --gray-600: var(--secondary-color);
      --gray-700: #495057;
      --gray-800: #343a40;
      --gray-900: #212529;
      --border-radius: 12px;
      --box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-color);
      line-height: 1.6;
    }
    
    .live-chat-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .live-chat-header {
      text-align: center;
      margin-bottom: 30px;
      padding: 20px 0;
    }
    
    .live-chat-header i {
      font-size: 48px;
      color: var(--primary-color);
      margin-bottom: 15px;
      display: block;
      transition: var(--transition);
    }
    
    .live-chat-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: var(--text-color);
      font-weight: 600;
    }
    
    .live-chat-header p {
      font-size: 1.2rem;
      color: var(--text-light);
      margin-bottom: 20px;
    }
    
    .live-chat-content {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 30px;
      min-height: 400px;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }
    
    .chat-status {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .status-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 8px;
    }
    
    .status-online {
      background-color: var(--status-online);
      box-shadow: 0 0 10px var(--status-online);
    }
    
    .status-offline {
      background-color: var(--status-offline);
      box-shadow: 0 0 10px var(--status-offline);
    }
    
    .status-away {
      background-color: var(--status-away);
      box-shadow: 0 0 10px var(--status-away);
    }
    
    .chat-hours {
      font-size: 0.85rem;
      color: var(--text-light);
      margin-top: 5px;
      font-style: italic;
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      margin-top: 20px;
      color: var(--primary-color);
      text-decoration: none !important;
      font-weight: 500;
      transition: var(--transition);
      padding: 8px 16px;
      border-radius: 30px;
      background-color: var(--primary-color-back);
    }
    
    .back-link:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-2px);
    }
    
    .back-link i {
      margin-right: 8px;
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: var(--border-radius);
      display: flex;
      align-items: center;
    }
    
    .alert i {
      font-size: 24px;
      margin-right: 15px;
    }
    
    .alert-warning {
      color: var(--alert-warning-text);
      background-color: var(--alert-warning-bg);
      border-color: var(--alert-warning-border);
    }
    
    .alert-info {
      color: var(--alert-info-text);
      background-color: var(--alert-info-bg);
      border-color: var(--alert-info-border);
    }
    
    /* Loading animation */
    .loading-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100px;
    }
    
    .loading-spinner {
      width: 40px;
      height: 40px;
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top-color: var(--primary-color);
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* FAQ Section */
    .faq-section {
      margin-top: 40px;
      border-top: 1px solid var(--gray-300, #eee);
      padding-top: 30px;
    }
    
    .faq-section h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: var(--text-color);
      text-align: center;
    }
    
    .faq-item {
      margin-bottom: 15px;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      transition: var(--transition);
    }
    
    .faq-question {
      background-color: var(--bg-light);
      padding: 15px 20px;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: var(--transition);
    }
    
    .faq-question:hover {
      background-color: var(--primary-color-back);
      color: var(--primary-color);
    }
    
    .faq-answer {
      padding: 0 20px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      background-color: white;
    }
    
    .faq-answer.active {
      padding: 15px 20px;
      max-height: 500px;
    }
    
    /* Contact Form */
    .contact-form {
      margin-top: 30px;
      display: none;
    }
    
    .contact-form.show {
      display: block;
      animation: fadeIn 0.5s;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--gray-300);
      border-radius: var(--border-radius);
      font-family: inherit;
      font-size: 1rem;
      transition: var(--transition);
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px var(--primary-color-back);
    }
    
    .btn {
      display: inline-block;
      padding: 12px 24px;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 500;
      transition: var(--transition);
      text-align: center;
    }
    
    .btn:hover {
      background-color: var(--primary-color-hover);
      transform: translateY(-2px);
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
      .live-chat-header h1 {
        font-size: 2rem;
      }
      
      .live-chat-content {
        padding: 20px;
      }
      
      .chat-status {
        position: static;
        margin-bottom: 15px;
        justify-content: center;
      }
    }
    
    @media (max-width: 576px) {
      .live-chat-header h1 {
        font-size: 1.8rem;
      }
      
      .live-chat-header i {
        font-size: 36px;
      }
      
      .live-chat-content {
        padding: 15px;
      }
    }
  </style>
  <!-- Allow for any scripts in the head section -->
  <?php 
  // Extract and output any script tags that should be in the head
  if (!empty($embed_code) && preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $embed_code, $matches)) {
      foreach ($matches[0] as $script) {
          // Check if it's a tracking/initialization script (usually small and without src)
          if (strpos($script, 'src=') === false && strlen($script) < 1000) {
              echo wp_kses($script, get_livechat_allowed_html());
              // Remove this script from the embed code so it's not duplicated in the body
              $embed_code = str_replace($script, '', $embed_code);
          }
      }
  }
  ?>
</head>
<body>
  <div class="live-chat-container">
    <div class="live-chat-content animate__animated animate__fadeIn">
      <div class="live-chat-header">
        <i class="<?php echo esc_attr($icon); ?> animate__animated animate__pulse animate__infinite"></i>
        <h1><?php echo esc_html($title); ?></h1>
        <p><?php echo esc_html($subtitle); ?></p>
      </div>
      
      <!-- Live Chat Embed or Fallback -->
      <div id="chat-container">
        <?php 
        if (empty($embed_code)) {
            echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No live chat embed code has been configured. Please set up the live chat in the admin panel.</div>';
        } else {
            // Show loading animation
            echo '<div class="loading-container" id="chat-loading">';
            echo '<div class="loading-spinner"></div>';
            echo '</div>';
            
            // Output the embed code
            echo '<div id="chat-embed" style="display: none;">';
            echo wp_kses($embed_code, get_livechat_allowed_html());
            echo '</div>';
        }
        ?>
      </div>

    
    
    <a class="back-link animate__animated animate__fadeIn animate__delay-1s" href="<?php echo esc_url($base_url); ?>">
      <i class="fas fa-arrow-left"></i> <?php echo bkntc__('Back to Help Center'); ?>
    </a>
  </div>
  
  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JavaScript -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Show chat embed after loading
    setTimeout(function() {
      const loadingElement = document.getElementById('chat-loading');
      const chatEmbed = document.getElementById('chat-embed');
      
      if (loadingElement) {
        loadingElement.style.display = 'none';
      }
      
      if (chatEmbed) {
        chatEmbed.style.display = 'block';
      }
    }, 1500);
    
    // FAQ accordion functionality
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
      question.addEventListener('click', () => {
        const answer = question.nextElementSibling;
        const icon = question.querySelector('i');
        
        // Toggle active class
        answer.classList.toggle('active');
        
        // Toggle icon
        if (answer.classList.contains('active')) {
          icon.classList.remove('fa-chevron-down');
          icon.classList.add('fa-chevron-up');
        } else {
          icon.classList.remove('fa-chevron-up');
          icon.classList.add('fa-chevron-down');
        }
      });
    });
    
    // Handle contact form submission
    const contactForm = document.getElementById('offline-contact-form');
    if (contactForm) {
      contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Here you would normally send the form data to the server
        // For demonstration, we'll just show a success message
        contactForm.innerHTML = '<div class="alert alert-info"><i class="fas fa-check-circle"></i> Thank you for your message! We\'ll get back to you as soon as possible.</div>';
      });
    }
  });
  </script>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>
