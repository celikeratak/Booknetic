jQuery(document).ready(function($) {
  // Save feature toggles
  $('#saveFeatureToggles').on('click', function() {
    const toggles = {
      feedback_section: $('#feedback_section').is(':checked'),
      still_need_help: $('#still_need_help').is(':checked'),
      related_articles: $('#related_articles').is(':checked'),
      livechat: $('#livechat').is(':checked'),
      popular_topics: $('#popular_topics').is(':checked')
    };
    
    // Show loading state
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Saving...'));
    
    // Get AJAX URL and nonce
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
    
    // Send AJAX request
    $.ajax({
      url: ajaxUrl,
      type: 'POST',
      data: {
        action: 'booknetic_save_feature_toggles',
        _wpnonce: nonce,
        toggles: JSON.stringify(toggles)
      },
      beforeSend: function() {
        // Already showing loading state above
      },
      success: function(response) {
        try {
          // Parse response if it's a string
          if (typeof response === 'string') {
            response = JSON.parse(response);
          }
          
          // Check status and handle success
          if (response.status) {
            // Show success message
            booknetic.toast(response.message || booknetic.__('Feature toggles saved successfully!'), 'success');
          } else {
            // Handle error with message from server
            booknetic.toast(response.error || booknetic.__('Failed to save feature toggles.'), 'error');
          }
        } catch (e) {
          // Handle parsing errors
          booknetic.toast(booknetic.__('Invalid response from server'), 'error');
        }
      },
      error: function() {
        // Handle AJAX errors
        booknetic.toast(booknetic.__('Failed to save feature toggles'), 'error');
      },
      complete: function() {
        // Reset button state
        $('#saveFeatureToggles').prop('disabled', false).html('<i class="fas fa-save" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Save Changes'));
      }
    });
  });
});
