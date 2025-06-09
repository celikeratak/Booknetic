(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        booknetic.addFilter( 'ajax_staff.save_staff', function ( params ) {
            let google_calendar_id = $("#google_calendar_select").val();
            let sync_previous_appointments = $("#sync-previous-appointments").is( ':checked' ) ? '1' : '0';

            params.append( 'google_calendar_id', google_calendar_id ? google_calendar_id : '' );
            params.append( 'sync_previous_appointments', sync_previous_appointments );

            return params;
        } );
    });

})(jQuery);