(function($)
{
    "use strict";

    bookneticHooks.addAction( 'customer_panel_loaded', function ( booknetic )
    {
        let customer_panel_js = booknetic.panel_js;

        customer_panel_js.find('.booknetic-cp-tabs > button[data-target="#booknetic-tab-appointments"]').after(`
            <button class="booknetic-cp-tab-item" data-target="#booknetic-tab-packages" type="button">
                <i class="fa-solid fa-box-open"></i>
                <div>
                    <span class="booknetic-cp-tab-item-name">Packages</span>
                </div>
            </button>
        `);

        customer_panel_js.find('.booknetic-cp-tab-wrapper').append(`<div class="booknetic-cp-tab" id="booknetic-tab-packages">loading...</div>`);
        let packages_tab = customer_panel_js.find('#booknetic-tab-packages');

        booknetic.ajax('packagesCustomerPanelGetPackageBookings', {}, function ( result )
        {
            packages_tab.html( booknetic.htmlspecialchars_decode( result['html'] ) );
        });

        customer_panel_js.on('click', '.booknetic_manage_package_btn', function ()
        {
            let packageBookingId = $(this).data('package-booking-id');

            booknetic.ajax('packagesCustomerPanelManagePackageBooking', {id: packageBookingId}, function ( result )
            {
                packages_tab.find('.booknetic-packages-list').fadeOut(200, function ()
                {
                    packages_tab.find('.booknetic-packages-manage-package').html( booknetic.htmlspecialchars_decode( result['html'] ) ).fadeIn(200);
                });
            });
        }).on('click', '.booknetic-packages-manage-package-backbtn', function ()
        {
            booknetic.ajax('packagesCustomerPanelGetPackageBookings', {}, function ( result )
            {
                packages_tab.find('.booknetic-packages-manage-package').empty().fadeOut(200, function ()
                {
                    packages_tab.html( booknetic.htmlspecialchars_decode( result['html'] ) );
                    packages_tab.find('.booknetic-packages-list').hide().fadeIn(200);
                });
            });


        }).on('click', '.booknetic-package-schedule-now', function ()
        {
            let packageBookingId    = $(this).data('package_booking_id');
            let serviceId           = $(this).data('package_service_id');
            let slotId              = $(this).data('package_booking_slot');

            booknetic.ajax( 'packagesLoadBookingPanel', {
                package_booking_id: packageBookingId,
                service_id: serviceId
            }, function ( result )
            {
                let bpanelNode = $(`
                    <div class="booknetic_packages_booking_panel_modal" style="position: fixed; background: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; top: 0; left: 0; display: flex; align-items: center; justify-content: center; z-index: 9999999;">
                        ${result['html']}
                    </div>
                `);

                $(document.body).css('overflow', 'hidden');
                let bpanel = bpanelNode.find(".booknetic_appointment:eq(0)");

                bpanel.append('<div class="booknetic_close_booking_panel" style="position: absolute; right: -15px; top: -15px; background: #FFF;width: 30px;height: 30px;display: flex;align-items: center;justify-content: center;font-size: 14px;font-weight: 600;border-radius: 50px;cursor: pointer;">X</div>');
                bpanel.find('.booknetic_close_booking_panel').on('click', function ()
                {
                    $(this).closest('.booknetic_packages_booking_panel_modal').fadeOut(200, function ()
                    {
                        $(this).remove();
                        $(document.body).css('overflow', 'auto');
                    });
                });

                let steps = bpanel.data('steps');
                for( let s in steps )
                {
                    if( steps[s]['id'] === 'cart' )
                    {
                        steps[s]['hidden'] = true;
                    }
                }

                bpanel.data('tenant_id', result['tenant_id']);
                bpanel.data('steps', steps);
                bpanel.data('package_booking_id', packageBookingId);
                bpanel.data('package_booking_slot', slotId);
                bpanel.data('package_on_finish', function ()
                {
                    booknetic.ajax('packagesCustomerPanelManagePackageBooking', {id: packageBookingId}, function ( result )
                    {
                        packages_tab.find('.booknetic-packages-manage-package').html( booknetic.htmlspecialchars_decode( result['html'] ) );
                    });
                    booknetic.loadAppointmentsList();
                });

                $('body').append( bpanelNode );
            });
        }).on('click', '.booknetic_pay_package_btn', function()
        {
            const packageBookingId = $(this).attr('data-package-booking-id'),
                  paymentMethod = $(this).closest().attr('data-payment-method');

            customer_panel_js.find( '#booknetic_packages_pay_now_popup' )
                .attr( 'data-package-booking-id', packageBookingId )
                .attr('data-payment-method', paymentMethod)
                .removeClass('booknetic_hidden').hide().fadeIn( 200 );

            booknetic.select2Ajax( packages_tab.find(".booknetic_packages_pay_now_popup_select"), 'getAllowedPaymentGateways', function()
            {
                return {
                    id: packages_tab.find('#booknetic_packages_pay_now_popup').attr( 'data-package-booking-id'),
                }
            });

        }).on('click', '.booknetic_packages_pay_now_popup_confirm', function()
        {
            const packageBookingId = customer_panel_js.find('#booknetic_packages_pay_now_popup').attr('data-package-booking-id'),
                  paymentMethod = packages_tab.find('.booknetic_packages_pay_now_popup_select').val();

            bookneticHooks.doAction('before_processing_payment', paymentMethod, {
                source: 'customer_panel_pay_package_booking',
                packageBookingId: packageBookingId,
                booknetic
            });

            booknetic.ajax('packagesCustomerPanelPayPackageBooking', {packageBookingId, paymentMethod}, function ( result ) {
                bookneticHooks.doAction('after_processing_payment', paymentMethod, true, result, booknetic);
            }, true, function( result ) {
                bookneticHooks.doAction('after_processing_payment', paymentMethod, false, result, booknetic);
            });
        });

        bookneticHooks.addAction('payment_completed', function ( status, data )
        {
            if( !data || !( typeof data['source'] !== 'undefined' && data['source'] === 'customer_panel_pay_package_booking' ) ) {
                return;
            }

            if( status )
            {
                packages_tab.find('.booknetic_pay_now_popup_cancel').trigger('click');

                booknetic.ajax('packagesCustomerPanelManagePackageBooking', {id: data['packageBookingId']}, function ( result )
                {
                    packages_tab.find('.booknetic-packages-list').fadeOut(200, function ()
                    {
                        packages_tab.find('.booknetic-packages-manage-package').html( booknetic.htmlspecialchars_decode( result['html'] ) ).fadeIn(200);
                    });
                });
            }
            else
            {
                booknetic.toast( 'Payment error! Please try again.', 'unsuccess' );
            }
        });
    });


})(jQuery);