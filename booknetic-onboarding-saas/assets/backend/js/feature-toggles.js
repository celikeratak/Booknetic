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
    
    // Get AJAX URL and nonce
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
    const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : help_center_nonce;
    
    
    // Send AJAX request
    $.ajax({
      url: ajaxUrl,
      type: 'POST',
      data: {
        action: 'booknetic_save_feature_toggles',
        _wpnonce: nonce,
        feedback_section: toggles.feedback_section ? 1 : 0,
        still_need_help: toggles.still_need_help ? 1 : 0,
        related_articles: toggles.related_articles ? 1 : 0,
        livechat: toggles.livechat ? 1 : 0,
        popular_topics: toggles.popular_topics ? 1 : 0
      },
      beforeSend: function() {
        // Show loading indicator
        $('#saveFeatureToggles').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="color: white; margin-right:5px;"></i> ' + booknetic.__('Saving...'));
      },
      success: function(response) {
        try {
          // Parse response if it's a string
          if (typeof response === 'string') {
            response = JSON.parse(response);
          }
          
          // Check status and handle success
          if (response.status || response.success) {
            // Show success message
            booknetic.toast(response.message || booknetic.__('Feature toggles saved successfully!'), 'success');
          } else {
            // Handle error with message from server
            booknetic.toast(response.error || booknetic.__('Failed to save feature toggles.'), 'error');
          }
        } catch (e) {
          console.error('Error parsing response:', e);
          // Handle parsing errors
          booknetic.toast(booknetic.__('Invalid response from server'), 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error:', status, error);
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
