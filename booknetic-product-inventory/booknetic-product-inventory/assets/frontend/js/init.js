(($) => {
    const doc = $(document);

    doc.ready(() => {
        bookneticHooks.addFilter('bkntc_cart', (obj, booknetic) => {
            const products = [];

            booknetic.panel_js.find('.booknetic_appointment_container_body [data-step-id="service_extras"] .booknetic-products-container .booknetic_service_extra_card_selected')
                 .map((i, e) => {
                     products.push($(e).data('id'));
                 });

            obj['products'] = products;

            return obj;
        });

        bookneticHooks.addAction('loaded_step_service_extras', (booknetic) => {
            booknetic.panel_js.on('change', '.booknetic-product-item-checkbox', function () {
                const checked = $(this).is(':checked');
                const container = $(this).parent();

                if (checked) {
                    container.addClass('booknetic_service_extra_card_selected');
                    return;
                }

                container.removeClass('booknetic_service_extra_card_selected');
            });
        });

    });
})(jQuery)