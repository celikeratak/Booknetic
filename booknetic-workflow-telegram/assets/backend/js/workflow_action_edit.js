( function ( $ ) {
	'use strict';

	$( document ).ready( function () {
		$( '.fs-modal' ).on( 'click', '#saveWorkflowActionBtn', function () {
			saveTelegram();
		}).on('click', '#saveAndTestWorkflowActionBtn', function ()
		{
			saveTelegram(function ()
			{
				booknetic.modal('<div class="p-3 pt-5 pb-5">' +
					'<div class="mb-2">' +
					'<input class="form-control" id="send_test_telegram_to" placeholder="'+booknetic.__('To')+'">' +
					'</div>' +
					'<div class="d-flex justify-content-center">' +
					'<button type="button" class="btn btn-lg btn-default mr-1" data-dismiss="modal">'+booknetic.__('CLOSE')+'</button>' +
					'<button type="button" class="btn btn-lg btn-success" id="send_test_btn">'+booknetic.__('SEND')+'</button>' +
					'</div>' +
					'</div>', {type: 'center'});

				$('#send_test_btn').click(function ()
				{
					let modal = $(this).closest( '.modal' );

					booknetic.ajax( 'telegram_bot_workflow.workflow_action_send_test_data', { id: workflow_action_id, to: $('#send_test_telegram_to').val()}, function ()
					{
						booknetic.modalHide( modal );
					} );
				});
			});
		});

		function saveTelegram( callback )
		{
			let data	= new FormData();
			let to		= $( '#input_to' ).val();
			let documents		= $( '#input_documents' ).val();
			let is_active = $("#input_is_active").is(':checked') ? 1 : 0;

			data.append('id',		workflow_action_id );
			data.append('to',		to );
			data.append('body',	booknetic.summernoteReplace( $( '#input_body' ) ) );
			data.append('documents',	documents );
			data.append('is_active', is_active);

			booknetic.ajax( 'telegram_bot_workflow.workflow_action_save_data', data, function () {
				if( typeof callback !== 'undefined' )
				{
					callback();
				}
				else
				{
					booknetic.modalHide($(".fs-modal"));
					booknetic.reloadActionList();
				}
			} );
		}

		$( '#input_to' ).select2( {
			tokenSeparators: [ ',' ],
			theme: 'bootstrap',
			tags: true,
		} );

		$( '#input_documents' ).select2( {
			tokenSeparators: [ ',' ],
			theme: 'bootstrap',
			tags: true,
		});

		booknetic.summernote(
			$( '#input_body' ),
			[
				[ 'style', [ 'bold', 'italic', 'underline', 'clear' ] ],
				[ 'insert', [ 'link' ] ],
			],
			workflow_telegram_action_all_shortcodes_obj
		);
	} );
})(jQuery);