<?php
defined('ABSPATH') or die();

use BookneticApp\Providers\DB\DB;
use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

// Include necessary files
require_once dirname(__FILE__) . '/../includes/data-provider.php';
require_once dirname(__FILE__) . '/../includes/feedback-handler.php';
require_once dirname(__FILE__) . '/../includes/template-functions.php';
require_once dirname(__FILE__) . '/../includes/routing.php';
require_once dirname(__FILE__) . '/../includes/data-preparation.php';
require_once dirname(__FILE__) . '/../includes/ajax-handlers.php';
require_once dirname(__FILE__) . '/../includes/manage-topics.php';
require_once dirname(__FILE__) . '/../includes/localization.php';

// Handle routing - if a route is matched, the script will exit
if (handle_help_center_routing()) {
    return;
}

// Prepare data for the view
$data = prepare_help_center_data();



// Extract variables from the data array
extract($data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo bkntc__('Help Center'); ?></title>
  <?php // FontAwesome now loaded via AssetManager ?>
  <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/style.css'); ?>">
  <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/index.css'); ?>">
  <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/help-center.css'); ?>">
  
  <!-- Custom CSS from settings -->
  <style>
    /* Apply color settings from database */
    :root {
      <?php
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
      
      // Convert hex to RGB for gradient effects
      $hex = ltrim($color_settings['primary_color'], '#');
      if (strlen($hex) == 3) {
          $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
          $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
          $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
      } else {
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
      }
      echo "--primary-color-rgb: " . $r . "," . $g . "," . $b . ";\n";
      ?>
    }
    
    
    
    /* Original custom CSS from settings */
    <?php echo get_help_setting('custom_css', ''); ?>
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Pass WordPress nonce and AJAX URL to JavaScript
    var helpCenterAjax = {
      ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
      nonce: '<?php echo wp_create_nonce('help_center_nonce'); ?>'
    };
  </script>
  <script src="<?php echo ContactUsPAddon::loadAsset('assets/backend/js/help-center.js'); ?>"></script>
</head>
<body>
  <div class="container">
    <!-- Menu Links Header Section -->
    <div class="menu-links-header">
      <!-- Subtle animated background particles -->
      <div class="menu-particles">
        <?php 
        // Generate a few subtle particles for the menu background
        $menu_particles = 5; // Number of particles
        for ($i = 0; $i < $menu_particles; $i++) {
          $size = rand(3, 8); // Smaller size for subtlety
          $top = rand(10, 90); // Random top position
          $left = rand(5, 95); // Random left position
          $duration = rand(15, 25); // Slower animation
          $delay = rand(0, 5); // Random animation delay
          echo "<span class=\"menu-particle\" style=\"width: {$size}px; height: {$size}px; top: {$top}%; left: {$left}%; animation-duration: {$duration}s; animation-delay: {$delay}s;\"></span>";
        }
        ?>
      </div>
      
      <div class="menu-links-container">
        <?php
        // Display menu links using our function from manage-topics.php
        echo display_menu_links();
        ?>
      </div>
    </div>

    <div class="hero">
      <!-- Floating particles for animation -->
      <div class="particles">
        <?php 
        // Generate random star-shaped particles
        $particles = 77; // Increased number of particles for better effect
        $colors = [
          'rgba(255, 255, 255, 0.4)', // White
          'rgba(255, 255, 255, 0.3)', // Lighter white
          'rgba(255, 255, 255, 0.2)', // Faint white
          'rgba(255, 255, 255, 0.5)', // Brighter white
          'rgba(255, 255, 255, 0.25)' // Very faint white
        ];
        
        for ($i = 0; $i < $particles; $i++) {
          $size = rand(4, 12); // Slightly larger size range for stars
          $top = rand(5, 95); // Random top position
          $left = rand(5, 95); // Random left position
          $duration = rand(10, 20); // Slower animation for more elegant movement
          $delay = rand(0, 1); // Random animation delay
          $color = $colors[array_rand($colors)]; // Random color from the array
          $rotation = rand(45, 180); // Initial rotation for variety
          
          echo "<span class=\"particle\" style=\"width: {$size}px; height: {$size}px; top: {$top}%; left: {$left}%; animation-duration: {$duration}s; animation-delay: {$delay}s; background-color: {$color}; transform: rotate({$rotation}deg);\"></span>";
        }
        ?>
      </div>
      
      <h1><?php echo bkntc__('How can we help?')?></h1>
      <!-- Enhanced Search Form with Autocomplete -->
      <form id="helpCenterSearchForm" method="get" action="admin.php">
        <input type="hidden" name="page" value="<?php echo isset($_GET['page']) ? esc_attr($_GET['page']) : 'booknetic-saas'; ?>">
        <input type="hidden" name="module" value="<?php echo isset($_GET['module']) ? esc_attr($_GET['module']) : 'help-center'; ?>">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('help_center_nonce'); ?>">
        <div class="search-container">
          <div class="search-box">
            <input type="text" 
                  id="helpSearchInput"
                  name="search" 
                  placeholder="<?php echo bkntc__('Search for help topics...')?>" 
                  value="<?php echo esc_attr($search_term); ?>" 
                  autocomplete="off"
                  data-min-chars="2"
                  data-suggestions-url="<?php echo admin_url('admin-ajax.php'); ?>">
            <button class="secondary-button-help" type="submit">
              <i class="fas fa-search"></i> 
            </button>
          </div>
          <div class="autocomplete-suggestions" style="display: none;">
            <div class="search-results-header">
              <h4><?php echo bkntc__('Suggestions'); ?></h4>
              <span class="close-suggestions">&times;</span>
            </div>
            <div class="search-results-container">
              <!-- Dynamic search results will be inserted here via JavaScript -->
              <div class="loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> <?php echo bkntc__('Searching...'); ?>
              </div>
              <div class="no-results" style="display: none;">
                <?php echo bkntc__('No results found.'); ?>
              </div>
              <div class="error" style="display: none;"></div>
            </div>
            <!-- The footer with "View all results" button will be added dynamically -->
          </div>
        </div>
      </form>

      <?php if (is_feature_enabled('popular_topics', true) && !empty($popular_topics)): ?>
      <!-- Most Popular Topics Section -->
      <div class="popular-topics-container">
        <div class="popular-topics-header">
          <span><i class="fas fa-bolt"></i> <?php echo bkntc__('Popular Topics'); ?></span>
        </div>
        <div class="popular-topics-list">
          <?php foreach ($popular_topics as $topic): ?>
            <a href="<?php echo esc_url(add_query_arg('topic', $topic->id, $base_url)); ?>" class="popular-topic-link">
              <?php echo esc_html($topic->title); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($search_term): ?>
        <?php render_search_results($topics, $search_term, $base_url); ?>

    <?php elseif ($topic_details): ?>
        <!-- Topic Detail View -->
        <?php render_topic_details($topic_details, $base_url, $error_message); ?>
       

    <?php elseif ($category && isset($categories[$category])): ?>
        <!-- Topics List View for Selected Category -->
        <?php render_category_topics($categories[$category], $topics, $base_url); ?>

       
    <?php else: ?>
        <!-- Categories View -->
        <?php render_categories_grid($categories, $livechat, $base_url); ?>
    <?php endif; ?>
    
  </div>

  <!-- Footer -->
  <?php render_footer(); ?>

</div>

<script type="text/javascript">
  // Localize the AJAX URL and nonce for use in JavaScript
  var helpCenterAjax = {
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('help_center_nonce'); ?>'
  };
  
  // Output localization data for JavaScript
  var helpCenterI18n = <?php echo json_encode(get_booknetic_help_localization('dashboard')); ?>;
  
  
  // Hero section animation enhancements
  document.addEventListener('DOMContentLoaded', function() {
    // Parallax effect for hero section
    const hero = document.querySelector('.hero');
    
    if (hero) {
      // Subtle parallax effect on mouse move
      document.addEventListener('mousemove', function(e) {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        
        // Apply subtle transform to create depth effect
        hero.style.backgroundPosition = `${x * 10}px ${y * 10}px`;
        
        // Move particles slightly based on mouse position
        const particles = document.querySelectorAll('.particle');
        particles.forEach(function(particle) {
          const speed = parseFloat(particle.getAttribute('data-speed') || Math.random() * 2);
          const offsetX = (x - 0.5) * speed * 20;
          const offsetY = (y - 0.5) * speed * 20;
          
          particle.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
        });
      });
      
      // Add data-speed attribute to particles for varying movement speeds
      const particles = document.querySelectorAll('.particle');
      particles.forEach(function(particle) {
        particle.setAttribute('data-speed', Math.random() * 2);
      });
    }
    
    // View switching functionality (grid/list view)
    const viewSwitches = document.querySelectorAll('.view-switch');
    const categoriesList = document.querySelector('.categories-list');
    
    if (viewSwitches.length && categoriesList) {
      // Get saved view preference from localStorage or default to grid
      const savedView = localStorage.getItem('helpCenterViewMode') || 'grid';
      
      // Apply saved view on page load
      categoriesList.className = 'categories-list ' + savedView + '-view';
      
      // Set active class on the correct button
      viewSwitches.forEach(function(button) {
        if (button.getAttribute('data-view') === savedView) {
          button.classList.add('active');
        } else {
          button.classList.remove('active');
        }
      });
      
      // Add click event listeners to view switches
      viewSwitches.forEach(function(button) {
        button.addEventListener('click', function() {
          const viewMode = this.getAttribute('data-view');
          
          // Skip if clicking on control panel button
          if (this.classList.contains('control-panel')) {
            return;
          }
          
          // Update view mode
          categoriesList.className = 'categories-list ' + viewMode + '-view';
          
          // Save preference to localStorage
          localStorage.setItem('helpCenterViewMode', viewMode);
          
          // Update active button
          viewSwitches.forEach(function(btn) {
            if (btn.getAttribute('data-view') === viewMode) {
              btn.classList.add('active');
            } else {
              btn.classList.remove('active');
            }
          });
        });
      });
      
      // Add animation to category cards
      const categoryCards = document.querySelectorAll('.category-card');
      categoryCards.forEach(function(card, index) {
        card.style.animationDelay = (0.1 * index) + 's';
      });
    }
  });
</script>

<!-- Search Box Animation Script -->
<script type="text/javascript">
  jQuery(document).ready(function($) {
    // Configuration
    const INACTIVITY_TIMEOUT = 6000; // 6 seconds of inactivity before animation starts
    const ANIMATION_DURATION = 12000; // Animation will run for 12 seconds
    
    // Variables to track state
    let inactivityTimer;
    let animationTimer;
    let $searchBox = $('.search-box');
    let $searchInput = $('#helpSearchInput');
    
    if (!$searchBox.length || !$searchInput.length) {
      return; // Exit if elements don't exist
    }
    
    // Set up event listeners for user activity
    $searchInput.on('focus click keydown', handleUserActivity);
    $(document).on('mousemove scroll click keydown', handleUserActivity);
    
    // Start the inactivity timer
    resetInactivityTimer();
    
    /**
     * Handle user activity events
     */
    function handleUserActivity() {
      // Remove animation class if it's active
      if ($searchBox.hasClass('attention')) {
        $searchBox.removeClass('attention');
        clearTimeout(animationTimer);
      }
      
      // Reset the inactivity timer
      resetInactivityTimer();
    }
    
    /**
     * Reset the inactivity timer
     */
    function resetInactivityTimer() {
      clearTimeout(inactivityTimer);
      
      // Set a new timer
      inactivityTimer = setTimeout(function() {
        // Only animate if user hasn't interacted with the search box
        if (!$searchInput.is(':focus') && !$searchBox.hasClass('attention')) {
          startSearchAnimation();
        }
      }, INACTIVITY_TIMEOUT);
    }
    
    /**
     * Start the search box animation
     */
    function startSearchAnimation() {
      // Add animation class
      $searchBox.addClass('attention');
      
      // Set timer to remove animation after duration
      animationTimer = setTimeout(function() {
        $searchBox.removeClass('attention');
      }, ANIMATION_DURATION);
    }
  });
</script>

</body>
</html>
