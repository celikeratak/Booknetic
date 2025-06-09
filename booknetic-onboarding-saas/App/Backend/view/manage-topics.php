<?php
ob_start(); // Start output buffering immediately.
defined('ABSPATH') or die();

// Only administrators should access this page.
if ( ! current_user_can('manage_options') ) {
  wp_die('You do not have sufficient permissions to access this page.');
}


use BookneticAddon\ContactUsP\ContactUsPAddon;
use function BookneticAddon\ContactUsP\bkntc__;



global $wpdb;
// Hard-coded redirect URL.
$redirect_url = add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php');
$base_url = isset($_GET['page']) && $_GET['page'] === 'booknetic' && isset($_GET['page']) && $_GET['module'] === 'help-center' ? "admin.php?page=booknetic&module=help-center&view=dashboard" : "admin.php?page=booknetic-saas&module=help-center&view=dashboard";

// Handle search and sorting
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'id';
$sort_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

// Validate sort column
$allowed_sort_columns = ['id', 'title', 'category_id'];
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'id';
}

// Validate sort order
if (!in_array(strtoupper($sort_order), ['ASC', 'DESC'])) {
    $sort_order = 'DESC';
}

// Pagination logic
$topics_per_page = 10;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $topics_per_page;

if ($search_query) {
    $topics = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}bkntc_help_topics WHERE title LIKE %s ORDER BY {$sort_column} {$sort_order} LIMIT %d OFFSET %d",
        '%' . $wpdb->esc_like($search_query) . '%',
        $topics_per_page,
        $offset
    ));
    $total_topics = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_topics WHERE title LIKE %s",
        '%' . $wpdb->esc_like($search_query) . '%'
    ));
} else {
    $topics = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}bkntc_help_topics ORDER BY {$sort_column} {$sort_order} LIMIT %d OFFSET %d",
        $topics_per_page,
        $offset
    ));
    $total_topics = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkntc_help_topics");
}

$total_pages = ceil($total_topics / $topics_per_page);
// Process deletion if requested via the task=delete and topic_id parameters
// Also support the old deletetopic parameter for backward compatibility
$topic_id = 0;
if (isset($_GET['task']) && $_GET['task'] === 'delete' && isset($_GET['topic_id'])) {
    $topic_id = intval($_GET['topic_id']);
} elseif (isset($_GET['deletetopic'])) {
    $topic_id = intval($_GET['deletetopic']);
}

if ($topic_id > 0) {
    $wpdb->delete("{$wpdb->prefix}bkntc_help_topics", ['id' => $topic_id], ['%d']);
    if (!headers_sent()) {
        wp_redirect(add_query_arg(['view' => 'topics', 'action_status' => 'deleted'], $redirect_url));
        exit;
    } else {
        echo '<script type="text/javascript">window.location.href="' . html_entity_decode(esc_url(add_query_arg(['view' => 'topics', 'action_status' => 'deleted'], $redirect_url))) . '";</script>';
        exit;
    }
}

// Process deletion if requested via the "deletecategory" parameter.
$redirect_with_status = add_query_arg(['categories' => 'yes', 'action_status' => 'deleted'], $redirect_url);
$deletecategory = isset($_GET['deletecategory']) ? intval($_GET['deletecategory']) : 0;
if ( $deletecategory > 0 ) {
  $wpdb->delete("{$wpdb->prefix}bkntc_help_categories", ['id' => $deletecategory], ['%d']);
  if ( ! headers_sent() ) {
    wp_redirect( $redirect_with_status );
    exit;
} else {
    echo '<script type="text/javascript">window.location.href="' . html_entity_decode( esc_url( $redirect_with_status ) ) . '";</script>';
    exit;
}


}

