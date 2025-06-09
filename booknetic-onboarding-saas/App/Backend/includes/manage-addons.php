<?php
defined('ABSPATH') or die();

use function BookneticAddon\ContactUsP\bkntc__;

/**
 * Get recommended add-ons for Booknetic
 * 
 * @return array List of recommended add-ons
 */
function get_recommended_addons() {
    // Define the list of recommended add-ons
    $recommended_addons = [
        [
            'title' => 'Help Center for Booknetic SaaS',
            'description' => 'A knowledge base for your tenants to get answers to their questions.',
            'link' => 'https://tahiir.gumroad.com/l/onboarding-for-booknetic-saas-addon',
            'icon' => 'fa-question-circle',
            'category' => 'SaaS Exclusives'
        ],
        [
            'title' => 'Resource Management for Booknetic',
            'description' => 'Manage resources like staff, equipment, and facilities.',
            'link' => 'https://payhip.com/b/QWxJL',
            'icon' => 'fa-dolly',
            'category' => 'Others'
        ],
        [
            'title' => 'Custom CSS Admin Dashboard',
            'description' => 'Add custom CSS to your admin dashboard to match your brand.',
            'link' => 'https://roanjoh.gumroad.com/l/Booknetic-AdminPanel-Customization-Plugin',
            'icon' => 'fa-list-alt',
            'category' => 'Appearance'
        ],
        [
            'title' => 'Custom CSS Customer Panel',
            'description' => 'Add custom CSS to your customer panel to match your brand.',
            'link' => 'https://roanjoh.gumroad.com/l/Booknetic-CustomerPanel-Customization-Plugin',
            'icon' => 'fa-list-alt',
            'category' => 'Appearance'
        ],
        [
            'title' => 'Popup Modal for Tenants',
            'description' => 'Add a popup modal to your website to open tenant booking form in a modal.',
            'link' => 'https://roanjoh.gumroad.com/l/Booknetic-saas-Popup-Modal',
            'icon' => 'fa-calendar',
            'category' => 'SaaS Exclusives'
        ],
        [
            'title' => 'Blacklist for Booknetic',
            'description' => 'Block customers from booking your services.',
            'link' => 'https://payhip.com/b/GDkBz',
            'icon' => 'fa-user-slash',
            'category' => 'Others'
        ],
        [
            'title' => 'Workflows History for Booknetic',
            'description' => 'Track and manage workflows history for your services.',
            'link' => 'https://payhip.com/b/hNg12',
            'icon' => 'fa-history',
            'category' => 'Workflow actions'
        ]
    ];

    // Add a filter to allow other plugins to add their add-ons
    return apply_filters('booknetic_recommended_addons', $recommended_addons);
}

/**
 * Get unique categories from add-ons
 * 
 * @param array $addons List of add-ons
 * @return array List of unique categories
 */
function get_addon_categories($addons) {
    return array_unique(array_column($addons, 'category'));
}

/**
 * Get human-readable category name
 * 
 * @param string $category Category slug
 * @return string Human-readable category name
 */
function get_category_name($category) {
    $category_names = [
        'communication' => bkntc__('Appearance'),
        'workflow' => bkntc__('Workflow actions'),
        'customization' => bkntc__('Payment Gateways'),
        'integration' => bkntc__('Others'),
        'other' => bkntc__('SaaS Exclusives')
    ];
    
    return isset($category_names[$category]) ? $category_names[$category] : ucfirst($category);
}

/**
 * Render add-ons grid
 * 
 * @param array $addons List of add-ons to display
 */
function render_addons_grid($addons) {
    if (empty($addons)) {
        echo '<div class="no-addons-message">' . bkntc__('No add-ons available at this time.') . '</div>';
        return;
    }
    
    echo '<div class="addons-grid">';
    
    foreach ($addons as $addon) {
        $category = isset($addon['category']) ? esc_attr($addon['category']) : 'other';
        $icon = isset($addon['icon']) ? esc_attr($addon['icon']) : 'fa-puzzle-piece';
        $title = isset($addon['title']) ? esc_html($addon['title']) : '';
        $description = isset($addon['description']) ? esc_html($addon['description']) : '';
        $link = isset($addon['link']) ? esc_url($addon['link']) : '#';
        
        echo '<div class="addon-card" data-category="' . $category . '">';
        echo '    <div class="addon-icon">';
        echo '        <i class="fas ' . $icon . '"></i>';
        echo '    </div>';
        echo '    <div class="addon-content">';
        echo '        <h3 class="addon-title">' . $title . '</h3>';
        echo '        <p class="addon-description">' . $description . '</p>';
        echo '        <a href="' . $link . '" target="_blank" class="addon-link">';
        echo '            ' . bkntc__('Check add-on') . ' <i class="fas fa-external-link-alt"></i>';
        echo '        </a>';
        echo '    </div>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Render category filters
 * 
 * @param array $categories List of categories
 */
function render_category_filters($categories) {
    if (empty($categories)) {
        return;
    }
    
    echo '<div class="addons-categories">';
    echo '    <button class="category-filter active" data-category="all">' . bkntc__('All Add-ons') . '</button>';
    
    foreach ($categories as $category) {
        echo '    <button class="category-filter" data-category="' . esc_attr($category) . '">';
        echo '        ' . get_category_name($category);
        echo '    </button>';
    }
    
    echo '</div>';
}