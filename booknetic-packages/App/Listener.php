<?php

namespace BookneticAddon\Packages;

use BookneticAddon\Packages\Helpers\PackageBookingData;
use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Config;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;

class Listener
{
    /*
     * Delete olunmush appointmentleri tutmag lazimdi ki, package booking-den de silek.
     * Bunun uchun appointment mutation before/after hook`u istifade edilir.
     * Before mutationda appointment ID goturulur ve ashagidaki parametrde save edilir.
     * After mutationda gelen ID eger null-dursa demek ki, appointment silinib, o halda bu parametrdeki ID silinen appointmentin ID-sidir demekdir.
     */
    private static $beforeMutationAppointmentId;

    public static function validatePackageInAppointment( AppointmentRequests $requests )
    {
        $firstAppointment = AppointmentRequests::appointments()[0];
        $isEditAction = $firstAppointment->appointmentId > 0;

        if( $isEditAction )
        {
            $packageBookingId = Appointment::getData( $firstAppointment->appointmentId, 'package_booking_id' );
            if( $packageBookingId > 0 )
            {
                $packageBookingInf = PackageBooking::get( $packageBookingId );
                $slots = json_decode( $packageBookingInf->appointments ?? '[]', true );
                $packageBookingSlot = -1;
                foreach ( $slots AS $sid => $slot )
                {
                    if( $slot['appointment_id'] > 0 )
                    {
                        $packageBookingSlot = $sid;
                        break;
                    }
                }
            }
        }
        else
        {
            $packageBookingId = Helper::_post('package_booking_id', 0, 'int');
            $packageBookingSlot = Helper::_post('package_booking_slot', -1, 'int');
        }

        if( empty( $packageBookingId ) || $packageBookingSlot === -1 )
            return;

        PackageService::validatePackageInAppointmentBooking( $packageBookingId, $packageBookingSlot, $requests );
    }

    public static function loadPackageToAppointment()
    {
        $firstAppointment = AppointmentRequests::appointments()[0];
        $isEditAction = $firstAppointment->appointmentId > 0;

        if( $isEditAction )
        {
            $packageBookingId = Appointment::getData( $firstAppointment->appointmentId, 'package_booking_id' );
            if( $packageBookingId > 0 )
            {
                $packageBookingInf = PackageBooking::get( $packageBookingId );
                $slots = json_decode( $packageBookingInf->appointments ?? '[]', true );
                $packageBookingSlot = -1;
                foreach ( $slots AS $sid => $slot )
                {
                    if( $slot['appointment_id'] > 0 )
                    {
                        $packageBookingSlot = $sid;
                        break;
                    }
                }
            }
        }
        else
        {
            $packageBookingId = Helper::_post('package_booking_id', 0, 'int');
            $packageBookingSlot = Helper::_post('package_booking_slot', -1, 'int');
        }

        if( empty( $packageBookingId ) || $packageBookingSlot === -1 )
            return;

        foreach ( AppointmentRequests::appointments() AS $appointmentObj )
        {
            $appointmentObj->price('service_price')->setPrice( 0 );
            $appointmentObj->package = [
                'package_booking_id'    =>  $packageBookingId,
                'slot_inedex'           =>  $packageBookingSlot
            ];
        }
    }

    public static function addAppointmentToPackage( AppointmentRequestData $appointmentObj )
    {
        if( ! isset( $appointmentObj->package ) )
            return;

        $appointmentId = $appointmentObj->appointmentId;
        $packageBookingId = $appointmentObj->package['package_booking_id'];
        $packageBookingSlotIndex = $appointmentObj->package['slot_inedex'];

        PackageService::addAppointmentToPackage( $appointmentId, $packageBookingId, $packageBookingSlotIndex );
    }

    public static function appointmentMutationBefore( $appointmentId )
    {
        self::$beforeMutationAppointmentId = $appointmentId;
    }

    public static function appointmentMutationAfter( $appointmentId )
    {
        if( ! is_null( $appointmentId ) )
            return;

        $deletedAppointmentId = self::$beforeMutationAppointmentId;
        PackageService::deleteAppointmentFromPackage( $deletedAppointmentId );
    }

    public static function loadAssetsInBookingPanel( $assets )
    {
        $packagesCount = Package::where('is_public', '1')->count();

        /* SaaS versiyada hemishe load olmalidi assetler. Chunki admin meselen isteye biler ki, home pagede 3 ferqli tenantin Booking panelini elave etsin, ve s. */
        if( $packagesCount > 0 || Helper::isSaaSVersion() )
        {
            $assets[] = [
                'id'    => 'booknetic.packages.book_package',
                'type'  => 'js',
                'src'   => PackagesAddon::loadAsset('assets/frontend/js/booking_panel_packages.js' )
            ];
            $assets[] = [
                'id'    => 'booknetic.packages.book_package',
                'type'  => 'css',
                'src'   => PackagesAddon::loadAsset('assets/frontend/css/booking_panel_packages.css' )
            ];
        }

        $assets[] = [
            'id'    => 'booknetic.packages.book_appointment_to_package',
            'type'  => 'js',
            'src'   => PackagesAddon::loadAsset('assets/frontend/js/book_appointment_to_package.js' )
        ];

        return $assets;
    }

