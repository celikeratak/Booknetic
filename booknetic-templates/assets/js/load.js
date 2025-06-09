( ( $ ) => {
    let doc = $( document );

    doc.ready( () => {
       booknetic.loadModal( 'templates.get_selection_modal', {}, {
           type: 'center',
           width: 80,
       } );
    } );

} )( jQuery )
