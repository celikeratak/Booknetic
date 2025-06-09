(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $(".fs-modal").on('click', '#savePaymentButton', function()
        {
            var total_amount	=	$(".fs-modal #input_total_amount").val(),
                paid_amount		=	$(".fs-modal #input_paid_amount").val(),
                status			=	$(".fs-modal #input_payment_status").val();

            var data = new FormData();

            data.append('id', $('#package_booking_edit_payment_JS').data('package-booking-id'));
            data.append('total_amount', total_amount);
            data.append('paid_amount', paid_amount);
            data.append('status', status);

            booknetic.ajax( 'package_bookings.save_payment', data, function()
            {
                booknetic.toast(booknetic.__('changes_saved'), 'success');
                booknetic.modalHide($(".fs-modal"));
                booknetic.dataTable.reload($("#fs_data_table_div"));
            });
        });

    });

})(jQuery);