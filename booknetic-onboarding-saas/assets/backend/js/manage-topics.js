/**
 * Booknetic Onboarding SaaS - Help Topics Management
 * 
 * This file contains JavaScript functionality for managing help topics
 * including rich text editing, file uploads, and tag management.
 */

$(document).ready(function() {
    // Initialize Summernote rich text editor
    if ($('#content').length) {
        $('#content').summernote({
            placeholder: booknetic_help_i18n.content_placeholder,
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    // Initialize Select2 for the category dropdown
    if ($('.category-select').length) {
        $('.category-select').select2({
            placeholder: booknetic_help_i18n.select_category,
            allowClear: true,
            width: '100%'
        });
    }
    
    
    // Custom file upload handling
    $('#upload_file_button').on('click', function(e) {
        e.preventDefault();
        // Trigger the hidden file input
        $('#file_upload_input').click();
    });
    
    // Handle file selection
    $('#file_upload_input').on('change', function(e) {
        var files = e.target.files;
        
        if (files.length > 0) {
            // Upload each file via AJAX
            for (var i = 0; i < files.length; i++) {
                uploadFile(files[i]);
            }
        }
    });
    
    // Function to upload a file via AJAX
    function uploadFile(file) {
        var formData = new FormData();
        formData.append('action', 'booknetic_upload_attachment');
        formData.append('_wpnonce', booknetic_help_i18n.upload_nonce);
        formData.append('file', file);
        
        // Create a temporary attachment item
        var attachmentList = $('#file_attachment_list');
        var tempItem = $('<div class="attachment-item uploading"></div>');
        
        // Add file icon based on file type
        var fileIcon = getFileIcon(file.name.split('.').pop().toLowerCase());
        tempItem.append('<i class="' + fileIcon + ' attachment-icon"></i>');
        
        // Create info container
        var infoContainer = $('<div class="attachment-info"></div>');
        
        // Add file name and loading indicator
        infoContainer.append('<span class="attachment-name">' + file.name + '</span>');
        infoContainer.append('<span class="attachment-status">' + booknetic_help_i18n.uploading + '</span>');
        
        // Add file size info
        var fileSize = formatFileSize(file.size);
        infoContainer.append('<span class="attachment-size">' + fileSize + '</span>');
        
        // Add info container to the item
        tempItem.append(infoContainer);
        
        // Add to the list and show it
        attachmentList.append(tempItem);
        attachmentList.show();
        
        // Show progress bar
        var progressBar = $('#upload_progress');
        var progressBarInner = progressBar.find('.progress-bar');
        var progressText = progressBar.find('.progress-text');
        progressBar.show();
        progressBarInner.css('width', '0%');
        
        // Get AJAX URL and nonce
        const ajaxUrl = booknetic_help_i18n.ajax_url;
        const nonce = booknetic_help_i18n.upload_nonce;
        
        // Upload the file with progress tracking
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                // Progress tracking is already handled in the xhr function
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        progressBarInner.css('width', percentComplete + '%');
                        progressText.text(booknetic_help_i18n.uploading + ' ' + percentComplete + '%');
                        tempItem.find('.attachment-status').text(booknetic_help_i18n.uploading + ' ' + percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                // Hide progress bar
                progressBar.hide();
                
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.success) {
                        // Update the temporary item with the real data
                        tempItem.removeClass('uploading');
                        tempItem.find('.attachment-status').remove();
                        tempItem.append('<span class="attachment-remove" data-id="' + response.data.id + '">×</span>');
                        tempItem.attr('data-id', response.data.id);
                        
                        // If it's an image, add thumbnail
                        if (response.data.type.match('image.*')) {
                            tempItem.find('.attachment-icon').remove();
                            tempItem.prepend('<img src="' + response.data.url + '" class="attachment-thumbnail">');
                        }
                        
                        // Create info container if it doesn't exist
                        if (tempItem.find('.attachment-info').length === 0) {
                            var infoContainer = $('<div class="attachment-info"></div>');
                            tempItem.find('.attachment-name, .attachment-size, .attachment-status').appendTo(infoContainer);
                            tempItem.append(infoContainer);
                        }
                        
                        // Add URL display
                        var urlContainer = $('<div class="attachment-url"></div>');
                        urlContainer.append('<a href="' + response.data.url + '" target="_blank" class="view-file-btn">' + booknetic_help_i18n.view_file + '</a>');
                        urlContainer.append('<button type="button" class="copy-link-btn" data-url="' + response.data.url + '">' + booknetic_help_i18n.copy_link + '</button>');
                        tempItem.find('.attachment-info').append(urlContainer);
                        
                        // Update the hidden input with attachment IDs
                        var currentIds = $('#attachments_input').val();
                        var newId = response.data.id;
                        var newIds = currentIds ? currentIds + ',' + newId : newId;
                        $('#attachments_input').val(newIds);
                    } else {
                        // Show error
                        tempItem.addClass('error');
                        tempItem.find('.attachment-status').text(booknetic_help_i18n.upload_failed + ': ' + (response.data && response.data.message ? response.data.message : booknetic_help_i18n.unknown_error));
                    }
                } catch (e) {
                    // Handle parsing errors
                    tempItem.addClass('error');
                    tempItem.find('.attachment-status').text(booknetic_help_i18n.invalid_response);
                }
            },
            error: function() {
                // Hide progress bar
                progressBar.hide();
                
                // Show error
                tempItem.addClass('error');
                tempItem.find('.attachment-status').text(booknetic_help_i18n.upload_failed + ': ' + booknetic_help_i18n.server_error);
            },
            complete: function() {
                // Additional cleanup if needed
            }
        });
    }
    
    // Remove attachment when clicking the remove button
    $(document).on('click', '.attachment-remove', function() {
        var attachmentItem = $(this).closest('.attachment-item');
        var attachmentId = $(this).data('id');
        
        // Remove from the list
        attachmentItem.remove();
        
        // Update the hidden input with attachment IDs
        if (attachmentId) {
            var currentIds = $('#attachments_input').val().split(',');
            var newIds = currentIds.filter(function(id) {
                return id != attachmentId;
            });
            $('#attachments_input').val(newIds.join(','));
        }
        
        // Hide the list if empty
        if ($('.attachment-item').length === 0) {
            $('#file_attachment_list').hide();
        }
    });
    
    // Helper function to get appropriate icon class based on file type
    function getFileIcon(fileType) {
        switch(fileType) {
            case 'pdf':
                return 'fas fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fas fa-file-powerpoint';
            case 'zip':
            case 'rar':
                return 'fas fa-file-archive';
            case 'txt':
                return 'fas fa-file-alt';
            default:
                return 'fas fa-file';
        }
    }
    
    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Handle responsive iframe containers
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.topic-content iframe').forEach(function(iframe) {
            if (!iframe.parentElement.classList.contains('video-container')) {
                var wrapper = document.createElement('div');
                wrapper.className = 'video-container';
                iframe.parentNode.insertBefore(wrapper, iframe);
                wrapper.appendChild(iframe);
            }
        });
    });
    
    // Add copy link functionality
    $(document).on('click', '.copy-link-btn', function() {
        var btn = $(this);
        var url = btn.data('url');
        var originalText = btn.text();
        
        // Use modern Clipboard API if available
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                // Success feedback
                btn.addClass('copy-success');
                btn.text(booknetic_help_i18n.copied);
                
                // Reset after 2 seconds
                setTimeout(function() {
                    btn.removeClass('copy-success');
                    btn.text(originalText);
                }, 2000);
            }).catch(function() {
                // Fallback to old method if permission denied
                fallbackCopy();
            });
        } else {
            // Fallback for browsers that don't support Clipboard API
            fallbackCopy();
        }
        
        // Fallback copy method
        function fallbackCopy() {
            // Create a temporary input element
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(url).select();
            
            // Copy the text
            document.execCommand('copy');
            
            // Remove the temporary element
            tempInput.remove();
            
            // Visual feedback
            btn.addClass('copy-success');
            btn.text(booknetic_help_i18n.copied);
            
            // Reset after 2 seconds
            setTimeout(function() {
                btn.removeClass('copy-success');
                btn.text(originalText);
            }, 2000);
        }
    });
});