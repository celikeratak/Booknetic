(function($)
{
    "use strict";

    bookneticHooks.addFilter('ajax', function ( params, booknetic )
    {
        let booking_panel_js = booknetic.panel_js;

        if( booking_panel_js.data('package_booking_id') === undefined || booking_panel_js.data('package_booking_slot') === undefined )
            return params;

        if( params instanceof FormData )
        {
            params.append('package_booking_id', booking_panel_js.data('package_booking_id'));
            params.append('package_booking_slot', booking_panel_js.data('package_booking_slot'));
        }
        else
        {
            params['package_booking_id'] = booking_panel_js.data('package_booking_id');
            params['package_booking_slot'] = booking_panel_js.data('package_booking_slot');
        }

        return params;
    });

    bookneticHooks.addAction('booking_finished_successfully', function (booknetic)
    {
        let booking_panel_js = booknetic.panel_js;

        if( booking_panel_js.data('package_booking_id') === undefined || booking_panel_js.data('package_booking_slot') === undefined )
            return;

        let callbackFn = booking_panel_js.data('package_on_finish');

        // doit redirect_users_on_confirm olanda redirect atacag ama. prevent redirect ishleyermi?
        booking_panel_js.closest('.booknetic_packages_booking_panel_modal').remove();

        if( typeof callbackFn === 'function' )
        {
            callbackFn();
        }
    });

})(jQuery);