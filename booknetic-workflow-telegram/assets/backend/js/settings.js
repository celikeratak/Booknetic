( function ( $ ) {
    'use strict';

    $( document ).ready( function () {
        $( '#booknetic_settings_area' ).on( 'click', '.settings-save-btn', function () {
			let data				= new FormData();
            let telegram_bot_token 	= $( '#input_telegram_bot_token' ).val();

            data.append( 'telegram_bot_token', telegram_bot_token );

            booknetic.ajax( 'telegram_bot.save_settings', data, function () {
                booknetic.toast( booknetic.__( 'saved_successfully' ), 'success' );
            } );
        } );
    } );

})(jQuery);