// Color Presets Functionality
jQuery(document).ready(function($) {
    // Get reference to the updateColorPreview function from the parent scope
    var updateColorPreviewFunc = window.updateColorPreview || updateColorPreview;
    
    // Handle color swatch clicks
    $(document).on('click', '.color-swatch', function() {
        const primaryColor = $(this).data('primary');
        const secondaryColor = $(this).data('secondary');
        
        // Update input fields
        $('#primaryColor').val(primaryColor);
        $('#secondaryColor').val(secondaryColor);
        
        // Update color pickers
        $('#primaryColorPicker').val(primaryColor);
        $('#secondaryColorPicker').val(secondaryColor);
        
        // Update preview
        if (typeof updateColorPreviewFunc === 'function') {
            updateColorPreviewFunc();
        }
        
        // Add active class to selected swatch
        $('.color-swatch').removeClass('active');
        $(this).addClass('active');
    });
    
    // Handle preset dropdown change
    $('#colorPreset').on('change', function() {
        var preset = $(this).val();
        var primaryColor, secondaryColor;
        
        // Update active swatch
        $('.color-swatch').removeClass('active');
        $('.color-swatch[data-preset="' + preset + '"]').addClass('active');
        
        switch (preset) {
            // Material Design Colors
            case 'material_red':
                primaryColor = '#F44336';
                secondaryColor = '#E57373';
                break;
            case 'material_pink':
                primaryColor = '#E91E63';
                secondaryColor = '#F06292';
                break;
            case 'material_purple':
                primaryColor = '#9C27B0';
                secondaryColor = '#BA68C8';
                break;
            case 'material_deep_purple':
                primaryColor = '#673AB7';
                secondaryColor = '#9575CD';
                break;
            case 'material_indigo':
                primaryColor = '#3F51B5';
                secondaryColor = '#7986CB';
                break;
            case 'material_blue':
                primaryColor = '#2196F3';
                secondaryColor = '#64B5F6';
                break;
            case 'material_light_blue':
                primaryColor = '#03A9F4';
                secondaryColor = '#4FC3F7';
                break;
            case 'material_cyan':
                primaryColor = '#00BCD4';
                secondaryColor = '#4DD0E1';
                break;
            case 'material_teal':
                primaryColor = '#009688';
                secondaryColor = '#4DB6AC';
                break;
            case 'material_green':
                primaryColor = '#4CAF50';
                secondaryColor = '#81C784';
                break;
            case 'material_light_green':
                primaryColor = '#8BC34A';
                secondaryColor = '#AED581';
                break;
            case 'material_lime':
                primaryColor = '#CDDC39';
                secondaryColor = '#DCE775';
                break;
            case 'material_amber':
                primaryColor = '#FFC107';
                secondaryColor = '#FFD54F';
                break;
            case 'material_orange':
                primaryColor = '#FF9800';
                secondaryColor = '#FFB74D';
                break;
            case 'material_deep_orange':
                primaryColor = '#FF5722';
                secondaryColor = '#FF8A65';
                break;
            case 'material_brown':
                primaryColor = '#795548';
                secondaryColor = '#A1887F';
                break;
            case 'material_grey':
                primaryColor = '#9E9E9E';
                secondaryColor = '#E0E0E0';
                break;
            case 'material_blue_grey':
                primaryColor = '#607D8B';
                secondaryColor = '#90A4AE';
                break;
                
            // Flat UI Colors
            case 'flat_turquoise':
                primaryColor = '#1abc9c';
                secondaryColor = '#16a085';
                break;
            case 'flat_emerald':
                primaryColor = '#2ecc71';
                secondaryColor = '#27ae60';
                break;
            case 'flat_peter_river':
                primaryColor = '#3498db';
                secondaryColor = '#2980b9';
                break;
            case 'flat_amethyst':
                primaryColor = '#9b59b6';
                secondaryColor = '#8e44ad';
                break;
            case 'flat_wet_asphalt':
                primaryColor = '#34495e';
                secondaryColor = '#2c3e50';
                break;
            case 'flat_green_sea':
                primaryColor = '#16a085';
                secondaryColor = '#1abc9c';
                break;
            case 'flat_nephritis':
                primaryColor = '#27ae60';
                secondaryColor = '#2ecc71';
                break;
            case 'flat_belize_hole':
                primaryColor = '#2980b9';
                secondaryColor = '#3498db';
                break;
            case 'flat_wisteria':
                primaryColor = '#8e44ad';
                secondaryColor = '#9b59b6';
                break;
            case 'flat_midnight_blue':
                primaryColor = '#2c3e50';
                secondaryColor = '#34495e';
                break;
            case 'flat_sunflower':
                primaryColor = '#f1c40f';
                secondaryColor = '#f4d03f';
                break;
            case 'flat_carrot':
                primaryColor = '#e67e22';
                secondaryColor = '#f39c12';
                break;
            case 'flat_alizarin':
                primaryColor = '#e74c3c';
                secondaryColor = '#c0392b';
                break;
            case 'flat_clouds':
                primaryColor = '#ecf0f1';
                secondaryColor = '#bdc3c7';
                break;
            case 'flat_concrete':
                primaryColor = '#95a5a6';
                secondaryColor = '#7f8c8d';
                break;
            case 'flat_orange':
                primaryColor = '#f39c12';
                secondaryColor = '#e67e22';
                break;
            case 'flat_pumpkin':
                primaryColor = '#d35400';
                secondaryColor = '#e67e22';
                break;
            case 'flat_pomegranate':
                primaryColor = '#c0392b';
                secondaryColor = '#e74c3c';
                break;
            case 'flat_silver':
                primaryColor = '#bdc3c7';
                secondaryColor = '#95a5a6';
                break;
            case 'flat_asbestos':
                primaryColor = '#7f8c8d';
                secondaryColor = '#95a5a6';
                break;
                
            // Gradient Colors
            case 'gradient_sunset':
                primaryColor = '#FF512F';
                secondaryColor = '#F09819';
                break;
            case 'gradient_ocean':
                primaryColor = '#2193b0';
                secondaryColor = '#6dd5ed';
                break;
            case 'gradient_forest':
                primaryColor = '#43e97b';
                secondaryColor = '#38f9d7';
                break;
            case 'gradient_candy':
                primaryColor = '#FC466B';
                secondaryColor = '#3F5EFB';
                break;
            case 'gradient_royal':
                primaryColor = '#141E30';
                secondaryColor = '#243B55';
                break;
            case 'gradient_crimson':
                primaryColor = '#ED213A';
                secondaryColor = '#93291E';
                break;
            case 'gradient_purple':
                primaryColor = '#8E2DE2';
                secondaryColor = '#4A00E0';
                break;
            case 'gradient_sky':
                primaryColor = '#1488CC';
                secondaryColor = '#2B32B2';
                break;
            case 'gradient_mojito':
                primaryColor = '#1D976C';
                secondaryColor = '#93F9B9';
                break;
            case 'gradient_cherry':
                primaryColor = '#EB3349';
                secondaryColor = '#F45C43';
                break;
                
            // Default
            default:
                primaryColor = '#4050B5';
                secondaryColor = '#6C757D';
        }

        // Update input fields
        $('#primaryColor').val(primaryColor);
        $('#secondaryColor').val(secondaryColor);
        
        // Update color pickers
        $('#primaryColorPicker').val(primaryColor);
        $('#secondaryColorPicker').val(secondaryColor);
        
        // Update preview
        if (typeof updateColorPreviewFunc === 'function') {
            updateColorPreviewFunc();
        }
    });
});
