( ( $ ) => {
    let doc = $( document );

    doc.ready( () => {
        $( '.fs-modal' )
            .on( 'click', '.template_picture img', () => $( '#input_image' ).click() )
            .on( 'change', '#input_image', imageChange )
            .on( 'click', '#save', save )
            .on( 'change', '#col_locations', locationsChange )
            .on( 'change', '#col_staff', staffChange )
            .on( 'change', '#tenant', tenantChange );

        //trigger this event to enable/disable dependent cols
        $( '#col_locations' ).trigger( 'change' );

        let tenant = $( '#tenant' );

        if ( tenant.length > 0 )
        {
            booknetic.select2Ajax( tenant, 'get_tenants' );
        }
    } );

    /*-------------------FUNCTIONS-------------------*/

    function imageChange()
    {
        let files = $( this )[ 0 ].files;

        if( ! files || ! files[ 0 ] )
            return;

        let reader = new FileReader();

        reader.onload = ( e ) => {
            //update the source attribute of the img element with the loaded file
            $( '.fs-modal .template_picture img' ).attr( 'src', e.target.result );
        }

        reader.readAsDataURL( files[ 0 ] );
    }

    function save()
    {
        let name        = $( '#name' ).val();
        let description = $( '#description' ).val();
        let isDefault   = $( '#default' ).is( ':checked' ) ? 1 : 0;
        let image       = $( '.fs-modal #input_image' )[ 0 ].files[ 0 ];
        let columns     = {}

        if ( name.length === 0 )
        {
            booknetic.toast(  booknetic.__( 'empty_template_name' ), 'unsuccess' );
            return;
        }

        $( '.template-data-column' ).each( function() {
            columns[ $( this ).data( 'key' ) ] = $( this ).is( ':checked' );
        } );

        let data = new FormData();

        data.append( 'name', name );
        data.append( 'default', isDefault );
        data.append( 'description', description );
        data.append( 'image', image );
        data.append( 'columns', JSON.stringify( columns ) );

        let finish = () => {
            booknetic.modalHide( $( '.fs-modal' ) );

            booknetic.dataTable.reload( $( '#fs_data_table_div' ) );
        }

        let id = $( '#add_new_JS' ).data( 'template-id' );

        if ( id.length === 0 )
        {
            data.append( 'tenant', $( '#tenant' ).val() );
            booknetic.ajax('create', data, null, true );
            finish();

            return;
        }

        data.append( 'id', id );
        booknetic.ajax('update', data, null, true );
        finish();
    }

    function locationsChange()
    {
        let on = $( this ).is( ':checked' );

        if ( on )
            return;

        $( '#col_staff' ).prop( 'checked', '' );
    }

    function staffChange()
    {
        let on = $( this ).is( ':checked' );

        if ( ! on )
            return;

        $( '#col_locations' ).prop( 'checked', true );
    }

    function tenantChange()
    {
        let id = $( this ).val();

        if ( id == null )
        {
            $( `.tenant-data-field-label b` ).text( '' );
            return;
        }

        booknetic.ajax( 'get_tenant_data_count', { id }, ( { counts } ) => {
            $.each( counts, ( k, v ) => {
                $( `label[for="col_${k}"] b` ).text( `(${v})` );
            } )
        }, true );
    }
} )( jQuery )