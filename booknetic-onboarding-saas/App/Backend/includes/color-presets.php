<?php
/**
 * Color presets for the Booknetic Help Center
 * 
 * This file contains all the color presets used in the Help Center customization
 * 
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get all color presets for the Help Center
 * 
 * @return array Array of color presets with primary and secondary colors
 */
function get_color_presets() {
    return [
        // Material Design Colors
        'material_red' => [
            'primary' => '#F44336',
            'secondary' => '#E57373',
            'name' => 'Red'
        ],
        'material_pink' => [
            'primary' => '#E91E63',
            'secondary' => '#F06292',
            'name' => 'Pink'
        ],
        'material_purple' => [
            'primary' => '#9C27B0',
            'secondary' => '#BA68C8',
            'name' => 'Purple'
        ],
        'material_deep_purple' => [
            'primary' => '#673AB7',
            'secondary' => '#9575CD',
            'name' => 'Deep Purple'
        ],
        'material_indigo' => [
            'primary' => '#3F51B5',
            'secondary' => '#7986CB',
            'name' => 'Indigo'
        ],
        'material_blue' => [
            'primary' => '#2196F3',
            'secondary' => '#64B5F6',
            'name' => 'Blue'
        ],
        'material_light_blue' => [
            'primary' => '#03A9F4',
            'secondary' => '#4FC3F7',
            'name' => 'Light Blue'
        ],
        'material_cyan' => [
            'primary' => '#00BCD4',
            'secondary' => '#4DD0E1',
            'name' => 'Cyan'
        ],
        'material_teal' => [
            'primary' => '#009688',
            'secondary' => '#4DB6AC',
            'name' => 'Teal'
        ],
        'material_green' => [
            'primary' => '#4CAF50',
            'secondary' => '#81C784',
            'name' => 'Green'
        ],
        'material_light_green' => [
            'primary' => '#8BC34A',
            'secondary' => '#AED581',
            'name' => 'Light Green'
        ],
        'material_lime' => [
            'primary' => '#CDDC39',
            'secondary' => '#DCE775',
            'name' => 'Lime'
        ],
        'material_yellow' => [
            'primary' => '#FFEB3B',
            'secondary' => '#FFF176',
            'name' => 'Yellow'
        ],
        'material_amber' => [
            'primary' => '#FFC107',
            'secondary' => '#FFD54F',
            'name' => 'Amber'
        ],
        'material_orange' => [
            'primary' => '#FF9800',
            'secondary' => '#FFB74D',
            'name' => 'Orange'
        ],
        'material_deep_orange' => [
            'primary' => '#FF5722',
            'secondary' => '#FF8A65',
            'name' => 'Deep Orange'
        ],
        'material_brown' => [
            'primary' => '#795548',
            'secondary' => '#A1887F',
            'name' => 'Brown'
        ],
        'material_grey' => [
            'primary' => '#9E9E9E',
            'secondary' => '#BDBDBD',
            'name' => 'Grey'
        ],
        'material_blue_grey' => [
            'primary' => '#607D8B',
            'secondary' => '#90A4AE',
            'name' => 'Blue Grey'
        ],
        
        // Gradient Colors
        'gradient_sunset' => [
            'primary' => '#FF512F',
            'secondary' => '#F09819',
            'name' => 'Sunset'
        ],
        'gradient_ocean' => [
            'primary' => '#2193b0',
            'secondary' => '#6dd5ed',
            'name' => 'Ocean'
        ],
        'gradient_forest' => [
            'primary' => '#43e97b',
            'secondary' => '#38f9d7',
            'name' => 'Forest'
        ],
        'gradient_candy' => [
            'primary' => '#ff758c',
            'secondary' => '#ff7eb3',
            'name' => 'Candy'
        ],
        
        // Flat UI Colors
        'flat_turquoise' => [
            'primary' => '#1abc9c',
            'secondary' => '#48c9b0',
            'name' => 'Turquoise'
        ],
        'flat_emerald' => [
            'primary' => '#2ecc71',
            'secondary' => '#58d68d',
            'name' => 'Emerald'
        ],
        'flat_peter_river' => [
            'primary' => '#3498db',
            'secondary' => '#5dade2',
            'name' => 'Peter River'
        ],
        'flat_amethyst' => [
            'primary' => '#9b59b6',
            'secondary' => '#af7ac5',
            'name' => 'Amethyst'
        ],
        'flat_wet_asphalt' => [
            'primary' => '#34495e',
            'secondary' => '#5d6d7e',
            'name' => 'Wet Asphalt'
        ],
        'flat_green_sea' => [
            'primary' => '#16a085',
            'secondary' => '#45b39d',
            'name' => 'Green Sea'
        ],
        'flat_nephritis' => [
            'primary' => '#27ae60',
            'secondary' => '#52be80',
            'name' => 'Nephritis'
        ],
        'flat_belize_hole' => [
            'primary' => '#2980b9',
            'secondary' => '#5499c7',
            'name' => 'Belize Hole'
        ],
        'flat_wisteria' => [
            'primary' => '#8e44ad',
            'secondary' => '#a569bd',
            'name' => 'Wisteria'
        ],
        'flat_midnight_blue' => [
            'primary' => '#2c3e50',
            'secondary' => '#566573',
            'name' => 'Midnight Blue'
        ],
        'flat_sunflower' => [
            'primary' => '#f1c40f',
            'secondary' => '#f4d03f',
            'name' => 'Sunflower'
        ],
        'flat_carrot' => [
            'primary' => '#e67e22',
            'secondary' => '#eb984e',
            'name' => 'Carrot'
        ],
        'flat_alizarin' => [
            'primary' => '#e74c3c',
            'secondary' => '#ec7063',
            'name' => 'Alizarin'
        ],
        'flat_clouds' => [
            'primary' => '#ecf0f1',
            'secondary' => '#f4f6f7',
            'name' => 'Clouds'
        ],
        'flat_concrete' => [
            'primary' => '#95a5a6',
            'secondary' => '#b2babb',
            'name' => 'Concrete'
        ],
        
        // Nature Colors
        'nature_sky' => [
            'primary' => '#87CEEB',
            'secondary' => '#B0E0E6',
            'name' => 'Sky'
        ],
        'nature_grass' => [
            'primary' => '#90EE90',
            'secondary' => '#98FB98',
            'name' => 'Grass'
        ],
        'nature_sunset' => [
            'primary' => '#FFA07A',
            'secondary' => '#FFB6C1',
            'name' => 'Sunset'
        ],
        'nature_ocean' => [
            'primary' => '#1E90FF',
            'secondary' => '#00BFFF',
            'name' => 'Ocean'
        ],
        
        // Vibrant Colors
        'vibrant_purple' => [
            'primary' => '#8A2BE2',
            'secondary' => '#9B59B6',
            'name' => 'Purple'
        ],
        'vibrant_teal' => [
            'primary' => '#00B4D8',
            'secondary' => '#48CAE4',
            'name' => 'Teal'
        ],
        'vibrant_orange' => [
            'primary' => '#FF6B35',
            'secondary' => '#FF9F1C',
            'name' => 'Orange'
        ],
        'vibrant_pink' => [
            'primary' => '#FF1493',
            'secondary' => '#FF69B4',
            'name' => 'Pink'
        ],
        
        // Brand Colors
        'booknetic' => [
            'primary' => '#6d2ffd',
            'secondary' => '#ff3d71',
            'name' => 'Booknetic'
        ],
        'brand_facebook' => [
            'primary' => '#3b5998',
            'secondary' => '#8b9dc3',
            'name' => 'Facebook'
        ],
        'brand_twitter' => [
            'primary' => '#1da1f2',
            'secondary' => '#71c9f8',
            'name' => 'Twitter'
        ],
        'brand_instagram' => [
            'primary' => '#833ab4',
            'secondary' => '#fd1d1d',
            'name' => 'Instagram'
        ],
        'brand_linkedin' => [
            'primary' => '#0077b5',
            'secondary' => '#00a0dc',
            'name' => 'LinkedIn'
        ],
        'brand_youtube' => [
            'primary' => '#ff0000',
            'secondary' => '#ff4d4d',
            'name' => 'YouTube'
        ],
        'brand_pinterest' => [
            'primary' => '#bd081c',
            'secondary' => '#e60023',
            'name' => 'Pinterest'
        ],
        'brand_spotify' => [
            'primary' => '#1db954',
            'secondary' => '#1ed760',
            'name' => 'Spotify'
        ],
        'brand_whatsapp' => [
            'primary' => '#25d366',
            'secondary' => '#4aef8b',
            'name' => 'WhatsApp'
        ],
        
        // Tech Colors
        'tech_google' => [
            'primary' => '#4285F4',
            'secondary' => '#34A853',
            'name' => 'Google'
        ],
        'tech_microsoft' => [
            'primary' => '#00A4EF',
            'secondary' => '#7FBA00',
            'name' => 'Microsoft'
        ],
        'tech_apple' => [
            'primary' => '#000000',
            'secondary' => '#A3AAAE',
            'name' => 'Apple'
        ],
        'tech_meta' => [
            'primary' => '#0668E1',
            'secondary' => '#2ABBA7',
            'name' => 'Meta'
        ],
        
        // Dark Colors
        'dark_red' => [
            'primary' => '#B71C1C',
            'secondary' => '#D32F2F',
            'name' => 'Dark Red'
        ],
        'dark_orange' => [
            'primary' => '#E65100',
            'secondary' => '#F57C00',
            'name' => 'Dark Orange'
        ],
        'dark_yellow' => [
            'primary' => '#F9A825',
            'secondary' => '#FBC02D',
            'name' => 'Dark Yellow'
        ],
        'dark_lime' => [
            'primary' => '#9E9D24',
            'secondary' => '#C0CA33',
            'name' => 'Dark Lime'
        ],
        'dark_green' => [
            'primary' => '#1B5E20',
            'secondary' => '#2E7D32',
            'name' => 'Dark Green'
        ],
        'dark_teal' => [
            'primary' => '#004D40',
            'secondary' => '#00695C',
            'name' => 'Dark Teal'
        ],
        'dark_cyan' => [
            'primary' => '#006064',
            'secondary' => '#00838F',
            'name' => 'Dark Cyan'
        ],
        'dark_blue' => [
            'primary' => '#0D47A1',
            'secondary' => '#1565C0',
            'name' => 'Dark Blue'
        ],
        'dark_indigo' => [
            'primary' => '#1A237E',
            'secondary' => '#283593',
            'name' => 'Dark Indigo'
        ],
        'dark_purple' => [
            'primary' => '#4A148C',
            'secondary' => '#6A1B9A',
            'name' => 'Dark Purple'
        ],
        'dark_pink' => [
            'primary' => '#880E4F',
            'secondary' => '#AD1457',
            'name' => 'Dark Pink'
        ],
        'dark_magenta' => [
            'primary' => '#6A1B9A',
            'secondary' => '#8E24AA',
            'name' => 'Dark Magenta'
        ],
        'dark_gold' => [
            'primary' => '#FF8F00',
            'secondary' => '#FFA000',
            'name' => 'Dark Gold'
        ],
        'dark_brown' => [
            'primary' => '#3E2723',
            'secondary' => '#5D4037',
            'name' => 'Dark Brown'
        ],
        'dark_gray' => [
            'primary' => '#263238',
            'secondary' => '#37474F',
            'name' => 'Dark Gray'
        ],
    ];
}