// Fetch all categories (keyed by id) for use in dropdowns.
$categories = $wpdb->get_results(
  "SELECT id, name FROM {$wpdb->prefix}bkntc_help_categories ORDER BY id ASC",
  OBJECT_K
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Topics</title>
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/index.css'); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/style-admin.css'); ?>">
  <link rel="stylesheet" href="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/css/manage-categories.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
  <?php // SweetAlert and FontAwesome now loaded via AssetManager ?>
  <script type="text/javascript" src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/index.js'); ?>"></script>
  
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
      <div class="topics-list">
  <div class="button-group">
        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'dashboard'], 'admin.php')); ?>" 
           class="button-group-item secondary-button">
           <i class="fas fa-home" style="margin-right:10px;"></i> <?php echo bkntc__('Dashboard') ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics'], 'admin.php')); ?>" 
           class="button-group-item primary-button">
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
    <div>
  <div class="button-group">
    <a href="<?php echo esc_url(add_query_arg(['page' => $_GET['page'], 'module' => 'help-center', 'view' => 'topics', 'task' => 'add'], 'admin.php')); ?>" 
       class="button-group-item secondary-button" style="height: 40px;">
       <i class="fas fa-plus" style="margin-right:10px;"></i> <?php echo bkntc__('Add New Topic') ?>
    </a>
    <button id="bulkDeleteBtn" class="button-group-item secondary-button" style="height: 40px; background-color: #dc3545; color: white; display: none;">
       <i class="fas fa-trash" style="margin-right:10px;"></i> <?php echo bkntc__('Delete Selected') ?>
    </button>
 <!-- Search Bar -->
    <form method="get" action="">
      <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
      <input type="hidden" name="module" value="help-center">
      <input type="hidden" name="view" value="topics">
     <input class="button-group-item secondary-button for-hover" style="height: 40px;" type="text" name="search" placeholder="<?php echo bkntc__('Search topics...') ?>" value="<?php echo esc_attr($search_query); ?>">
    </form>
</div></div>
   

  <table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
    <tr>
      <th class="checkbox-column">
        <input type="checkbox" id="select-all-topics" class="select-all-checkbox">
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
      <th class="sortable-header" data-sort="title">
          <div class="sort-container">
              <span><?php echo bkntc__('Title')?></span>
              <div class="sort-icons">
                  <i class="fas fa-sort-up<?php echo $sort_column === 'title' && $sort_order === 'ASC' ? ' active' : ''; ?>"></i>
                  <i class="fas fa-sort-down<?php echo $sort_column === 'title' && $sort_order === 'DESC' ? ' active' : ''; ?>"></i>
              </div>
          </div>
      </th>
      <th class="sortable-header" data-sort="category_id">
          <div class="sort-container">
              <span><?php echo bkntc__('Category')?></span>
              <div class="sort-icons">
                  <i class="fas fa-sort-up<?php echo $sort_column === 'category_id' && $sort_order === 'ASC' ? ' active' : ''; ?>"></i>
                  <i class="fas fa-sort-down<?php echo $sort_column === 'category_id' && $sort_order === 'DESC' ? ' active' : ''; ?>"></i>
              </div>
          </div>
      </th>
      <th><?php echo bkntc__('Actions')?></th>
    </tr>
    </thead>
    <tbody>
    <?php
