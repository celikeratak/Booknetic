<?php

namespace BookneticAddon\Packages\Backend;


use BookneticAddon\Packages\Helpers\PackageBookingData;
use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Backend\Customers\Helpers\CustomerService;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Packages\bkntc__;

class PackageBookingsAjax extends \BookneticApp\Providers\Core\Controller
{

    public function add_new()
    {
        $id = Helper::_post('id', '0', 'int');

        if( $id > 0 )
        {
            Capabilities::must( 'package_bookings_edit' );
        }
        else
        {
            Capabilities::must( 'package_bookings_add' );
        }

        if( $id > 0 )
        {
            $info = PackageBooking::get( $id );
            $customer = $info->customer()->fetch();
        }
        else
        {
            $info = [
                'id'            =>  null,
                'customer_id'   =>  null,
                'package_id'    =>  null,
                'note'          =>  null
            ];
            $customer = null;
        }

        TabUI::get( 'package_bookings_add_new' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Details' ) )
             ->addView(__DIR__ . '/view/tabs/package_bookings_add_new_details.php')
             ->setPriority( 1 );

        return $this->modalView('package_bookings_add_new', [
            'info'      =>  $info,
            'packages'  =>  Package::fetchAll(),
            'customer'  =>  $customer
        ]);
    }

    public function get_customers()
    {
        $search = Helper::_post( 'q', '', 'string' );

        $customers = Customer::my();

        if( ! empty( $search ) )
        {
            $customers = $customers->where( 'CONCAT(`first_name`, \' \', `last_name`)', 'like', "%{$search}%" )
                                   ->orWhere( 'email', 'like', "%{$search}%" )
                                   ->orWhere( 'phone_number', 'like', "%{$search}%" );
        }

        $customers = $customers->select( [ 'id', 'first_name', 'last_name' ] )->limit( 100 )->fetchAll();

        $data = array_map( fn( $elem ) => [
            'id'	=> (int) $elem[ 'id' ],
            'text'	=> htmlspecialchars($elem[ 'first_name' ] . ' ' . $elem[ 'last_name' ] )
        ], $customers );

        return $this->response( true, [ 'results' => $data ] );
    }

    public function save_package_booking()
    {
        $id         = Helper::_post('id', 0, 'int');
        $package    = Helper::_post('package', 0, 'int');
        $customer   = Helper::_post('customer', 0, 'int');
        $note       = Helper::_post('note', '', 'string');

        if( $id > 0 ) {
            Capabilities::must( 'package_bookings_edit' );
        } else {
            Capabilities::must( 'package_bookings_add' );
        }

        if( ! ( $package > 0 && $customer > 0 ) )
            return $this->response(false, bkntc__('Please fill all required fields!'));

        $packageInf = Package::get( $package );

        if( ! $packageInf )
            return $this->response(false);

        $data = new PackageBookingData();

        $data->id               = $id;
        $data->customerId       = $customer;
        $data->packageId        = $package;
        $data->note             = $note;
        $data->paidAmount       = 0;
        $data->totalAmount      = $packageInf->price;
        $data->paymentStatus    = PackageBookingData::PAYMENT_STATUSES['NOT_PAID'];
        $data->paymentMethod    = 'local';

        if ( !( $id > 0 ) ) {
            /* try to find customer timezone from old package or appointment bookings */
            $data->clientTimezone = CustomerService::findCustomerTimezone( $customer );
        }

        try {
            $packageBookingId = PackageService::bookPackage( $data );
        } catch ( \Exception $exception ) {
            return $this->response(false, $exception->getMessage());
        }

        $paymentItems = [
            [
                'name'  =>  $packageInf->name,
                'price' =>  $packageInf->price,
                'image' =>  PackageService::imageUrl( $packageInf->image )
            ]
        ];

        $paymenCustomData = [
            'payent_for'            =>  'packages',
            'package_booking_id'    =>  $packageBookingId
        ];

        $paymentGateway = PaymentGatewayService::find('local');
        $paymentData = $paymentGateway->createPayment( $paymentItems, $paymenCustomData );

        return $this->response(true);
    }

    public function package_booking_info()
    {
        Capabilities::must( 'package_bookings' );

        $packageBookingId = Helper::_post('id', '0', 'integer');

        $packageBookingInf = PackageBooking::where( PackageBooking::getField('id'), $packageBookingId )
            ->leftJoin( 'customer', [ 'first_name', 'last_name', 'email', 'profile_image', 'phone_number' ] )
            ->leftJoin( 'package', [ 'name', 'duration_value', 'duration' ] )
            ->fetch();

        $slots = [];
        foreach ( json_decode( $packageBookingInf->appointments ) AS $slotId => $appointment )
        {
            if( !isset( $slots[$appointment->service_id] ) )
            {
                $serviceInf = Service::get( $appointment->service_id );
                $slots[$appointment->service_id] = [
                    'info'      =>  $serviceInf,
                    'category'  =>  ServiceCategory::get( $serviceInf->category_id ),
                    'slots'     =>  []
                ];
            }

            $appointmentInfo = false;
            if( $appointment->appointment_id > 0 )
            {
                $appointmentInfo = new AppointmentSmartObject( $appointment->appointment_id );
            }

            $slots[$appointment->service_id]['slots'][] = [
                'slot_id'           =>  $slotId,
                'appointment_id'    =>  $appointment->appointment_id,
                'appointment_info'  =>  $appointmentInfo
            ];
        }

        TabUI::get( 'package_booking_info' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Package Booking Details' ) )
             ->addView(__DIR__ . '/view/tabs/package_booking_info_details.php')
             ->setPriority( 1 );

        return $this->modalView('package_booking_info', [
            'info'  =>  $packageBookingInf,
            'slots' =>  $slots
        ]);
    }

    public function payment_edit()
    {
        Capabilities::must( 'package_bookings' );

        $packageBookingId   = Helper::_post('id', '0', 'integer');
        $packageBookingInf  = PackageBooking::get( $packageBookingId );

        if( ! $packageBookingInf )
            throw new \Exception(bkntc__('Package Booking not found!'));

        return $this->modalView( 'package_booking_payment_edit', [
            'info'  => $packageBookingInf
        ]);
    }

    public function save_payment()
    {
        Capabilities::must( 'package_bookings_edit' );

        $isUpdated = false;

        $packageBookingId   = Helper::_post('id', 0, 'integer');
        $total_amount	    = Helper::_post('total_amount', null, 'float');
        $paid_amount	    = Helper::_post('paid_amount', null, 'float');
        $payment_status     = Helper::_post('status', null, 'string', array_values(PackageBookingData::PAYMENT_STATUSES));

        $pbData = new PackageBookingData();

        $pbData->id = $packageBookingId;
        $pbData->totalAmount = $total_amount;
        $pbData->paidAmount = $paid_amount;
        $pbData->paymentStatus = $payment_status;

        PackageService::updatePackageBookingPayment( $pbData );

        return $this->response(true, [ 'id' => $packageBookingId ]);
    }

}