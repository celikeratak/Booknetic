(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        var checked = google_calendar_enabled === "on" ? 'checked' : '';
        $(".advanced_filters").before(`<div class="action_btn google_calendar">
                            <label for="input_show_google_calendar_events"><span>${ booknetic.__( 'google_calendar' ) }</span></label>
                            <div class="fs_onoffswitch">
                                <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_show_google_calendar_events" ` + checked + `>
                                <label class="fs_onoffswitch-label" for="input_show_google_calendar_events"></label>
                            </div>
                        </div>`);



        $("#input_show_google_calendar_events").on('change', function () {
            var show_google_calendar_events = $("#input_show_google_calendar_events").is(':checked')?'on':'off';

            booknetic.ajax( 'Googlecalendar.save_calendar_module_settings', { show_gc_events: show_google_calendar_events }, function ( result )
            {
                if( result.status === "ok" )
                {
                    reloadCalendarFn();
                }
            });
        })
    });

})(jQuery);