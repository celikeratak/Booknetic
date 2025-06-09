(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(document).on('click', '#addBtn', function()
		{
			booknetic.loadModal('add_new', {});
		});

		booknetic.dataTable.actionCallbacks['edit'] = function (ids)
		{
			booknetic.loadModal('add_new', {'id': ids[0]});
		};

		booknetic.dataTable.actionCallbacks['set_as_default'] = function (ids)
		{
			booknetic.ajax('set_as_default', {id: ids[0]}, function ()
			{
				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		}
	});

})(jQuery);