<?php

namespace BookneticAddon\Packages;

use BookneticAddon\Packages\Backend\PackageBookingsAjax;
use BookneticAddon\Packages\Backend\PackageBookingsController;
use BookneticAddon\Packages\Backend\PackagesAjax;
use BookneticAddon\Packages\Backend\PackagesController;
use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Config;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\UI\TabUI;

function bkntc__ ( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, PackagesAddon::getAddonTextDomain() );
}

class PackagesAddon extends AddonLoader
{

    public function init ()
    {
	    Capabilities::registerTenantCapability( 'packages', bkntc__('Packages module') );

	    if( ! Capabilities::tenantCan( 'packages' ) )
		    return;

	    Capabilities::register( 'packages', bkntc__('Packages'));
	    Capabilities::register( 'packages_add', bkntc__('Add'));
	    Capabilities::register( 'packages_edit', bkntc__('Edit'));
	    Capabilities::register( 'packages_delete', bkntc__('Delete'));

	    Capabilities::register( 'package_bookings', bkntc__('Package Bookings'));
        Capabilities::register( 'package_bookings_add', bkntc__('Add new'), 'package_bookings');
        Capabilities::register( 'package_bookings_edit', bkntc__('Edit'), 'package_bookings');
	    Capabilities::register( 'package_bookings_delete', bkntc__('Delete'), 'package_bookings');

        Config::getWorkflowEventsManager()->get( 'package_booking_created' )
              ->setTitle( bkntc__( 'New Package Booking' ) )
              ->setEditAction( 'packages', 'workflow_event_package_booking_created' )
              ->setAvailableParams( [ 'package_booking_id', 'package_id', 'customer_id' ] );

        self::registerShortCodes();

        add_action( 'bkntc_appointment_requests_load',  [ Listener::class, 'loadPackageToAppointment' ] );
        add_action( 'bkntc_appointment_requests_validate',  [ Listener::class, 'validatePackageInAppointment' ] );
        add_action( 'bkntc_appointment_created',  [ Listener::class, 'addAppointmentToPackage' ] );

        add_action( 'bkntc_appointment_before_mutation',  [ Listener::class, 'appointmentMutationBefore' ] );
        add_action( 'bkntc_appointment_after_mutation',  [ Listener::class, 'appointmentMutationAfter' ] );

        add_action('bkntc_payment_completed', [ Listener::class, 'paymentCompleted' ], 10, 3 );

        add_filter( 'bkntc_customer_timezone', [ Listener::class, 'customerTimezoneFilter' ], 10, 2 );
        add_filter( 'bkntc_customer_locale', [ Listener::class, 'customerLocaleFilter' ], 10, 2 );

	    add_filter( 'bkntc_whitelist_translation_tables', fn ( $whiteList ) => array_merge( $whiteList, [ Package::getTableName() ] ) );
    }

    public function initFrontend()
    {
        if( ! Capabilities::tenantCan( 'packages' ) )
            return;

        add_filter( 'bkntc_booking_panel_assets', [ Listener::class, 'loadAssetsInBookingPanel' ] );
        add_action( 'bkntc_booking_panel_information_step_parameters',  [ Listener::class, 'bookingPanelInformationStepParametersFilter' ], 10, 1 );
        add_action( 'bkntc_service_step_footer',  [ Listener::class, 'addPackagesInBookingPanel' ], 10, 0 );

        $this->setFrontendAjaxController( Frontend\Ajax::class );

        add_action('bkntc_after_customer_panel_shortcode', function ()
        {
            wp_enqueue_script( 'booknetic-cp-packages', PackagesAddon::loadAsset('assets/frontend/js/packages-customer-panel.js') );
            wp_enqueue_style('booknetic-cp-packages', PackagesAddon::loadAsset('assets/frontend/css/packages-customer-panel.css' ) );
        });
    }

