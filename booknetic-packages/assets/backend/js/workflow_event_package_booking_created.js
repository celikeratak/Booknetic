(function ($)
{
    "use strict";

    $(document).ready(function()
    {

        $('.fs-modal').on('click', '#eventSettingsSave', function ()
        {
            var locale			    = $("#input_locale").val(),
                called_from         = $("#input_called_from").val();


            var data = new FormData();

            data.append('id', currentWorkflowID);
            data.append('locale', locale);
            data.append('called_from', called_from);

            booknetic.ajax( 'packages.workflow_event_package_booking_created_save', data, function()
            {
                booknetic.modalHide($(".fs-modal"));
            });
        });

    });

})(jQuery);