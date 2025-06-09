<?php

namespace BookneticAddon\Packages\Backend;

use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Models\Customer;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\Abstracts\AbstractDataTableUI;
use BookneticApp\Providers\UI\DataTableUI;
use function BookneticAddon\Packages\bkntc__;


class PackageBookingsController extends \BookneticApp\Providers\Core\Controller
{

    public function index()
    {
        Capabilities::must('package_bookings');

        $packageBookings = PackageBooking::leftJoin( 'customer', [ 'first_name', 'last_name', 'email', 'profile_image', 'phone_number' ] )
                               ->leftJoin( 'package', [ 'name' ] );

        $dataTable = new DataTableUI( $packageBookings );

        $dataTable->setIdFieldForQuery( PackageBooking::getField('id') );

        if (Capabilities::userCan('package_bookings_edit'))
            $dataTable->addAction('edit', bkntc__('Edit'));

        $dataTable->addAction('info', bkntc__('Info'));

        if (Capabilities::userCan('package_bookings_delete'))
        {
            $dataTable->addAction('delete', bkntc__('Delete'), function ( $ids )
            {
                foreach ( $ids AS $id )
                {
                    PackageService::deletePackageBooking( $id );
                }
            }, AbstractDataTableUI::ACTION_FLAG_BULK_SINGLE);
        }

        $dataTable->setTitle(bkntc__('Package bookings'));

        if (Capabilities::userCan('package_bookings_add'))
            $dataTable->addNewBtn(bkntc__('BOOK PACKAGE'));

        $dataTable->searchBy([PackageBooking::getField('id'), Package::getField('name'), Customer::getField('first_name'), Customer::getField('last_name'), Customer::getField('email'), Customer::getField('phone_number')]);

        $dataTable->addColumns(bkntc__('ID'), 'id');

        $dataTable->addColumns(bkntc__( 'CUSTOMER' ), function ( $row )
        {
            $customerHtml = Helper::profileCard( $row[ 'customer_first_name' ] . ' ' . $row[ 'customer_last_name' ], $row[ 'customer_profile_image' ], $row[ 'customer_email' ], 'Customers' ) ;

            return '<div class="d-flex align-items-center justify-content-between">' . $customerHtml . '</div>';
        }, [ 'is_html' => true, 'order_by_field' => 'customer_first_name' ], true );

        $dataTable->addColumns(bkntc__('PACKAGE NAME'), 'package_name');
        $dataTable->addColumns(bkntc__('PACKAGE NAME'), 'package_name');
        $dataTable->addColumns(bkntc__('CREATED AT' ), fn( $row ) => Date::dateTime( $row[ 'created_at' ] ), ['order_by_field' => 'created_at']);

        $dataTable->addColumns(bkntc__('PRICE'), function( $row )
        {
            $svg = Helper::icon( ($row[ 'payment_status' ] === 'paid' ? 'invoice-paid' : 'invoice') . '.svg' );
            $badge = ' <img class="invoice-icon" data-load-modal="package_bookings.payment_edit" data-parameter-id="' . (int) $row[ 'id' ] . '" src="' . $svg . '"> ';
            return Helper::price( $row[ 'total_amount' ] ) . $badge;
        }, ['order_by_field' => 'total_amount', 'is_html' => true]);

        $dataTable->addColumns(bkntc__('BOOKED'), function( $row )
        {
            $bookings = json_decode( $row['appointments']??'[]', true );
            $totalSlots = count($bookings);
            $bookedSlots = count(array_filter($bookings, fn ($appointment) => !is_null($appointment['appointment_id'])));

            $progressBar = '<div class="package-progressbar"><span style="width: '.((int)($bookedSlots / $totalSlots * 100)).'%"></span></div>';

            return "<div data-load-modal=\"package_bookings.package_booking_info\" data-parameter-id=\"{$row->id}\"><div>{$bookedSlots}/{$totalSlots}</div>{$progressBar}</div>";
        }, ['is_html' => true]);

        $table = $dataTable->renderHTML();

        $this->view( 'package_bookings_index', ['table' => $table]);
    }

}
