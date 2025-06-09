(function($)
{
    "use strict";

    let createdPackageData;

    bookneticHooks.addAction('booking_panel_loaded', function ( booknetic )
    {
        let booking_panel_js = booknetic.panel_js;

        booking_panel_js.on('click', '.booknetic_package_card', function ()
        {
            booking_panel_js.data('package_id', $(this).data('package-id'));

            booking_panel_js.find('.booknetic_appointment_container_body [data-step-id]').empty();

            booking_panel_js.find('.booknetic_appointment_steps_body .booknetic_appointment_step_element:not(.booknetic_menu_hidden)').addClass('booknetic_package_save_for_revert').addClass('booknetic_menu_hidden').hide();

            booking_panel_js.find('.booknetic_appointment_steps_body').append(`
                <div class="booknetic_appointment_step_element booknetic_package_step booknetic_selected_step" data-step-id="packages_services" data-loader="" data-title="${booknetic.__('Services')}">
                    <span class="booknetic_badge"></span>
                    <span class="booknetic_step_title"> ${booknetic.__('Service')}</span>
                </div>
            `);
            booking_panel_js.find('.booknetic_appointment_steps_body').append(`
                <div class="booknetic_appointment_step_element booknetic_package_step" data-step-id="packages_information" data-loader="" data-title="${booknetic.__('Fill information')}">
                    <span class="booknetic_badge"></span>
                    <span class="booknetic_step_title"> ${booknetic.__('Information')}</span>
                </div>
            `);
            booking_panel_js.find('.booknetic_appointment_steps_body').append(`
                <div class="booknetic_appointment_step_element booknetic_package_step" data-step-id="packages_confirm_details" data-loader="" data-title="${booknetic.__('Confirmation')}">
                    <span class="booknetic_badge"></span>
                    <span class="booknetic_step_title"> ${booknetic.__('Confirmation')}</span>
                </div>
            `);

            booking_panel_js.find('.booknetic_appointment_container_body').append(`<div data-step-id="packages_services" class="booknetic_hidden booknetic_package_step_body"></div>`)
            booking_panel_js.find('.booknetic_appointment_container_body').append(`<div data-step-id="packages_information" class="booknetic_hidden booknetic_package_step_body"></div>`)
            booking_panel_js.find('.booknetic_appointment_container_body').append(`<div data-step-id="packages_confirm_details" class="booknetic_hidden booknetic_package_step_body"></div>`)

            /**
             * remove edir, chunki core-da o classa click verilibdi. Packageden back edib geri qayidanda, tezeden bu classi qaytarir.
             */
            booking_panel_js.find('.booknetic_confirm_booking_btn').removeClass('booknetic_next_step');

            booknetic.fadeInAnimate('.booknetic_appointment_step_element:not(.booknetic_menu_hidden)', function () {
                booknetic.stepManager.refreshStepNumbers();
            });

            booknetic.stepManager.loadStep('packages_information');
        });

        booking_panel_js.on('click', '.booknetic_confirm_booking_btn', function ()
        {
            if( !( booking_panel_js.data('package_id') > 0 ) )
                return;

            let form_data = {};

            let formSection = booking_panel_js.find(".booknetic_appointment_container_body [data-step-id=\"packages_information\"]");
            formSection.find('input[name]#bkntc_input_name, input[name]#bkntc_input_surname, input[name]#bkntc_input_email, input[name]#bkntc_input_phone').each(function()
            {
                var name	= $(this).attr('name'),
                    value	= name == 'phone' && typeof intlTelInputUtils != 'undefined' ? $(this).data('iti').getNumber(intlTelInputUtils.numberFormat.E164) : $(this).val();

                if ( name === 'email' )
                    value = value.trim()

                form_data[name] = value;
            });

            let payment_method;
            if( booking_panel_js.find('.booknetic_appointment_step_element[data-step-id="packages_confirm_details"]').hasClass('booknetic_menu_hidden') )
            {
                payment_method = 'local';
            }
            else
            {
                payment_method = booking_panel_js.find('.booknetic_payment_method.booknetic_payment_method_selected').attr('data-payment-type');
            }

            var data = new FormData();
            data.append( 'package_id', booking_panel_js.data('package_id') );
            data.append( 'customer_data', JSON.stringify(form_data) );
            data.append( 'payment_method', payment_method );
            data.append( 'client_time_zone', booknetic.timeZoneOffset() );

            data.append( 'google_recaptcha_token', booknetic.google_recaptcha_token );
            data.append( 'google_recaptcha_action', booknetic.google_recaptcha_action );

            bookneticHooks.doAction('before_processing_payment', payment_method, {
                source: 'package_booking',
                booknetic
            });

            createdPackageData = null;

            booknetic.ajax( 'pacakgesConfirm', data , function ( result )
            {
                createdPackageData = result;

                booknetic.refreshGoogleReCaptchaToken();

                bookneticHooks.doAction('after_processing_payment', payment_method, true, result, booknetic);
            }, true, function ( result )
            {
                booknetic.refreshGoogleReCaptchaToken();

                bookneticHooks.doAction('after_processing_payment', payment_method, false, result, booknetic);
            });

        }).on('click', '.booknetic_prev_step', function ()
        {
            if( ! ( booking_panel_js.data('package_id') > 0 ) )
                return;

            /**
             * Niye timeouta salinib?
             * Package bookingde ilk step hemishe Services stepi olur. Bize lazimdi tutag ki, Back buttonu clicklendikde 1ci stepe gedirmi?
             * Mesele oduki, Back buttonu clicklendikde core`da ishe dushur. Ve active stepi deyishir. Burda ardicillig sorunu yarana biler.
             * Ilk Core ishe dushse active step Services edecek ve sonra bu JS ishe dushecek.
             * Eks hal bash vererse eger active step Information goture biler.
             * Bu conflict yashanmasin deye her ehtimala 10ms-lik timeout veririk, Core`daki JS-in ilk ishe dushmesine ve active stepi teyin etmesine yol achirig.
             */
            setTimeout(function ()
            {
                let new_step_id = booking_panel_js.find(".booknetic_active_step").data('step-id');

                if( new_step_id === 'packages_services' )
                {
                    booking_panel_js.removeData('package_id');

                    booking_panel_js.find('.booknetic_appointment_steps_body .booknetic_package_save_for_revert').removeClass('booknetic_package_save_for_revert').removeClass('booknetic_menu_hidden').show();

                    booking_panel_js.find('.booknetic_appointment_steps_body .booknetic_package_step').remove();
                    booking_panel_js.find('.booknetic_appointment_container_body .booknetic_package_step_body').remove();

                    booking_panel_js.find('.booknetic_confirm_booking_btn').addClass('booknetic_next_step');

                    booknetic.fadeInAnimate('.booknetic_appointment_step_element:not(.booknetic_menu_hidden)', function () {
                        booknetic.stepManager.refreshStepNumbers();
                    });

                    booknetic.stepManager.loadStep('service');

                    return;
                }
            }, 10);
        }).on('click', '.bnktc_package_booking_popup_btn', function ()
        {
            if( !createdPackageData )
                return;

            let serviceId   = $(this).data('service-id');
            let slotId      = $(this).data('slot-id');

            booknetic.ajax( 'packagesLoadBookingPanel', {
                package_booking_id: createdPackageData['package_booking_id'],
                service_id: serviceId
            }, function ( result )
            {
                $('body').append(`
                    <div class="booknetic_packages_booking_panel_modal" style="position: fixed; background: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; top: 0; left: 0; display: flex; align-items: center; justify-content: center; z-index: 9999999;">
                        ${result['html']}
                    </div>
                `);

                let bpanel = $(".booknetic_packages_booking_panel_modal .booknetic_appointment:eq(0)");
                $('html').css('overflow', 'hidden');
                bpanel.append('<div class="booknetic_close_booking_panel" style="position: absolute; right: -15px; top: -15px; background: #FFF;width: 30px;height: 30px;display: flex;align-items: center;justify-content: center;font-size: 14px;font-weight: 600;border-radius: 50px;cursor: pointer;">X</div>');
                bpanel.find('.booknetic_close_booking_panel').on('click', function ()
                {
                    $(this).closest('.booknetic_packages_booking_panel_modal').fadeOut(200, function ()
                    {
                        $('html').css('overflow', 'initial');
                        $(this).remove();
                    });
                });

                let steps = bpanel.data('steps');
                for( let s in steps )
                {
                    if( steps[s]['id'] === 'cart' )
                    {
                        steps[s]['hidden'] = true;
                    }
                }

                bpanel.data('steps', steps);
                bpanel.data('package_booking_id', createdPackageData['package_booking_id']);
                bpanel.data('package_booking_slot', slotId);
                bpanel.data('package_on_finish', function ()
                {
                    booknetic.ajax('packagesFinishBooking', {package_booking_id: createdPackageData['package_booking_id']}, function ( result )
                    {
                        booknetic.panel_js.find('.booknetic_package_booking_finished').remove();
                        booknetic.panel_js.append( booknetic.htmlspecialchars_decode( result['html'] ) );
                    });
                });

                bookneticInitBookingPage( bpanel );
            });
        }).on('click', '#bkntc_package_finish_btn', function ()
        {
            booking_panel_js.find('#booknetic_finish_btn').trigger('click');
        });

    });

    bookneticHooks.addAction('before_step_loading', function (booknetic, new_step_id, old_step_id)
    {
        if( ! ( new_step_id === 'packages_information' || new_step_id === 'packages_confirm_details' ) )
            return;

        let booking_panel_js = booknetic.panel_js;
        var data = new FormData();
        data.append( 'step_id', new_step_id );
        data.append( 'package_id', booking_panel_js.data('package_id') );

        booknetic.ajax( 'pacakgesLoad', data , function ( result )
        {
            let container = booking_panel_js.find('.booknetic_appointment_container_body [data-step-id="' + new_step_id + '"]');
            container.show().html( booknetic.htmlspecialchars_decode(result['html']) );

            booking_panel_js.find(".booknetic_appointment_container_body").scrollTop(0);
            booknetic.handleScroll();
            booknetic.stepManager.enableActions();

            bookneticHooks.doAction( 'loaded_step', booknetic, new_step_id, old_step_id, result );
            bookneticHooks.doAction( `loaded_step_${new_step_id}`, booknetic, result );
        });
    });

    bookneticHooks.addFilter('step_validation_packages_information' , function ( result , booknetic )
    {
        let booking_panel_js = booknetic.panel_js;
        var hasError = false;

        booking_panel_js.find( 'label[for="bkntc_input_name"], label[for="bkntc_input_surname"], label[for="bkntc_input_email"], label[for="bkntc_input_phone"]' ).each( function ()
        {
            var el = $( this ).next();
            var required = $( this ).is( '[data-required="true"]' );

            if( el.is('.bkntc_input_phone-container') )
            {
                el = el.find('input');
            }

            if( ! ( booknetic.getSelected.customerId() > 0 ) )
            {
                if( el.is('#bkntc_input_name , #bkntc_input_surname , #bkntc_input_email, #bkntc_input_phone') )
                {
                    var value = el.val();

                    if( required && (value.trim() == '' || value == null) )
                    {
                        el.addClass('booknetic_input_error');
                        hasError = booknetic.__('fill_all_required');
                    }
                    else if( el.attr('name') === 'email' )
                    {
                        var email_regexp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        var checkEmail = email_regexp.test(String(value.trim()).toLowerCase());

                        if( !( (value == '' && !required) || checkEmail ) )
                        {
                            el.addClass('booknetic_input_error');
                            hasError = booknetic.__('email_is_not_valid');
                        }
                    }
                    else if( el.attr('name') === 'phone' )
                    {
                        const input = booking_panel_js.find( '#bkntc_input_phone' );
                        const inputValue = typeof intlTelInputUtils != 'undefined' ? input.data('iti').getNumber(intlTelInputUtils.numberFormat.E164) : input.val().trim();
                        const regex = /^\+?(\d{1,3})?[-. \(\)]?\d{1,4}[-. \(\)]?\d{1,4}[-. \(\)]?\d{1,9}$/;

                        const isValueInvalid = (inputValue === '' || (inputValue.length < 6 || inputValue.length > 15) || !regex.test(inputValue));

                        if ((required && isValueInvalid) || (!required && inputValue.length > 0 && !regex.test(inputValue))) {
                            el.addClass('booknetic_input_error');
                            hasError = booknetic.__('phone_is_not_valid');
                        }
                    }
                }
            }
        });

        if( hasError !== false )
        {
            return {
                status: false,
                errorMsg: hasError
            };
        }

        return result
    });

    bookneticHooks.addAction('loaded_step', function( booknetic, new_step_id )
    {
        if( new_step_id !== 'packages_information' )
            return;

        let booking_panel_js = booknetic.panel_js;

        var phone_input = booking_panel_js.find('#bkntc_input_phone');

        phone_input.data('iti', window.intlTelInput( phone_input[0], {
            utilsScript: BookneticData.assets_url + "js/utilsIntlTelInput.js",
            initialCountry: phone_input.data('country-code')
        }));
    });

    bookneticHooks.addAction('payment_completed', function ( status, data )
    {
        if( !data || !( typeof data['source'] !== 'undefined' && data['source'] === 'package_booking' ) )
            return;

        let booknetic = data['booknetic'];
        let booking_panel_js = booknetic.panel_js;

        if( status )
        {
            booknetic.ajax('packagesFinishBooking', {package_booking_id: createdPackageData['package_booking_id']}, function ( result )
            {
                booking_panel_js.append( booknetic.htmlspecialchars_decode( result['html'] ) );
            });
        }
        else
        {
            booknetic.toast( 'Payment error! Please try again.' );
            booknetic.ajax('packagesBookingDelete', {
                package_booking_id: createdPackageData['package_booking_id'],
                token: createdPackageData['token']
            });
        }
    });

})(jQuery);