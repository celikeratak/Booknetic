(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		$('.fs-modal').on('click', '#addPackageBookingSave', function ()
		{
			var packageId	= $("#input_packages").val(),
				customerId	= $("#input_customer").val(),
				note		= $("#input_note").val();

			var data = new FormData();

			data.append('id', $('#package_bookings_add_new_JS').data('package-booking-id'));
			data.append('package', packageId);
			data.append('customer', customerId);
			data.append('note', note);

			booknetic.ajax( 'save_package_booking', data, function()
			{
				booknetic.modalHide($(".fs-modal"));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		})

		$('#input_packages').select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});

		booknetic.select2Ajax( $("#input_customer"), 'get_customers'  );

	});

})(jQuery);