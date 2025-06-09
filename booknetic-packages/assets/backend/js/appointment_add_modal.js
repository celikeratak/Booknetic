(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        let pacakgeBookingId = $('#package_booking_appointment_modal_JS').data('package_booking_id'),
            pacakgeBookingSlot = $('#package_booking_appointment_modal_JS').data('package_booking_slot'),
            preselectData = $('#package_booking_appointment_modal_JS').data('preselect');

        if( pacakgeBookingId > 0 && pacakgeBookingSlot > -1 )
        {
            booknetic.addFilter( 'ajax_appointments.create_appointment', function ( params )
            {
                params.append( 'package_booking_id', pacakgeBookingId);
                params.append( 'package_booking_slot', pacakgeBookingSlot);

                return params;
            }, 'addon-packages');

            let serviceOption = new Option(preselectData['service']['text'], preselectData['service']['id'], true, true);
            let categoryOption = new Option(preselectData['category']['text'], preselectData['category']['id'], true, true);
            let customerOption = new Option(preselectData['customer']['text'], preselectData['customer']['id'], true, true);

            $(".fs-modal .input_category").append(categoryOption);
            $(".fs-modal .input_category").select2('data', preselectData['category']);
            $(".fs-modal .input_category").attr('disabled', true).trigger('change');

            $(".fs-modal #input_service").append(serviceOption);
            $(".fs-modal #input_service").select2('data', preselectData['service']);
            $(".fs-modal #input_service").attr('disabled', true).trigger('change');

            $(".fs-modal .input_customer").append(customerOption);
            $(".fs-modal .input_customer").select2('data', preselectData['customer']);
            $(".fs-modal .input_customer").attr('disabled', true).trigger('change');

        }

    });

})(jQuery);