(function ( $ )
{
    'use strict';

    $( document ).ready( function ()
    {
        $( '#addHeaders' ).on( 'click', function ()
        {
            $( '.webhook-headers' ).last().after( $( '#headersClone' ).clone().removeAttr( 'style' ).removeAttr( 'id' )[ 0 ].outerHTML );
            $( '.webhook-headers:last input:last' ).data('keywords', $( '#headersClone input:last' ).data('keywords'));
        } );

        $( '#addBody' ).on( 'click', function ()
        {
            $( '.webhook-body' ).last().after( $( '#bodyClone' ).clone().removeAttr( 'style' ).removeAttr( 'id' )[ 0 ].outerHTML );
            $( '.webhook-body:last input:last' ).data('keywords', $( '#bodyClone input:last' ).data('keywords'));
        } );

        $( document ).on( 'click', '.remove-row', function ()
        {
            $( this ).parent().remove();
        } );

        $( '#input_request_method' ).on( 'change', function ()
        {
            let val = $( this ).val();

            if ( val === 'POST' || val === 'PUT' )
            {
                $( '#bodyContainer' ).removeClass( 'd-none' );
            }
            else
            {
                $( '#bodyContainer' ).addClass( 'd-none' );
            }
        } );

        $( '#input_content_type' ).on( 'change', function ()
        {
            let val = $( this ).val();

            if ( val === 'FORM_DATA' )
            {
                $( '#dataContainer' ).removeClass( 'd-none' );
                $( '#jsonContainer' ).addClass( 'd-none' );
            }
            else if ( val === 'JSON' )
            {
                $( '#dataContainer' ).addClass( 'd-none' );
                $( '#jsonContainer' ).removeClass( 'd-none' );
            }
            else
            {
                $( '#dataContainer' ).addClass( 'd-none' );
                $( '#jsonContainer' ).addClass( 'd-none' );
            }
        } );

        function saveWebhook( callback )
        {
            let request_method = $( '#input_request_method' ).val(),
                url            = $( '#input_url' ).val(),
                content_type   = $( '#input_content_type' ).val(),
                is_active      = $( '#input_is_active' ).is( ':checked' ) ? 1 : 0;

            let data = new FormData();

            data.append( 'id', workflow_action_id );
            data.append( 'is_active', is_active );
            data.append( 'request_method', request_method );
            data.append( 'url', url );
            data.append( 'content_type', content_type );

            $( '.webhook-headers' ).each( function ( i, el )
            {
                let k = $( el ).find( 'input:first' ).val();
                let v = $( el ).find( 'input:last' ).val();

                if ( k.length > 0 && v.length > 0 )
                {
                    data.append( 'headers[' + k + ']', v );
                }
            } );

            if ( request_method === 'POST' || request_method === 'PUT' )
            {
                if ( content_type === 'FORM_DATA' )
                {
                    $( '.webhook-body' ).each( function ( i, el )
                    {
                        let k = $( el ).find( 'input:first' ).val();
                        let v = $( el ).find( 'input:last' ).val();

                        if ( k.length > 0 && v.length > 0 )
                        {
                            data.append( 'body[' + k + ']', v );
                        }
                    } );
                }
                else if ( content_type === 'JSON' )
                {
                    data.append( 'body', $( '#jsonBody' ).val() );
                }
            }

            booknetic.ajax( 'webhook_workflow.workflow_action_save_data', data, function ()
            {
                if( typeof callback !== 'undefined' )
                {
                    callback();
                }
                else
                {
                    booknetic.modalHide( $( '.fs-modal' ) );

                    booknetic.reloadActionList();
                }
            } );
        }

        $( '.fs-modal' ).on( 'click', '#saveWorkflowActionBtn', function ()
        {
            saveWebhook();
        } ).on('click', '#saveAndTestWorkflowActionBtn', function ()
        {
            saveWebhook(function ()
            {
                booknetic.modal('<div class="p-3 pt-5 pb-5">' +
                    '<div class="mb-2">' +
                        '<input class="form-control" id="send_test_url_webhook" placeholder="https://">' +
                    '</div>' +
                    '<div class="d-flex justify-content-center">' +
                        '<button type="button" class="btn btn-lg btn-default mr-1" data-dismiss="modal">'+booknetic.__('CLOSE')+'</button>' +
                        '<button type="button" class="btn btn-lg btn-success" id="send_test_btn">'+booknetic.__('SEND')+'</button>' +
                    '</div>' +
                '</div>', {type: 'center'});

                $('#send_test_url_webhook').val( $('#input_url').val() );

                $('#send_test_btn').click(function ()
                {
                    let modal = $(this).closest( '.modal' );

                    booknetic.ajax( 'webhook_workflow.workflow_action_send_test_data', { id: workflow_action_id, url: $('#send_test_url_webhook').val()}, function ()
                    {
                        booknetic.modalHide( modal );
                    } );
                });
            });
        });

        booknetic.initKeywordsInput( $( '#input_url' ), workflow_email_action_all_shortcodes_obj );
        booknetic.initKeywordsInput( $( '#headersClone input:last' ), workflow_email_action_all_shortcodes_obj );
        booknetic.initKeywordsInput( $( '#bodyClone input:last' ), workflow_email_action_all_shortcodes_obj );

        $('.webhook-headers, .webhook-body').each(function ()
        {
            booknetic.initKeywordsInput( $(this).find('input:last'), workflow_email_action_all_shortcodes_obj );
        } );

        booknetic.initKeywordsInput( $( '#jsonBody' ), workflow_email_action_all_shortcodes_obj );

    } );

})( jQuery );