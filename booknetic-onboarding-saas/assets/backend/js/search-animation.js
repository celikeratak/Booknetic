/**
 * Search box animation for Booknetic Help Center
 * Adds attention-grabbing animation to the search box after a period of inactivity
 */
(function($) {
    'use strict';
    
    // Configuration
    const INACTIVITY_TIMEOUT = 8000; // 8 seconds of inactivity before animation starts
    const ANIMATION_DURATION = 5000; // Animation will run for 5 seconds
    
    // Variables to track state
    let inactivityTimer;
    let animationTimer;
    let userActive = false;
    let $searchBox;
    let $searchInput;
    
    // Initialize when document is ready
    $(document).ready(function() {
        initSearchAnimation();
    });
    
    /**
     * Initialize search box animation functionality
     */
    function initSearchAnimation() {
        $searchBox = $('.search-box');
        $searchInput = $('#helpSearchInput');
        
        if (!$searchBox.length || !$searchInput.length) {
            return; // Exit if elements don't exist
        }
        
        // Set up event listeners for user activity
        $searchInput.on('focus click keydown', handleUserActivity);
        $(document).on('mousemove scroll click keydown', handleUserActivity);
        
        // Start the inactivity timer
        resetInactivityTimer();
    }
    
    /**
     * Handle user activity events
     */
    function handleUserActivity() {
        userActive = true;
        
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
})(jQuery);
