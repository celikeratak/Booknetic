
// Toast system implementation
if (typeof booknetic === 'undefined') {
    window.booknetic = {};
}

// Add translation function if it doesn't exist
if (typeof booknetic.__ !== 'function') {
    booknetic.__ = function(text) {
        return text; // Simple passthrough if no translation is available
    };
}

if (typeof booknetic.toast !== 'function') {
    booknetic.toast = function(message, type) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type, // 'success', 'error', 'warning', 'info', or 'question'
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('select-all-categories');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Function to check if any checkboxes are selected
    function updateBulkDeleteButton() {
        const anySelected = Array.from(categoryCheckboxes).some(checkbox => checkbox.checked);
        bulkDeleteBtn.style.display = anySelected ? 'inherit' : 'none';
    }
    
    // Select all checkbox functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    // Individual checkbox functionality
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateBulkDeleteButton();
        });
    });
    
    // Bulk delete functionality
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const selectedIds = Array.from(categoryCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                booknetic.toast(booknetic.__('Please select at least one category to delete.'), 'warning');
                return;
            }
            
            // Confirm deletion using Booknetic's built-in confirmation
            if (typeof booknetic.confirm === 'function') {
                booknetic.confirm(
                    booknetic.__('You are about to delete ' + selectedIds.length + ' categories. This action cannot be undone.'),
                    'danger',
                    'trash',
                    function() {
                        // AJAX request to delete categories
                        const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
                        const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
                        
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'booknetic_bulk_delete_categories',
                                _wpnonce: nonce,
                                category_ids: selectedIds
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
                                        booknetic.toast(response.data && response.data.message ? response.data.message : booknetic.__('Failed to delete categories.'), 'error');
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
                if (confirm(booknetic.__('You are about to delete ' + selectedIds.length + ' categories. This action cannot be undone.'))) {
                    // AJAX request to delete categories
                    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
                    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'booknetic_bulk_delete_categories',
                            _wpnonce: nonce,
                            category_ids: selectedIds
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
                                    alert(response.data && response.data.message ? response.data.message : booknetic.__('Failed to delete categories.'));
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
            
            // Preserve search query if it exists
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

    // Toast notifications based on action_status in URL.
    const urlParams = new URLSearchParams(window.location.search);
    const actionStatus = urlParams.get('action_status');
    
    // Create a unique key for this specific action status to track in session storage
    const actionStatusKey = 'toast_shown_' + (actionStatus || 'none') + '_' + new Date().getTime();
    const isFirstLoad = true; // Always show toast for new action_status
    
    if (actionStatus && isFirstLoad) {
        let toastMessage = '';
        let toastType = 'success';
        switch(actionStatus) {
            case 'added':
                toastMessage = typeof booknetic !== 'undefined' && typeof booknetic.__ === 'function' ? 
                    booknetic.__('Category added successfully!') : 'Category added successfully!';
                break;
            case 'updated':
                toastMessage = typeof booknetic !== 'undefined' && typeof booknetic.__ === 'function' ? 
                    booknetic.__('Category updated successfully!') : 'Category updated successfully!';
                break;
            case 'deleted':
                toastMessage = typeof booknetic !== 'undefined' && typeof booknetic.__ === 'function' ? 
                    booknetic.__('Category deleted successfully!') : 'Category deleted successfully!';
                break;
            case 'bulk_deleted':
                toastMessage = typeof booknetic !== 'undefined' && typeof booknetic.__ === 'function' ? 
                    booknetic.__('Categories have been deleted successfully.') : 'Categories have been deleted successfully.';
                break;
        }
        if (toastMessage) {
            booknetic.toast(toastMessage, toastType);
            
            // No need to use session storage as it's causing issues with multiple actions
            
            // Clean up the URL
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.delete('action_status');
            window.history.replaceState({}, document.title, newUrl);
        }
    } else if (!actionStatus) {
        // No action needed when there's no action_status
    }
    // AJAX Setup
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Dynamic search with debouncing
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value;
                const searchResultsContainer = document.getElementById('search-results');
                
                if (searchResultsContainer) {
                    // Show loading indicator
                    searchResultsContainer.innerHTML = '<div class="loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
                    
                    // Get current URL
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('ajax', '1');
                    currentUrl.searchParams.set('search', searchTerm);
                    
                    fetch(currentUrl.toString())
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            searchResultsContainer.innerHTML = data;
                        })
                        .catch(error => {
                            if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                                booknetic.toast('Failed to process search request', 'error');
                            } else {
                                searchResultsContainer.innerHTML = '<div class="error">Error: Failed to process search request</div>';
                            }
                            console.error('Search error:', error);
                        });
                }
            }, 500); // 500ms debounce delay
        });
    }

    const categoryModal = $('#categoryModal');
    const iconPickerModal = $('#iconPickerModal');
    
    // Icon picker functionality
    const helpIcons = [
        // Booking & Appointments
        'fas fa-calendar-plus', 'fas fa-calendar-check', 'fas fa-calendar-week', 'fas fa-calendar-alt',
        'fas fa-clock', 'fas fa-hourglass-half', 'fas fa-hourglass',
        'fas fa-business-time', 'fas fa-user-clock', 'fas fa-history',
        'fas fa-stopwatch', 'fas fa-calendar-day', 'fas fa-calendar-times',
        'fas fa-chair', 'fas fa-door-open', 'fas fa-concierge-bell',
        'fas fa-calendar-minus', 'fas fa-clipboard-list', 'fas fa-clipboard-check',
        'fas fa-clipboard', 'fas fa-ticket-alt', 'fas fa-address-card',
        
        // Staff & Service Providers
        'fas fa-user-tie', 'fas fa-user-nurse', 'fas fa-user-md',
        'fas fa-user-plus', 'fas fa-user-minus', 'fas fa-user-friends',
        'fas fa-user-check', 'fas fa-users-cog', 'fas fa-user-friends',
        
        // Payments & Finance
        'fas fa-credit-card', 'fas fa-money-bill', 'fas fa-money-bill-wave',
        'fas fa-coins', 'fas fa-dollar-sign', 'fas fa-percent',
        'fas fa-cash-register', 'fas fa-receipt', 'fas fa-file-invoice',
        'fas fa-file-invoice-dollar', 'fas fa-wallet', 'fas fa-piggy-bank',
        'fas fa-money-check', 'fas fa-money-check-alt',
        
        // Email & Notifications
        'fas fa-envelope', 'fas fa-envelope-open', 'fas fa-envelope-square',
        'fas fa-envelope-open-text', 'fas fa-paper-plane', 'fas fa-inbox',
        'fas fa-bell', 'fas fa-bell-slash', 'fas fa-comment-alt',
        
        // Locations & Venues
        'fas fa-map-pin', 'fas fa-thumbtack', 'fas fa-map-marker-alt',
        'fas fa-building', 'fas fa-store', 'fas fa-hospital', 'fas fa-hotel',
        'fas fa-warehouse', 'fas fa-home', 'fas fa-spa', 'fas fa-clinic-medical',
        
        // Services & Products
        'fas fa-briefcase', 'fas fa-box', 'fas fa-cut', 'fas fa-spa',
        'fas fa-box-open', 'fas fa-tags', 'fas fa-tag',
        'fas fa-certificate', 'fas fa-award', 'fas fa-medal',
        'fas fa-hand-holding', 'fas fa-hands', 'fas fa-pump-soap',
        
        // Customer Support
        'fas fa-headset', 'fas fa-phone', 'fas fa-phone-volume',
        'fas fa-comments', 'fas fa-comment-dots', 'fas fa-info',
        'fas fa-life-ring', 'fas fa-hands-helping', 'fas fa-handshake',
        'fas fa-notes-medical',
        
        // Documentation & Help
        'fas fa-book', 'fas fa-book-open', 'fas fa-book-reader', 'fas fa-bookmark',
        'fas fa-question-circle', 'fas fa-info-circle', 'fas fa-graduation-cap',
        'fas fa-chalkboard-teacher', 'fas fa-lightbulb',
        'fas fa-compass', 'fas fa-map', 'fas fa-road',
        
        // Features & Functions
        'fas fa-cog', 'fas fa-cogs', 'fas fa-tools', 'fas fa-wrench',
        'fas fa-sliders-h', 'fas fa-magic', 'fas fa-star',
        
        // User Interface
        'fas fa-desktop', 'fas fa-laptop', 'fas fa-mobile', 'fas fa-tablet',
        'fas fa-window-maximize', 'fas fa-palette', 'fas fa-paint-brush',
        
        // Data & Content
        'fas fa-database', 'fas fa-server',
        'fas fa-folder', 'fas fa-folder-open', 'fas fa-file',
        'fas fa-copy', 'fas fa-paste',
        
        // Users & Accounts
        'fas fa-user', 'fas fa-users', 'fas fa-user-circle',
        'fas fa-user-cog', 'fas fa-user-shield', 'fas fa-user-lock',
        'fas fa-key', 'fas fa-shield-alt', 'fas fa-user-graduate',
        
        // Analytics & Reports
        'fas fa-chart-line', 'fas fa-chart-bar', 'fas fa-chart-pie',
        'fas fa-chart-area', 'fas fa-search-dollar', 'fas fa-search-plus',
        
        // Tasks & Lists
        'fas fa-tasks', 'fas fa-list-ul',
        'fas fa-check-circle', 'fas fa-check-square', 'fas fa-bullhorn',
        
        // Files & Documents
        'fas fa-file-alt', 'fas fa-file-pdf', 'fas fa-file-word',
        'fas fa-file-excel', 'fas fa-file-image', 'fas fa-file-video',
        
        // Security & Privacy
        'fas fa-lock', 'fas fa-unlock', 'fas fa-fingerprint',
        
        // Navigation & Location
        'fas fa-map-signs', 'fas fa-map-marked-alt',
        'fas fa-route', 'fas fa-map-marker', 'fas fa-directions',
        
        // Media & Files
        'fas fa-image', 'fas fa-video', 'fas fa-camera',
        'fas fa-music', 'fas fa-headphones', 'fas fa-film',
        
        // Social & Sharing
        'fas fa-share', 'fas fa-share-alt', 'fas fa-thumbs-up',
        'fas fa-heart', 'fas fa-comment',
        
        // Development & Code
        'fas fa-code', 'fas fa-terminal', 'fas fa-bug',
        'fas fa-git', 'fas fa-github', 'fas fa-code-branch',
        
        // Misc UI Elements
        'fas fa-bars', 'fas fa-ellipsis', 'fas fa-plus',
        'fas fa-minus', 'fas fa-times', 'fas fa-check',
        'fas fa-arrow-right', 'fas fa-arrow-left',
        'fas fa-chevron-right', 'fas fa-chevron-left'
    ];

    function populateIcons() {
        const iconGrid = document.getElementById('iconGrid');
        iconGrid.innerHTML = '';
        
        helpIcons.forEach(icon => {
            const col = document.createElement('div');
            col.className = 'col text-center';
            col.innerHTML = `
                <div class="p-3 border rounded icon-item" data-icon="${icon}" style="cursor: pointer;">
                    <i class="${icon} fa-2x mb-2"></i>
                    <div class="small text-muted">${icon}</div>
                </div>
            `;
            iconGrid.appendChild(col);
        });

        // Add click event to icons
        document.querySelectorAll('.icon-item').forEach(item => {
            item.addEventListener('click', function() {
                const icon = this.dataset.icon;
                const iconClass = 'fas ' + icon;
                $('#icon').val(icon);
                $('#selected-icon-preview').removeClass().addClass(iconClass);
                iconPickerModal.modal('hide');
            });
        });
    }

    // Initialize modals
    categoryModal.on('show.bs.modal', function () {
        $('body').addClass('modal-open');
        var $backdrop = $('<div class="modal-backdrop fade show"></div>');
        $('body').append($backdrop);
    });

    categoryModal.on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open').css('padding-right', '');
        $('.modal-backdrop').remove();
    });

    iconPickerModal.on('show.bs.modal', function () {
        populateIcons();
        $('#iconSearch').val('').focus();
    });

    // Add Category Button - using event delegation for dynamically added elements
    $(document).on('click', '#addCategoryBtn', function() {
        $('#categoryForm')[0].reset();
        $('#category_id').val('');
        $('#icon').val('fa-book');
        $('#selected-icon-preview').removeClass().addClass('fas fa-book');
        categoryModal.modal('show');
    });

    // Edit Category - using event delegation for dynamically added elements
    $(document).on('click', '.edit-category', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');
        const icon = $(this).data('icon') || 'fa-book';

        $('#category_id').val(id);
        $('#name').val(name);
        $('#description').val(description);
        $('#icon').val(icon);
        $('#selected-icon-preview').removeClass().addClass('fas ' + icon);
        categoryModal.modal('show');
    });

    // Save Category
    $('#saveCategoryBtn').on('click', function() {
        $('#categoryForm').submit();
    });

    // Live preview for manual icon input
    $('#icon').on('input', function() {
        const icon = $(this).val();
        $('#selected-icon-preview').removeClass().addClass('fas ' + icon);
    });

    // Icon search functionality
    $('#iconSearch').on('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.icon-item').forEach(item => {
            const iconName = item.dataset.icon.toLowerCase();
            item.closest('.col').style.display = iconName.includes(searchTerm) ? '' : 'none';
        });
    });

    // Close modals when clicking outside
    $(document).on('click', '.modal-backdrop', function() {
        categoryModal.modal('hide');
        iconPickerModal.modal('hide');
    });
});

// Delete category handler is defined below

// The icon picker button already has data-toggle="modal" and data-target="#iconPickerModal"
// so Bootstrap will handle opening the modal automatically

// Handle delete confirmation using Booknetic's built-in confirmation
$(document).on('click', '.delete-category', function(e) {
    e.preventDefault();
    var deleteUrl = $(this).data('url');

    if (typeof booknetic !== 'undefined' && typeof booknetic.confirm === 'function') {
        booknetic.confirm(booknetic.__('are_you_sure_want_to_delete'), 'danger', 'trash', function() {
            window.location.href = deleteUrl + '&action_status=deleted';
        });
    } else {
        if (confirm('Are you sure you want to delete this category?')) {
            window.location.href = deleteUrl + '&action_status=deleted';
        }
    }
});