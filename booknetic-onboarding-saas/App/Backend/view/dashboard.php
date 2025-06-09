<?php
ob_start();
defined('ABSPATH') or die();

if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}


use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;

// Include dashboard data preparation file
require_once dirname(__DIR__) . '/includes/dashboard-data.php';

// Get all dashboard data
$dashboard_data = get_dashboard_data();

// Extract data for easier access in the template
extract($dashboard_data);

$redirect_url = add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'dashboard'], 'admin.php');
$base_url = isset($_GET['page']) && $_GET['page'] === 'booknetic' ? "admin.php?page=booknetic&module=help-center" : "admin.php?page=booknetic-saas&module=help-center";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Pro Dashboard</title>
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/style-admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo ContactUsPAddon::loadAsset('assets/backend/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <?php // FontAwesome now loaded via AssetManager ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
                    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'dashboard'], 'admin.php')); ?>" 
                       class="button-group-item primary-button">
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

                    <a href="<?php echo esc_url($base_url); ?>" 
                       class="button-group-item secondary-button">
                       <i class="fas fa-arrow-left" style="margin-right:10px;"></i> <?php echo bkntc__('Back') ?>
                    </a>
                    
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="total-statistics stats-grid">
                <div class="total-statistics-card stat-card">
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_topics']) ?></h3>
                        <p><i class="far fa-file"></i> <?php echo bkntc__('Total Topics') ?></p>
                    </div>
                </div>

                <div class="total-statistics-card stat-card">
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_categories']) ?></h3>
                        <p><i class="far fa-folder"></i> <?php echo bkntc__('Categories') ?></p>
                    </div>
                </div>
                
                <div class="total-statistics-card stat-card">
                    <div class="stat-info">
                        <h3><?php echo number_format((float)($stats['total_views'] ?? 0)) ?></h3>
                        <p><i class="far fa-eye"></i> <?php echo bkntc__('Views') ?></p>
                    </div>
                </div>
                
                <div class="total-statistics-card stat-card">
                    <div class="stat-info">
                        <h3><?php echo $stats['satisfaction_rate'] ? number_format($stats['satisfaction_rate'] * 100, 1) : '0' ?>%</h3>
                        <p><i class="far fa-smile"></i> <?php echo bkntc__('Satisfaction Rate') ?></p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3><?php echo bkntc__('Topic Trends') ?></h3>
                    <canvas id="trendChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3><?php echo bkntc__('Category Distribution') ?></h3>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Most Helpful Articles -->
            <div class="charts-grid">
                <div class="helpful-card chart-card">
                    <h3><?php echo bkntc__('Most Helpful Topics') ?></h3>
                    <ul>
                        <?php foreach ($most_helpful as $topic): ?>
                        <li>
                            <a href="admin.php?page=booknetic-saas&module=help-center&topic=<?php echo esc_attr($topic->id); ?>" class="topic-title" target="_blank">
                                <?php echo esc_html($topic->title) ?>
                            </a>
                            <div class="feedback-stats">
                                <span class="satisfaction-rate"><?php echo number_format($topic->helpfulness_percentage, 1) ?>% helpful</span>
                                <span class="searchs-count"><?php echo $topic->total_feedback ?> feedback</span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            
                <!-- Popular Searches -->
                <div class="search-card chart-card">
                    <h3><?php echo bkntc__('Popular Searches') ?></h3>
                    <ul>
                        <?php foreach ($popular_searches as $search): ?>
                        <li>
                            <span class="search-term"><?php echo esc_html($search->search_term) ?></span>
                            <span class="search-count"><?php echo $search->search_count ?> searches</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>           
            </div>

            <!-- Main Content Grid -->
            <!-- Left Column -->
            <div class="main-content">
                <!-- Engagement Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><?php echo bkntc__('Content Engagement') ?></h3>
                    </div>
                    <canvas id="engagementChart"></canvas>
                </div>
              

              

       

                <!-- Top Content Section -->
                <div class="content-section">
                    <div class="top-content-grid">
                        <div class="top-list chart-card" style="border: 0;">
                            <h3><?php echo bkntc__('Most Viewed Topics') ?></h3>
                            <ul>
                                <?php foreach ($most_viewed as $topic): ?>
                                <li>
                                    <a href="admin.php?page=booknetic-saas&module=help-center&topic=<?php   echo esc_attr($topic->id); ?>" target="_blank">
                                        <span class="topic-title"><?php echo esc_html($topic->title) ?></span>
                                    </a>
                                    <span class="topic-views"><?php echo number_format($topic->views) ?> views</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="top-list chart-card" style="border: 0;">
                            <h3><?php echo bkntc__('Top Rated Topics') ?></h3>
                            <ul>
                                <?php foreach ($top_rated as $topic): ?>
                                <li>    
                                    <a href="admin.php?page=booknetic-saas&module=help-center&topic=<?php   echo esc_attr($topic->id); ?>" target="_blank">
                                        <span class="topic-title"><?php echo esc_html($topic->title) ?></span>
                                    </a>
                                    <div class="feedback-stats">
                                        <span class="satisfaction-rate"><?php echo number_format($topic->satisfaction_rate * 100, 1) ?>%</span>
                                        <span class="searchs-count"><?php echo $topic->total_feedback ?> feedback</span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Knowledge Base Health -->
            <div class="health-card chart-card">
                <h3><?php echo bkntc__('Knowledge Health') ?></h3>
                <div class="health-meter">
                    <div class="gauge" data-value="<?php echo number_format($stats['knowledge_health_score'], 1) ?>"></div>
                    <div class="health-stats">
                        <div>
                            <span><?php echo number_format($stats['knowledge_health_score'], 1) ?>%</span>
                            <small><?php echo bkntc__('Health Score') ?></small>
                        </div>
                        <div>
                            <span><?php echo number_format($stats['total_likes']) ?></span>
                            <small><?php echo bkntc__('Total Likes') ?></small>
                        </div>
                        <div>
                            <span><?php echo number_format($stats['total_dislikes']) ?></span>
                            <small><?php echo bkntc__('Total Dislikes') ?></small>
                        </div>
                        <div>
                            <span><?php echo number_format($stats['topics_with_feedback_percentage'], 1) ?>%</span>
                            <small><?php echo bkntc__('Topics with Feedback') ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Health Metrics -->
                <div class="health-details">
                    <div class="health-detail-row">
                        <div class="health-detail-item">
                            <div class="health-detail-label"><?php echo bkntc__('Content Coverage') ?></div>
                            <div class="health-detail-value">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(($stats['total_topics']/300)*100, 100) ?>%;"></div>
                                    <div class="target-indicator" title="<?php echo bkntc__('Target: 300 topics') ?>"></div>
                                </div>
                                <span><?php echo number_format(min(($stats['total_topics']/300)*100, 100), 1) ?>%</span>
                            </div>
                        </div>
                        <div class="health-detail-item">
                            <div class="health-detail-label"><?php echo bkntc__('Avg. Views per Topic') ?></div>
                            <div class="health-detail-value">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min($stats['avg_views_percentage'], 100) ?>%;"></div>
                                    <div class="target-indicator" title="<?php echo bkntc__('Target: 100 views per topic') ?>"></div>
                                </div>
                                <span><?php echo number_format($stats['avg_views_per_topic'], 1) ?> (<?php echo number_format(min($stats['avg_views_percentage'], 100), 1) ?>%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="health-detail-row">
                        <div class="health-detail-item">
                            <div class="health-detail-label"><?php echo bkntc__('Feedback Rate') ?></div>
                            <div class="health-detail-value">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(($stats['total_feedback'] / max($stats['total_topics'] * 5, 1)) * 100, 100) ?>%;"></div>
                                    <div class="target-indicator" title="<?php echo bkntc__('Target: 5 feedbacks per topic') ?>"></div>
                                </div>
                                <span><?php echo number_format(min(($stats['total_feedback'] / max($stats['total_topics'] * 5, 1)) * 100, 100), 1) ?>%</span>
                            </div>
                        </div>
                        <div class="health-detail-item">
                            <div class="health-detail-label"><?php echo bkntc__('Satisfaction Rate') ?></div>
                            <div class="health-detail-value">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($stats['satisfaction_rate'] * 100) ?>%;"></div>
                                    <div class="target-indicator" title="<?php echo bkntc__('Target: 100% satisfaction') ?>"></div>
                                </div>
                                <span><?php echo number_format($stats['satisfaction_rate'] * 100, 1) ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const dashboardData = {
            // Engagement chart data
            engagementTopicTitles: <?php echo json_encode(array_column($engagement_topics, 'title')); ?>,
            engagementTopicViews: <?php echo json_encode(array_column($engagement_topics, 'views')); ?>,
            engagementTopicPositiveFeedback: <?php echo json_encode(array_column($engagement_topics, 'positive_feedback')); ?>,
            engagementTopicNegativeFeedback: <?php echo json_encode(array_column($engagement_topics, 'negative_feedback')); ?>,
            
            // Status chart data
            statusLabels: <?php echo json_encode(array_column($status_distribution, 'status')); ?>,
            statusCounts: <?php echo json_encode(array_column($status_distribution, 'count')); ?>,
            
            // Trend chart data
            trendMonths: <?php echo json_encode(array_column($monthly_trends, 'month')); ?>,
            topicCounts: <?php echo json_encode(array_column($monthly_trends, 'topic_count')); ?>,
            positiveFeedback: <?php echo json_encode(array_column($monthly_trends, 'positive_feedback')); ?>,
            negativeFeedback: <?php echo json_encode(array_column($monthly_trends, 'negative_feedback')); ?>,
            totalViews: <?php echo json_encode(array_column($monthly_trends, 'total_views')); ?>,
            
            // Category chart data
            categoryNames: <?php echo json_encode(array_column($category_distribution, 'name')); ?>,
            categoryCounts: <?php echo json_encode(array_column($category_distribution, 'count')); ?>
        };
    </script>
    
    <!-- Include dashboard JavaScript -->
    <script src="<?php echo ContactUsPAddon::loadAsset('assets/backend/js/dashboard.js'); ?>"></script>

</body>
</html>
<?php ob_end_flush(); ?>