    public static function bookingPanelInformationStepParametersFilter( $parameters )
    {
        $packageBookingId = Helper::_post('package_booking_id', 0, 'int');
        $packageBookingSlot = Helper::_post('package_booking_slot', -1, 'int');

        if( empty( $packageBookingId ) || $packageBookingSlot === -1 )
            return $parameters;

        $packageBookingInfo = PackageBooking::get( $packageBookingId );

        if( ! $packageBookingInfo )
            return $parameters;

        $customerInfo = Customer::get( $packageBookingInfo->customer_id );

        $parameters['name'] = $customerInfo->first_name;
        $parameters['surname'] = $customerInfo->last_name;
        $parameters['email'] = $customerInfo->email;
        $parameters['phone'] = $customerInfo->phone_number;

        $parameters['disable_name_input'] = true;
        $parameters['disable_surname_input'] = true;
        $parameters['disable_email_input'] = true;
        $parameters['disable_phone_input'] = true;

        $parameters['can_change_email_input'] = false;
        $parameters['how_many_people_can_bring'] = false;

        return $parameters;
    }

    public static function addPackagesInBookingPanel()
    {
        if( count( AppointmentRequests::appointments() ) > 1 )
            return;

		if ( AppointmentRequests::self()->currentRequest()->serviceCategoryId > 0 )
			return;

        $packages = Package::withTranslations()->where('is_public', '1')->fetchAll();

        if( count( $packages ) === 0 )
            return;

        foreach ( $packages as $p => $package )
        {
            $note = $package['notes'];

            $wrappedNote = \BookneticApp\Providers\Helpers\Helper::cutText( $note, 180 );
            $wrappedNoteLines = explode("\n", $wrappedNote);
            $hasManyLines = is_array($wrappedNoteLines) && count($wrappedNoteLines) > 2;

            if($hasManyLines)
            {
                $wrappedNote = implode("\n", [$wrappedNoteLines[0], $wrappedNoteLines[1]]);
            }

            $shouldWrap = (mb_strlen( $note ) > 180) || $hasManyLines;

            $packages[$p]['wrapped_note'] = $wrappedNote;
            $packages[$p]['should_wrap'] = $shouldWrap;

            $servicesSumPrice = 0;

            $packageServices = json_decode( $package['services'], true );
            foreach ( $packageServices AS $s => $serviceArr )
            {
                $serviceInf = Service::withTranslations()->get( $serviceArr['id'] );

                $servicesSumPrice = Math::add( $servicesSumPrice, Math::mul( $serviceInf->price, $serviceArr['count'] ) );

                $packageServices[$s]['service_inf'] = $serviceInf;
            }

            $packages[$p]['services'] = $packageServices;
            $packages[$p]['services_sum_price'] = $servicesSumPrice;
        }

        require __DIR__ . '/Frontend/views/booking_panel_packages.php';
    }

    public static function paymentCompleted( $paymentStatus, $customData, $paymentMethod )
    {
        if( !( isset( $customData['payent_for'] ) && $customData['payent_for'] === 'packages' ) )
            return;

        $packageBookingId = $customData['package_booking_id'];

        $packageBookingInf = PackageBooking::get( $packageBookingId );

        /**
         * Bu sherti yazmagda meqsedim, bezi payment gatewayler Webhook, IPN ile ishleye biler ve dublicate sorgu ata bilerler
         * Dublicate sorgu gelse o halda workflowda dublicate run olar, ona gore sherti yoxlayiram.
         * Ve ya Webhookdan gec cavab geler ve o cavab gelene qeder Appointment/Package/ves siline biler artig ve partlayar kod.
         */
        if( $packageBookingInf && $packageBookingInf->payment_status != PackageBookingData::PAYMENT_STATUSES['PAID'] )
        {
            if( $paymentStatus )
            {
                if( $paymentMethod !== 'local' )
                {
                    PackageBooking::where('id', $packageBookingId)->update([
                        'payment_status'    =>  PackageBookingData::PAYMENT_STATUSES['PAID'],
                        'paid_amount'       =>  $packageBookingInf->total_amount,
                    ]);
                }

                Config::getWorkflowEventsManager()->trigger('package_booking_created', [
                    'package_booking_id'    => $packageBookingId,
                    'package_id'            => $packageBookingInf->package_id,
                    'customer_id'           => $packageBookingInf->customer_id
                ], function ($event) use ($packageBookingInf)
                {
                    if (empty($event['data']))
                        return true;

                    $data = json_decode($event['data'], true);

                    if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== $packageBookingInf->locale)
                    {
                        return false;
                    }

                    if (
                        ! empty( $data['called_from'] ) &&
                        (
                            ( $data['called_from'] == 'backend' && !Permission::isBackEnd() ) ||
                            ( $data['called_from'] == 'frontend' && Permission::isBackEnd() )
                        )
                    ) {
                        return false;
                    }

                    return true;
                });
            }
            else
            {
                PackageBooking::where('id', $packageBookingId)->delete();
            }
        }
    }

    public static function customerTimezoneFilter ( $timezone, $customerId ) {
        if ( $timezone !== '-' && ! empty( $timezone ) )
            return $timezone;

        $packageBooking = PackageBooking::where( 'customer_id', $customerId )
                                  ->where( 'client_timezone', '<>', '-' )
                                  ->select( [ 'client_timezone' ] )
                                  ->orderBy('id DESC')
                                  ->fetch();

        $timezone = $packageBooking->client_timezone ?? '-';

        return $timezone;
    }

    public static function customerLocaleFilter ( $locale, $customerId ) {
        if ( ! empty( $locale ) )
            return $locale;

        $packageBooking = PackageBooking::where( 'customer_id', $customerId )
                                  ->where( 'locale', '<>', '' )
                                  ->select( [ 'locale' ] )
                                  ->orderBy('id DESC')
                                  ->fetch();

        $locale = $packageBooking->locale ?? '-';

        return $locale;
    }

}