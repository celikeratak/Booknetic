( function ( $ ) {
	'use strict';

	$( document ).ready( function () {
		$( document ).on( 'click', '#fs_data_table_div td[data-column="usage_history"]', function () {
			let giftcarId = $( this ).closest( 'tr' ).data( 'id' );

			booknetic.loadModal( 'Giftcards.giftcard_usage_history', { id: giftcarId } , {'width': "800px"} );
		} ).on( 'click', 'td[data-column="appointment_info"]', function () {
			let appointmentID = $( this ).data( 'appointment-id' );

			booknetic.loadModal( 'appointments.info', { id: appointmentID } );
		} );
	} );
} )( jQuery );