/**
 * Get a specific color preset by key
 * 
 * @param string $preset_key The key of the preset to retrieve
 * @return array|null The color preset data or null if not found
 */
function get_color_preset($preset_key) {
    $presets = get_color_presets();
    
    return isset($presets[$preset_key]) ? $presets[$preset_key] : null;
}

/**
 * Get all color presets grouped by category
 * 
 * @return array Presets grouped by category
 */
function get_color_presets_by_category() {
    $all_presets = get_color_presets();
    
    return [
        'material' => array_filter($all_presets, function($key) {
            return strpos($key, 'material_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'gradient' => array_filter($all_presets, function($key) {
            return strpos($key, 'gradient_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'flat' => array_filter($all_presets, function($key) {
            return strpos($key, 'flat_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'nature' => array_filter($all_presets, function($key) {
            return strpos($key, 'nature_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'vibrant' => array_filter($all_presets, function($key) {
            return strpos($key, 'vibrant_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'brand' => array_filter($all_presets, function($key) {
            return strpos($key, 'brand_') === 0 || $key === 'booknetic';
        }, ARRAY_FILTER_USE_KEY),
        
        'tech' => array_filter($all_presets, function($key) {
            return strpos($key, 'tech_') === 0;
        }, ARRAY_FILTER_USE_KEY),
        
        'dark' => array_filter($all_presets, function($key) {
            return strpos($key, 'dark_') === 0;
        }, ARRAY_FILTER_USE_KEY),
    ];
}