if ($topics) {
    foreach ($topics as $topic) {
        $catName = isset($categories[$topic->category_id]) ? $categories[$topic->category_id]->name : 'Uncategorized';
        $edit_url = esc_url(add_query_arg([
            'page'      => $_GET['page'],
            'module'    => 'help-center',
            'view'      => 'topics',
            'task'      => 'edit',
            'topic_id'  => $topic->id
        ], 'admin.php'));

        $delete_url = esc_url(add_query_arg([
            'page'        => $_GET['page'],
            'module'      => 'help-center',
            'view'        => 'topics',
            'task'        => 'delete',
            'topic_id'    => $topic->id
        ], 'admin.php'));

        echo '<tr>';
        echo '<td class="checkbox-column"><input type="checkbox" class="topic-checkbox" value="' . intval($topic->id) . '"></td>';
        echo '<td class="id-column">' . intval($topic->id) . '</td>';
        echo '<td class="title-column">' . esc_html($topic->title) . '</td>';
        echo '<td class="category-column">' . esc_html($catName) . '</td>';
        echo '<td class="actions-column">';
        echo '<button class="btn btn-primary btn-sm edit-topic" onclick="window.location.href=\'' . $edit_url . '\'">';
        echo '<i class="fas fa-edit"></i>';
        echo '</button> ';
        echo '<button class="btn btn-danger btn-sm delete-topic" data-url="' . $delete_url . '">';
        echo '<i class="fas fa-trash"></i>';
        echo '</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4">' . bkntc__('No topics found.') . '</td></tr>';
}
?>
    </tbody>
  </table>

  <div class="pagination" style="margin-top: 30px;">
    <?php if ($total_pages > 1): ?>
        <!-- Previous Button (Hidden on First Page) -->
        <?php if ($current_page > 1): ?>
            <?php 
            // Get current URL parameters and remove action_status
            $current_params = $_GET;
            unset($current_params['action_status']);
            $current_params['paged'] = $current_page - 1;
            ?>
            <a href="<?php echo esc_url(add_query_arg($current_params, 'admin.php')); ?>" class="prev">← <?php echo bkntc__('Prev')?></a>
        <?php endif; ?>

        <!-- Page Numbers -->
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

        <!-- Next Button (Hidden on Last Page) -->
        <?php if ($current_page < $total_pages): ?>
            <?php 
            // Get current URL parameters and remove action_status
            $current_params = $_GET;
            unset($current_params['action_status']);
            $current_params['paged'] = $current_page + 1;
            ?>
            <a href="<?php echo esc_url(add_query_arg($current_params, 'admin.php')); ?>" class="next"><?php echo bkntc__('Next')?> →</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
    // Handle table sorting
    document.querySelectorAll('.sortable-header').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const currentOrder = new URLSearchParams(window.location.search).get('order') || 'DESC';
            const newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            
            // Construct the new URL with sort parameters
            const url = new URL(window.location.href);
            url.searchParams.set('sort', column);
            url.searchParams.set('order', newOrder);
            url.searchParams.set('view', 'topics');
            
            // Preserve search query and pagination if they exist
            const searchQuery = new URLSearchParams(window.location.search).get('search');
            if (searchQuery) {
                url.searchParams.set('search', searchQuery);
            }
            
            // Add loading state
            const table = document.querySelector('table');
            table.classList.add('loading');
            
            // Navigate to the new URL
            window.location.href = url.toString();
        });
    });

    // Initialize helpCenterAjax object for AJAX calls
    var helpCenterAjax = {
        ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>',
        nonce: '<?php echo wp_create_nonce("booknetic_help_center"); ?>'
    };
    
    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('select-all-topics');
    const topicCheckboxes = document.querySelectorAll('.topic-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Function to check if any checkboxes are selected
    function updateBulkDeleteButton() {
        const anySelected = Array.from(topicCheckboxes).some(checkbox => checkbox.checked);
        bulkDeleteBtn.style.display = anySelected ? 'inherit' : 'none';
    }
    
    // Select all checkbox functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            topicCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    // Individual checkbox functionality
    topicCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(topicCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateBulkDeleteButton();
        });
    });
    
    // Bulk delete functionality
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const selectedIds = Array.from(topicCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                booknetic.toast(booknetic.__('Please select at least one topic to delete.'), 'warning');
                return;
            }
            
            // Confirm deletion using Booknetic's built-in confirmation
            if (typeof booknetic.confirm === 'function') {
                booknetic.confirm(
                    booknetic.__('You are about to delete ' + selectedIds.length + ' topics. This action cannot be undone.'),
                    'danger',
                    'trash',
                    function() {
                        // AJAX request to delete topics
                        const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
                        const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
                        
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'booknetic_bulk_delete_topics',
                                _wpnonce: nonce,
                                topic_ids: selectedIds
                            },
                            success: function(response) {
                                try {
                                    // Parse response if it's a string
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response);
                                    }
                                    
                                    // Check status and handle success
                                    if (response.success) {
                                        // Redirect with action_status instead of showing toast directly
                                        const currentUrl = new URL(window.location.href);
                                        currentUrl.searchParams.set('action_status', 'bulk_deleted');
                                        window.location.href = currentUrl.toString();
                                    } else {
                                        // Handle error with message from server
                                        booknetic.toast(response.data && response.data.message ? response.data.message : booknetic.__('Failed to delete topics.'), 'error');
                                    }
                                } catch (e) {
                                    // Handle parsing errors
                                    booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                                }
                            },
                            error: function() {
                                // Handle AJAX errors
                                booknetic.toast(booknetic.__('Failed to process request'), 'error');
                            }
                        });
                    }
                );
            } else {
                // Fallback if booknetic.confirm is not available
                if (confirm(booknetic.__('You are about to delete ' + selectedIds.length + ' topics. This action cannot be undone.'))) {
                    // AJAX request to delete topics
                    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
                    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'booknetic_bulk_delete_topics',
                            _wpnonce: nonce,
                            topic_ids: selectedIds
                        },
                        success: function(response) {
                            try {
                                // Parse response if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                // Check status and handle success
                                if (response.success) {
                                    // Redirect with action_status instead of showing alert directly
                                    const currentUrl = new URL(window.location.href);
                                    currentUrl.searchParams.set('action_status', 'bulk_deleted');
                                    window.location.href = currentUrl.toString();
                                } else {
                                    // Handle error with message from server
                                    alert(response.data && response.data.message ? response.data.message : booknetic.__('Failed to delete topics.'));
                                }
                            } catch (e) {
                                // Handle parsing errors
                                alert(booknetic.__('Invalid response from server'));
                            }
                        },
                        error: function() {
                            // Handle AJAX errors
                            alert(booknetic.__('Failed to process request'));
                        }
                    });
                }
            }
        });
    }

    $(document).on('click', '.delete-topic', function(e) {
    e.preventDefault();
    var deleteUrl = $(this).data('url');

    booknetic.confirm(booknetic.__('are_you_sure_want_to_delete'), 'danger', 'trash', function(modal) {
        window.location.href = deleteUrl + '&action_status=deleted';
    });
});
</script>
  </div>
