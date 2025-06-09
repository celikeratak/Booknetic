<?php
ob_start(); // Start output buffering immediately.
defined('ABSPATH') or die();

// Only administrators should access this page.
if ( ! current_user_can('manage_options') ) {
    wp_die('You do not have sufficient permissions to access this page.');
}

global $wpdb;

use function BookneticAddon\ContactUsP\bkntc__;

// Determine the base URL based on the incoming GET parameters.
$base_url = ( isset($_GET['page']) && $_GET['page'] === 'booknetic' && isset($_GET['module']) && $_GET['module'] === 'help-center' )
    ? "admin.php?page=booknetic&module=help-center"
    : "admin.php?page=booknetic-saas&module=help-center";

// Build the redirect URL (for after deletion or form submission).
$redirect_url = admin_url( add_query_arg( [ 'view' => 'categories' ], $base_url ) );

// Process deletion if requested via the "deletecategory" parameter.
$deletecategory = isset($_GET['deletecategory']) ? intval($_GET['deletecategory']) : 0;
if ( $deletecategory > 0 ) {

    
    // Attempt deletion from the table.
    $deleted = $wpdb->delete( "{$wpdb->prefix}bkntc_help_categories", [ 'id' => $deletecategory ], [ '%d' ] );
    
    if ( $deleted === false ) {
    } elseif ( $deleted == 0 ) {
        // No row was deleted. Possibly the ID doesn't exist.
    } else {
    }
    
    // Redirect after deletion with action status
    $redirect_with_status = add_query_arg(['action_status' => 'deleted'], $redirect_url);
    if ( ! headers_sent() ) {
        wp_redirect( $redirect_with_status );
        exit;
    } else {
        echo '<script type="text/javascript">window.location.href="' . html_entity_decode( esc_url( $redirect_with_status ) ) . '";</script>';
        exit;
    }
}

// Handle form submission for adding/editing categories.
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['name'] ) && isset( $_POST['description'] ) ) {
    check_admin_referer( 'manage_category' );
    
    $category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
    $name        = sanitize_text_field( $_POST['name'] );
    $description = sanitize_textarea_field( $_POST['description'] );
    $icon        = sanitize_text_field( $_POST['icon'] );
    
    if ( $category_id > 0 ) {
        // Update existing category.
        $wpdb->update(
            "{$wpdb->prefix}bkntc_help_categories",
            [ 'name' => $name, 'description' => $description, 'icon' => $icon ],
            [ 'id'   => $category_id ],
            [ '%s', '%s', '%s' ],
            [ '%d' ]
        );
    } else {
        // Insert new category.
        $wpdb->insert(
            "{$wpdb->prefix}bkntc_help_categories",
            [ 'name' => $name, 'description' => $description, 'icon' => $icon ],
            [ '%s', '%s', '%s' ]
        );
    }
    
    // Redirect after processing the form with action status
    $action_status = $category_id > 0 ? 'updated' : 'added';
    $redirect_with_status = add_query_arg(['action_status' => $action_status], $redirect_url);
    if ( ! headers_sent() ) {
        wp_redirect( $redirect_with_status );
        exit;
    } else {
        echo '<script type="text/javascript">window.location.href="' . html_entity_decode( esc_url( $redirect_with_status ) ) . '";</script>';
        exit;
    }
}

// Handle search and sorting
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'id';
$sort_order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : 'DESC';

// Validate sort parameters
$allowed_sort_columns = ['id', 'name', 'description'];
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'id';
}
if (!in_array($sort_order, ['ASC', 'DESC'])) {
    $sort_order = 'DESC';
}

// Check if this is an AJAX request
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

// Pagination Setup
$per_page = 10; // Categories per page
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Ensure page is at least 1
$offset = ($current_page - 1) * $per_page;

// Fetch categories with search
if ($search_query) {
    $categories = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}bkntc_help_categories WHERE name LIKE %s OR description LIKE %s ORDER BY {$sort_column} {$sort_order} LIMIT %d OFFSET %d",
        '%' . $wpdb->esc_like($search_query) . '%',
        '%' . $wpdb->esc_like($search_query) . '%',
        $per_page,
        $offset
    ));
    $total_categories = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_categories WHERE name LIKE %s OR description LIKE %s",
        '%' . $wpdb->esc_like($search_query) . '%',
        '%' . $wpdb->esc_like($search_query) . '%'
    ));
} else {
    $categories = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}bkntc_help_categories ORDER BY {$sort_column} {$sort_order} LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    $total_categories = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_categories");
}

$total_pages = ceil($total_categories / $per_page);

