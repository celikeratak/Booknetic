(function($) {
    "use strict";

    $(document).ready(function() {
        function showSuccess(message) {
            if (typeof booknetic !== 'undefined' && booknetic.toast) {
                booknetic.toast(message, 'success');
            } else {
                alert(message);
            }
        }

        function showError(message) {
            if (typeof booknetic !== 'undefined' && booknetic.toast) {
                booknetic.toast(message, 'error');
            } else {
                alert(message);
            }
        }

        function showConfirm(title, text, callback) {
            if (typeof booknetic !== 'undefined' && booknetic.confirm) {
                booknetic.confirm(text, callback, booknetic.options.yes_proceed, booknetic.options.cancel, title);
            } else {
                if (confirm(text)) {
                    callback();
                }
            }
        }

        let saveTimeout, isSaving = false;
        const $savingIndicator = $('.saving-indicator');
        let hasChanges = false;

        // Initialize position indicators
        function updatePositionIndicators() {
            $('.category-item').each(function(index) {
                $(this).find('.position-indicator').text(index + 1);
                
                // Enable/disable up/down buttons based on position
                const $container = $('#categories-container');
                const isFirst = $(this).is($container.find('.category-item:first-child'));
                const isLast = $(this).is($container.find('.category-item:last-child'));
                
                $(this).find('.move-up').prop('disabled', isFirst);
                $(this).find('.move-down').prop('disabled', isLast);
            });
        }

        // Initialize sortable
        $('#categories-container').sortable({
            // Removed handle option to make entire item draggable
            placeholder: 'ui-sortable-placeholder',
            connectWith: '.category-group-content',
            cursor: 'grabbing',
            opacity: 0.8,
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
                ui.helper.addClass('ui-sortable-helper');
            },
            stop: function(e, ui) {
                ui.item.css('transform', 'none').effect('highlight', {color: 'var(--primary-color-back)'}, 1000);
                updatePositionIndicators();
            },
            update: function() {
                hasChanges = true;
                updatePositionIndicators();
                
                // Clear any existing timeout
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }
            }
        }).disableSelection();

        // Save order function
        function saveOrder() {
            if (isSaving) return;
            isSaving = true;
            $savingIndicator.addClass('active');
            const order = [];
            $('.category-item').each(function(index) {
                order.push($(this).data('id'));
            });

            
            // Use the Booknetic AJAX pattern
            const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
            const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : helpCenterNonce;
            
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'booknetic_reorder_categories', // WordPress action hook
                    order: order,
                    _wpnonce: nonce
                },
                dataType: 'json',
                beforeSend: function() {
                    // Already showing loading indicator
                },
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Check status and handle success
                        if (response.success) {
                            showSuccess(booknetic.options.order_saved_success);
                            hasChanges = false;
                        } else if (response.status === 'ok' || response.status === 'success') {
                            // Handle legacy response format
                            showSuccess(booknetic.options.order_saved_success);
                            hasChanges = false;
                        } else {
                            // Extract error message from WordPress JSON response format
                            const errorMessage = response.data?.message || response.error_msg || booknetic.options.error_saving_order;
                            showError(errorMessage);
                        }
                    } catch (e) {
                        showError(booknetic.options.invalid_response);
                    }
                },
                error: function(xhr, status, error) {
                    // Try to parse response text if possible
                    try {
                        const responseText = xhr.responseText;
                        
                        // Check if response contains HTML (which indicates an error)
                        if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
                            showError(booknetic.options.server_returned_html);
                        } else if (responseText.trim().startsWith('{')) {
                            // Try to parse JSON response
                            try {
                                const jsonResponse = JSON.parse(responseText);
                                showError(jsonResponse.data?.message || booknetic.options.error_in_server_response);
                            } catch (jsonError) {
                                showError(booknetic.options.invalid_json_response);
                            }
                        } else {
                            showError(booknetic.options.network_error);
                        }
                    } catch (e) {
                        showError(booknetic.options.network_error);
                    }
                },
                complete: function() {
                    isSaving = false;
                    $savingIndicator.removeClass('active');
                }
            });
        }

        // Manual save button
        $('#saveOrderBtn').on('click', function() {
            if (hasChanges) {
                saveOrder();
            } else {
                showSuccess(booknetic.options.no_changes_to_save);
            }
        });

        // Reset order button
        $('#resetOrderBtn').on('click', function() {
            // Reset each category to its original position
            const $container = $('#categories-container');
            const $items = $container.find('.category-item').get();
            
            $items.sort(function(a, b) {
                return $(a).data('original-position') - $(b).data('original-position');
            });
            
            $.each($items, function(idx, item) {
                $container.append(item);
            });
            
            updatePositionIndicators();
            hasChanges = true;
            showSuccess(booknetic.options.order_has_been_reset);
        });

        // Move up/down buttons
        $(document).on('click', '.move-up', function() {
            const $item = $(this).closest('.category-item');
            const $prev = $item.prev('.category-item');
            
            if ($prev.length) {
                $item.insertBefore($prev);
                $item.effect('highlight', {color: 'var(--primary-color-back)'}, 1000);
                updatePositionIndicators();
                hasChanges = true;
            }
        });
        
        $(document).on('click', '.move-down', function() {
            const $item = $(this).closest('.category-item');
            const $next = $item.next('.category-item');
            
            if ($next.length) {
                $item.insertAfter($next);
                $item.effect('highlight', {color: 'var(--primary-color-back)'}, 1000);
                updatePositionIndicators();
                hasChanges = true;
            }
        });

        // Keyboard accessibility
        $('.category-item').attr('tabindex', '0');
        
        $(document).on('keydown', '.category-item', function(e) {
            const $item = $(this);
            
            // Arrow up/down for navigation
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                $item.prev('.category-item').focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                $item.next('.category-item').focus();
            }
            
            // Ctrl + Arrow up/down for moving
            if (e.ctrlKey && e.key === 'ArrowUp') {
                e.preventDefault();
                $item.find('.move-up').trigger('click');
            } else if (e.ctrlKey && e.key === 'ArrowDown') {
                e.preventDefault();
                $item.find('.move-down').trigger('click');
            }
        });

        // Initialize position indicators on load
        updatePositionIndicators();
        
        // Window beforeunload warning if there are unsaved changes
        $(window).on('beforeunload', function() {
            if (hasChanges) {
                return booknetic.options.unsaved_changes_warning;
            }
        });
    });
})(jQuery);