    public function initBackend ()
    {
	    if( ! Capabilities::tenantCan( 'packages' ) )
		    return;

        if( Capabilities::userCan('packages') )
        {
            Route::get( 'packages', PackagesController::class );
            Route::post( 'packages', PackagesAjax::class );

            MenuUI::get( 'packages' )
                  ->setTitle( bkntc__( 'Packages' ) )
                  ->setIcon('fa fa-cubes')
                  ->setPriority( 250 );
        }

        if( Capabilities::userCan('package_bookings') )
        {
            Route::get( 'package_bookings', PackageBookingsController::class );
            Route::post( 'package_bookings', PackageBookingsAjax::class );

            MenuUI::get( 'package_bookings' )
                  ->setTitle( bkntc__( 'Package Bookings' ) )
                  ->setIcon('fa fa-box-open')
                  ->setPriority( 250 );

            TabUI::get( 'appointments_add_new' )
                 ->item( 'details' )
                 ->addView( __DIR__ . '/Backend/view/tabs/appointment_add_modal.php' );

            TabUI::get( 'appointments_edit' )
                 ->item( 'details' )
                 ->addView( __DIR__ . '/Backend/view/tabs/appointment_edit_modal.php' );
        }
    }

    private static function registerShortCodes()
    {
        Config::getShortCodeService()->registerCategory('package_booking_info', bkntc__('Package Booking Info'));
        Config::getShortCodeService()->registerCategory('package_info', bkntc__('Package Info'));

        Config::getShortCodeService()->registerShortCode( 'package_booking_id', [
            'name'      =>  bkntc__('Package Booking - ID'),
            'category'  =>  'package_booking_info',
            'depends'   =>  'package_booking_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_booking_note', [
            'name'      =>  bkntc__('Package Booking - Note'),
            'category'  =>  'package_booking_info',
            'depends'   =>  'package_booking_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_booking_price', [
            'name'      =>  bkntc__('Package Booking - Price'),
            'category'  =>  'package_booking_info',
            'depends'   =>  'package_booking_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_booking_expires_on', [
            'name'      =>  bkntc__('Package Booking - Expires On'),
            'category'  =>  'package_booking_info',
            'depends'   =>  'package_booking_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_booking_payment_method', [
            'name'      =>  bkntc__('Package Booking - Payment method'),
            'category'  =>  'package_booking_info',
            'depends'   =>  'package_booking_id'
        ] );


        Config::getShortCodeService()->registerShortCode( 'package_id', [
            'name'      =>  bkntc__('Package - ID'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_name', [
            'name'      =>  bkntc__('Package - Name'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_image_url', [
            'name'      =>  bkntc__('Package - Image URL'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_color', [
            'name'      =>  bkntc__('Package - Color'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_note', [
            'name'      =>  bkntc__('Package - Note'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );
        Config::getShortCodeService()->registerShortCode( 'package_services', [
            'name'      =>  bkntc__('Package - Services list'),
            'category'  =>  'package_info',
            'depends'   =>  'package_id'
        ] );

        Config::getShortCodeService()->addReplacer(function ( $text, $data )
        {
            if ( isset( $data[ 'package_booking_id' ] ) )
            {
                $packageBookingInf = PackageBooking::get( $data[ 'package_booking_id' ] );

                $arr = [
                    '{package_booking_id}'              => $packageBookingInf->id,
                    '{package_booking_note}'            => $packageBookingInf->note,
                    '{package_booking_price}'           => Helper::price( $packageBookingInf->total_amount ),
                    '{package_booking_expires_on}'      => Date::datee( $packageBookingInf->expires_on, false, true, $packageBookingInf->client_timezone ),
                    '{package_booking_payment_method}'  => Helper::paymentMethod( $packageBookingInf->payment_method )
                ];

                $text = str_replace(array_keys($arr), array_values($arr), $text);
            }

            if ( isset( $data[ 'package_id' ] ) )
            {
                $packageInf = Package::withTranslations()->get( $data[ 'package_id' ] );

                $services = [];
                foreach ( json_decode( $packageInf->services, true ) AS $service )
                {
                    $serviceInf = Service::get( $service['id'] );
                    $services[] = $serviceInf->name . ' [x' . (int)$service['count'] . ']';
                }

                $arr = [
                    '{package_id}'              => $packageInf->id,
                    '{package_name}'            => $packageInf->name,
                    '{package_image_url}'       => PackageService::imageUrl( $packageInf->image ),
                    '{package_color}'           => $packageInf->color,
                    '{package_note}'            => $packageInf->notes,
                    '{package_services}'        => implode(" , ", $services)
                ];

                $text = str_replace(array_keys($arr), array_values($arr), $text);
            }

            return $text;
        });
    }

}