// If this is an AJAX request, return only the table content
if ($is_ajax) {
    // Start output buffering to capture only the table content
    ob_start();
    ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="sortable-header" data-sort="id">#</th>
                <th class="sortable-header" data-sort="name"><?php echo bkntc__('Name') ?></th>
                <th class="sortable-header" data-sort="description"><?php echo bkntc__('Description') ?></th>
                <th><?php echo bkntc__('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($categories) {
                foreach ($categories as $category) {
                    $edit_url = add_query_arg([
                        'page' => $_GET['page'],
                        'module' => 'help-center',
                        'view' => 'categories',
                        'action' => 'edit',
                        'id' => $category->id
                    ], 'admin.php');
                    
                    $delete_url = add_query_arg([
                        'page' => $_GET['page'],
                        'module' => 'help-center',
                        'view' => 'categories',
                        'action' => 'delete',
                        'id' => $category->id
                    ], 'admin.php');
                    
                    echo '<tr>';
                    echo '<td>' . esc_html($category->id) . '</td>';
                    echo '<td>' . esc_html($category->name) . '</td>';
                    echo '<td>' . esc_html($category->description) . '</td>';
                    echo '<td class="actions">';
                    echo '<button class="btn btn-primary btn-sm edit-category" data-id="' . esc_attr($category->id) . '" data-name="' . esc_attr($category->name) . '" data-description="' . esc_attr($category->description) . '" data-icon="' . esc_attr($category->icon) . '">';
                    echo '<i class="fas fa-edit"></i>';
                    echo '</button>';
                    echo '<button class="btn btn-danger btn-sm delete-category" data-url="' . $delete_url . '">';
                    echo '<i class="fas fa-trash"></i>';
                    echo '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">' . bkntc__('No categories found.') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
    <?php
    // Get the buffered content and end buffering
    $table_content = ob_get_clean();
    
    // Return the table content and exit
    echo $table_content;
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Categories</title>
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/css/style-admin.css' ); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/css/manage-categories.css' ); ?>">

  <!-- AJAX Setup -->
  <script>
    var helpCenterAjax = {
      ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
      nonce: '<?php echo wp_create_nonce('booknetic_help_center'); ?>'
    };
  </script>
 
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <?php // SweetAlert and FontAwesome now loaded via AssetManager ?>
 
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
           class="button-group-item primary-button">
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
    </div>
    
    <div class="button-group">
        <a href="#" 
           class="button-group-item secondary-button" id="addCategoryBtn" style="height: 40px;">
           <i class="fas fa-plus" style="margin-right:10px;"></i> <?php echo bkntc__('Add New Category') ?>
        </a>
        <a href="<?php echo esc_url( admin_url( add_query_arg( [ 'view' => 'reorder_categories' ], $base_url ) ) ); ?>" 
           class="button-group-item secondary-button" style="height: 40px;">
           <i class="fas fa-sort" style="margin-right:10px;"></i> <?php echo bkntc__('Reorder Categories') ?>
        </a>
        <button id="bulkDeleteBtn" class="button-group-item secondary-button" style="height: 40px; background-color: #dc3545; color: white; display: none;">
           <i class="fas fa-trash" style="margin-right:10px;"></i> <?php echo bkntc__('Delete Selected') ?>
        </button>
    <!-- Search Bar -->
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <input type="hidden" name="module" value="help-center">
            <input type="hidden" name="view" value="categories">
            <input type="hidden" name="categories" value="yes">
            <input style="height: 40px;" class="button-group-item secondary-button for-hover" type="text" name="search" id="search-input" placeholder="<?php echo bkntc__('Search categories...') ?>" value="<?php echo esc_attr($search_query); ?>">
        </form>
    </div>



    <table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th class="checkbox-column">
                <input type="checkbox" id="select-all-categories" class="select-all-checkbox">
            </th>
            <th class="sortable-header" data-sort="id">
                <div class="sort-container">
                    <span><?php echo bkntc__('ID')?></span>
                    <div class="sort-icons">
                        <i class="fas fa-sort-up<?php echo $sort_column === 'id' && $sort_order === 'ASC' ? ' active' : ''; ?>"></i>
                        <i class="fas fa-sort-down<?php echo $sort_column === 'id' && $sort_order === 'DESC' ? ' active' : ''; ?>"></i>
                    </div>
                </div>
            </th>
            <th class="sortable-header" data-sort="name">
                <div class="sort-container">
                    <span><?php echo bkntc__('Category')?></span>
                    <div class="sort-icons">
                        <i class="fas fa-sort-up<?php echo $sort_column === 'name' && $sort_order === 'ASC' ? ' active' : ''; ?>"></i>
                        <i class="fas fa-sort-down<?php echo $sort_column === 'name' && $sort_order === 'DESC' ? ' active' : ''; ?>"></i>
                    </div>
                </div>
            </th>
            <th class="sortable-header" data-sort="description">
                <div class="sort-container">
                    <span><?php echo bkntc__('Description')?></span>
                    <div class="sort-icons">
                        <i class="fas fa-sort-up<?php echo $sort_column === 'description' && $sort_order === 'ASC' ? ' active' : ''; ?>"></i>
                        <i class="fas fa-sort-down<?php echo $sort_column === 'description' && $sort_order === 'DESC' ? ' active' : ''; ?>"></i>
                    </div>
                </div>
            </th>
            <th><?php echo bkntc__('Actions')?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($categories) {
            foreach ($categories as $category) {
                $delete_url = esc_url(add_query_arg([
                    'page' => $_GET['page'],
                    'module' => 'help-center',
                    'adminp' => 'yes',
                    'deletecategory' => $category->id
                ], 'admin.php'));

                echo '<tr>';
                echo '<td class="checkbox-column"><input type="checkbox" class="category-checkbox" value="' . intval($category->id) . '"></td>';
                echo '<td class="id-column">' . intval($category->id) . '</td>';
                echo '<td class="name-column"><div class="category-name-wrapper"><i class="fas ' . (!empty($category->icon) ? esc_attr($category->icon) : 'fa-book') . '"></i><span>' . esc_html($category->name) . '</span></div></td>';
                echo '<td class="description-column">' . esc_html($category->description) . '</td>';
                echo '<td class="actions-column">';
                echo '<div class="btn-group">';
                echo '<button class="btn btn-primary btn-sm edit-category" data-id="' . esc_attr($category->id) . '" data-name="' . esc_attr($category->name) . '" data-description="' . esc_attr($category->description) . '" data-icon="' . (!empty($category->icon) ? esc_attr($category->icon) : 'fa-book') . '">';
                echo '<i class="fas fa-edit"></i>';
                echo '</button>';
                echo '<button class="btn btn-danger btn-sm delete-category" data-url="' . $delete_url . '">';
                echo '<i class="fas fa-trash"></i>';
                echo '</button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">' . bkntc__('No categories found.') . '</td></tr>';
        }
        ?>
    </tbody>
</table>

    <!-- Search Results Container -->
    <div id="search-results"></div>

    <!-- Category Form Modal -->
    <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo bkntc__('Category') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm" method="post" action="">
                        <?php wp_nonce_field('manage_category'); ?>
                        <input type="hidden" name="category_id" id="category_id" value="">
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo bkntc__('Category Name') ?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-heading text-muted"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo bkntc__('Description') ?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-align-left text-muted"></i>
                                </span>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?php echo bkntc__('Icon') ?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-book" style="color: var(--primary-color);" id="selected-icon-preview"></i>
                                </span>
                                <input type="text" class="form-control" name="icon" id="icon" value="fa-book" required>
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#iconPickerModal">  
                                    <i class="fas fa-search"></i> <?php echo bkntc__('Browse') ?>
                                </button>
                            </div>
                            <small class="form-text text-muted"><?php echo bkntc__('Select or enter Font Awesome icon class') ?></small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo bkntc__('Close') ?></button>
                    <button type="button" class="btn btn-primary" id="saveCategoryBtn"><?php echo bkntc__('Save') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="iconPickerModalLabel"><?php echo bkntc__('Select Icon') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="iconSearch" placeholder="<?php echo bkntc__('Search icons...') ?>">
                    </div>
                    <div class="row row-cols-2 row-cols-md-4 g-3" id="iconGrid">
                        <!-- Icons will be populated here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- All JavaScript functionality has been moved to manage-categories.js -->


<!-- Load the manage-categories.js file -->
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset( 'assets/backend/js/manage-categories.js' ); ?>"></script>

    <?php if ($total_pages > 1): ?>
    <div class="pagination" style="margin-top: 30px;">
        <?php if ($current_page > 1): ?>
            <?php 
            // Get current URL parameters and remove action_status
            $current_params = $_GET;
            unset($current_params['action_status']);
            $current_params['paged'] = $current_page - 1;
            ?>
            <a href="<?php echo esc_url(add_query_arg($current_params, 'admin.php')); ?>" class="prev">← <?php echo bkntc__('Prev')?></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $current_page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <?php 
                // Get current URL parameters and remove action_status
                $current_params = $_GET;
                unset($current_params['action_status']);
                $current_params['paged'] = $i;
                ?>
                <a href="<?php echo esc_url(add_query_arg($current_params, 'admin.php')); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <?php 
            // Get current URL parameters and remove action_status
            $current_params = $_GET;
            unset($current_params['action_status']);
            $current_params['paged'] = $current_page + 1;
            ?>
            <a href="<?php echo esc_url(add_query_arg($current_params, 'admin.php')); ?>" class="next"><?php echo bkntc__('Next')?> →</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

  </div>
</div>
</div>
</div>
</body>
</html>
<?php
ob_end_flush(); // End output buffering and flush content to browser.
?>