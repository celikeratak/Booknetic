( ( $ ) => {
    'use strict'

    let doc = $( document );

    doc.ready( () => {
        doc.on( 'click', '#addBtn', addBtn );

        booknetic.dataTable.actionCallbacks['edit'] = editAction;
    } );

    /*-------------------FUNCTIONS-------------------*/

    function editAction( ids ) {
        booknetic.loadModal( 'add_new', { 'id': ids[ 0 ] } )
    }

    function addBtn()
    {
        booknetic.loadModal( 'add_new', {} );
    }
} )( jQuery );

