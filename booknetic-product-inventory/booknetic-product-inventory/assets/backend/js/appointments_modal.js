(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        let saveLastStateOfProductCheckboxes = {};

        let appointmentId = $('#add_new_JS').data('appointment-id');

        if( parseInt( appointmentId ) > 0 )
        {
            booknetic.ajax('inventory.get_appointment_products', { appointment: appointmentId }, function ( result )
            {
                let products = result['products'];

                for( let productId of products )
                {
                    saveLastStateOfProductCheckboxes[productId] = true;
                }

                reloadProductInventoryTab();
            });
        }
        else if( $('#input_service').val() > 0 )
        {
            reloadProductInventoryTab();
        }

        function reloadProductInventoryTab()
        {
            let serviceId = $('#input_service').val();

            booknetic.ajax('inventory.get_service_products', { service: serviceId }, function ( result )
            {
                let products = result['products'];

                $("#product-inventory-edit-tab").html( products.length > 0 ? '' : '---' );

                for( let product of products )
                {
                    let lastCheckboxState = (saveLastStateOfProductCheckboxes[product.id] || false) ? 'checked' : '';
                    let disabledState = !(saveLastStateOfProductCheckboxes[product.id] || false) && parseInt(product.quantity)<=0 ? 'disabled' : '';

                    $("#product-inventory-edit-tab").append(`
                        <div class="product-inventory-row-in-appointment-modal">
                            <input type="checkbox" id="product-inventory-${product.id}" value="${product.id}" class="product-inventory-checkbox-in-appointment-modal" ${lastCheckboxState} ${disabledState}>
                            <label for="product-inventory-${product.id}">${booknetic.htmlspecialchars( product.name )} [${product.quantity}]</label>
                        </div>
                    `);
                }
            });
        }

        $(".fs-modal").on('change', '#input_service', function ()
        {
            reloadProductInventoryTab();
        }).on('change', '#product-inventory-edit-tab .product-inventory-checkbox-in-appointment-modal', function ()
        {
            saveLastStateOfProductCheckboxes[$(this).val()] = $(this).is(':checked');
        });

        booknetic.addFilter( 'ajax_appointments.save_edited_appointment', function ( params )
        {
            let products = $("#product-inventory-edit-tab .product-inventory-checkbox-in-appointment-modal:checked").map((i,e)=>e.value).get();

            let cart = JSON.parse( params.get('cart') );
            cart[0]['products'] = products;

            params.set( 'cart', JSON.stringify( cart ) );

            return params;
        }, 'addon-product-inventory');

        booknetic.addFilter( 'ajax_appointments.create_appointment', function ( params )
        {
            let products = $("#product-inventory-edit-tab .product-inventory-checkbox-in-appointment-modal:checked").map((i,e)=>e.value).get();

            let cart = JSON.parse( params.get('cart') );
            cart[0]['products'] = products;

            params.set( 'cart', JSON.stringify( cart ) );

            return params;
        }, 'addon-product-inventory');
    });

})(jQuery);