</div>
</body>

<script>
  const bookneticL10n = {
    'BKconfirmDelete': '<?php echo bkntc__('Are you sure?') ?>',
    'BKcancel': '<?php echo bkntc__('Cancel') ?>',
    'BKdelete': '<?php echo bkntc__('Yes, delete it!') ?>',
    'BKconfirmDeleteText': '<?php echo bkntc__('This action cannot be undone!') ?>',
    'BKToastDeleteSuccess': '<?php echo bkntc__('Topic Successfully Deleted!') ?>',
    'BKToastDeleteError': '<?php echo bkntc__('An error occurred while deleting the topic!') ?>',
    'BKToastAddedSuccess': '<?php echo bkntc__('Topic Added Successfully!') ?>',
    'BKToastAddedError': '<?php echo bkntc__('An error occurred while adding the topic!') ?>',
    'BKToastEditedSuccess': '<?php echo bkntc__('Topic Updated Successfully!') ?>',
    'BKToastEditedError': '<?php echo bkntc__('An error occurred while updating the topic!') ?>',
  }
</script>
<script>

</script>
<script>
  jQuery(document).ready(function($) {
    // Check for action_status in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const actionStatus = urlParams.get('action_status');
    
    // Create a unique key for this specific action status to track in session storage
    const actionStatusKey = 'toast_shown_' + (actionStatus || 'none') + '_' + new Date().getTime();
    const isFirstLoad = true; // Always show toast for new action_status
    
    if (actionStatus && isFirstLoad) {
        // Show appropriate toast message based on action_status
        if (actionStatus === 'deleted') {
            booknetic.toast(bookneticL10n['BKToastDeleteSuccess'], 'success');
        } else if (actionStatus === 'bulk_deleted') {
            booknetic.toast(booknetic.__('Selected topics have been deleted successfully.'), 'success');
        } else if (actionStatus === 'added') {
            booknetic.toast(bookneticL10n['BKToastAddedSuccess'], 'success');
        } else if (actionStatus === 'updated') {
            booknetic.toast(bookneticL10n['BKToastEditedSuccess'], 'success');
        }
        
        // No need to use session storage as it's causing issues with multiple actions
        
        // Clean up URL by removing action_status parameter
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.delete('action_status');
        window.history.replaceState({}, document.title, newUrl);
    } else if (!actionStatus) {
        // No action needed when there's no action_status
    }
  });
</script>

</html>
<?php
ob_end_flush(); // Flush the output buffer.
?>