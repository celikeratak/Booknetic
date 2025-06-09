(($) => {
	'use strict';

	$(document).ready(() => {
		$('.fs-modal').on('click', '#addRoleSave', function ()
		{
			let name = $("#input_name").val(),
				staff = $("#input_staff").val(),
				note = $("#input_note").val();

			let data = new FormData();
			let capabilities = {};

			$("#tab_permissions .form-group").each(function ()
			{
				if( $(this).find('.fs_onoffswitch-checkbox').length === 0)
					return;

				let permissionKey = $(this).find('.fs_onoffswitch-checkbox').attr('id').replace( 'input_permission_', '' );

				capabilities[permissionKey] = $(this).find('.fs_onoffswitch-checkbox').is(':checked') ? 'on' : 'off';
			});

			$("#tab_permissions .form-group select").each(function ()
			{
				let permissionKey = $(this).attr('id').replace( 'input_permission_', '' );

				capabilities[permissionKey] = $(this).val();
			});

			data.append( 'id', $(".fs-modal #add_new_JS").data('role-id') );
			data.append( 'name', name );
			data.append( 'staff', staff );
			data.append( 'note', note );
			data.append( 'capabilities', JSON.stringify( capabilities ) );

			booknetic.ajax( 'save_role', data, function()
			{
				booknetic.modalHide($(".fs-modal"));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		}).on('change', '.form-groups-list input[type="checkbox"]', function ()
		{
			let capability = $(this).closest('.form-groups-list').prev().find('input');
			let enabledChildren = $(this).closest('.form-groups-list').find('input[type="checkbox"]:checked').length;

			if (capability.length !== 0) {
				if ($(this).is(':checked') && !capability.is(':checked')) {
					capability.click();
				} else if (capability.is(':checked') && enabledChildren === 0) {
					capability.click();
				}

				return;
			}

			//none-all-my select box capabilities
			capability = $(this).closest('.form-groups-list').prev().find('select');

			if (capability.val() !== 'off' && enabledChildren === 0) {
				capability.val('off');
			}
		}).on('change', '#tab_permissions > .form-group input[type="checkbox"]', function ()
		{
			if( $(this).is(':checked') )
			{
				$(this).closest('.form-group').next('.form-groups-list').find('input:not(:checked)').click();
			}
			else
			{
				$(this).closest('.form-group').next('.form-groups-list').find('input:checked').click();
			}
		}).on('change', '#tab_permissions > .form-group select', function ()
		{
			let val = $(this).val();
			if( val !== 'off'){
				$(this).closest('.form-group').next('.form-groups-list').find('input:not(:checked)').click();
			}else{
				$(this).closest('.form-group').next('.form-groups-list').find('input:checked').click();
			}
		}).on('change', '#input_permission_staff', function () {
			const container = $('.children-container-staff');
			const children = container.find('.child-container:not(#child_permission_staff_edit)');

			if ($(this).val() === 'my') {
				children.each((t, e) => $(e).fadeOut('slow'));
				children.find('input:checked').click();

				return;
			}

			children.each((t, e) => $(e).fadeIn('slow'));
		});

		$(".fs-modal #input_staff").select2({
			theme:			'bootstrap',
			placeholder:	booknetic.__('select'),
			allowClear:		true
		});

		//This line and the related DOM event adds the functionality to hide the unrelated permissions on 'my' option on staff.
		//Current structure of the addon doesn't allow such a thing, thus this method is added.
		$('#input_permission_staff').trigger('change');
	});
})(jQuery);
