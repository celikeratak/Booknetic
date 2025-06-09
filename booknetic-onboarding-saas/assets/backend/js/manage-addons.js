jQuery(document).ready(function($) {
    // Category filtering
    $('.category-filter').on('click', function() {
        const category = $(this).data('category');
        
        // Update active button
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
        
        // Show/hide add-ons based on category
        if (category === 'all') {
            $('.addon-card').show();
        } else {
            $('.addon-card').hide();
            $('.addon-card[data-category="' + category + '"]').show();
        }
    });
});