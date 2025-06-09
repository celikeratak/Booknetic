<?php

namespace BookneticAddon\Packages\Helpers;

use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Customer;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use Exception;
use function BookneticAddon\Packages\bkntc__;

class PackageService
{

    public static function createPackage( PackageData $packageData )
    {
        if( empty( $packageData->name ) )
            throw new Exception(bkntc__('Package name is required.'));

        if( empty( $packageData->services ) || !is_array($packageData->services) )
            throw new Exception(bkntc__('Package services is required.'));

        foreach ( $packageData->services AS $service )
        {
            if( count( $service ) !== 2 || ! isset( $service['id'] ) || ! isset( $service['count'] ) || ! is_numeric( $service['id'] ) || ! is_numeric( $service['count'] ) )
                throw new Exception(bkntc__('Package services is required.'));
        }

        if( ! is_array( $packageData->paymentMethods ) || empty( $packageData->paymentMethods ) )
            throw new Exception(bkntc__('Package payment methods is required.'));

        foreach ( $packageData->paymentMethods AS $paymentMethod )
        {
            if( ! is_string( $paymentMethod ) || ! PaymentGatewayService::find( $paymentMethod ) )
                throw new Exception(bkntc__('Package payment methods is required.'));
        }

        if( $packageData->id > 0 )
        {
            $packageId = $packageData->id;
            $packageInf = Package::get( $packageData->id );

            $updateData = [
                'name'              =>  $packageData->name,
                'duration_value'    =>  $packageData->durationValue,
                'duration'          =>  $packageData->duration,
                'notes'             =>  $packageData->notes,
                'services'          =>  json_encode( $packageData->services ),
                'payment_methods'   =>  json_encode( $packageData->paymentMethods ),
                'price'             =>  $packageData->price,
                'is_public'         =>  $packageData->isPublic
            ];

            if( ! empty( $packageData->image ) )
            {
                $updateData['image'] = $packageData->image;

                if( ! empty( $packageInf->image ) )
                {
                    self::removeImage( $packageInf->image );
                }
            }

            if( ! empty( $packageInf->image ) && $packageData->removeOldImage == 1 )
            {
                $updateData['image'] = null;

                self::removeImage( $packageInf->image );
            }

            Package::where('id', $packageId)->update( $updateData );
        }
        else
        {
            Package::insert([
                'name'              =>  $packageData->name,
                'image'             =>  $packageData->image,
                'duration_value'    =>  $packageData->durationValue,
                'duration'          =>  $packageData->duration,
                'notes'             =>  $packageData->notes,
                'services'          =>  json_encode( $packageData->services ),
                'payment_methods'   =>  json_encode( $packageData->paymentMethods ),
                'price'             =>  $packageData->price,
                'is_public'         =>  $packageData->isPublic
            ]);

            $packageId = DB::lastInsertedId();
        }

        return $packageId;
    }

    public static function deletePackage( $packageId )
    {
        $checkPackageBookingExists = PackageBooking::where('package_id', $packageId)->count();

        if( $checkPackageBookingExists > 0 )
            throw new Exception(bkntc__('You cannot delete this package because there are Package Bookings associated with it.'));

        Package::where('id', $packageId)->delete();
    }

    /**
     * @param PackageBookingData $packageBookingData
     *
     * @return int
     * @throws Exception
     */
    public static function bookPackage( PackageBookingData $packageBookingData ) : int
    {
        $packageInfo = Package::get( $packageBookingData->packageId );

        if( ! $packageInfo )
            throw new Exception(bkntc__('Package not found!'));

        $customerInfo = Customer::get( $packageBookingData->customerId );

        if( ! $customerInfo )
            throw new Exception(bkntc__('Customer not found!'));

        // for edit action
        $createNewSlots = true;
        if( $packageBookingData->id > 0 )
        {
            $packageBookingInfo = PackageBooking::get( $packageBookingData->id );

            if( ! $packageBookingInfo )
                throw new Exception(bkntc__('Something went wrong!'));

            $appointmentSlots = json_decode( $packageBookingInfo->appointments, true );
            foreach ( $appointmentSlots AS $appointment )
            {
                if( ! is_null( $appointment['appointment_id'] ) )
                {
                    if( $packageBookingData->customerId == $packageBookingInfo->customer_id && $packageBookingData->packageId == $packageBookingInfo->package_id )
                    {
                        $createNewSlots = false;
                    }
                    else
                    {
                        throw new Exception(bkntc__('You cannot edit this package. First, delete your booked appointments.'));
                    }
                }
            }
        }

        $appointments = [];

        if( $createNewSlots )
        {
            $packageServices = json_decode($packageInfo->services, true);
            foreach ( $packageServices AS $service )
            {
                if( ! ( isset( $service['id'] ) && isset( $service['count'] ) ) )
                    continue;

                for( $i=0; $i<$service['count']; $i++ )
                {
                    $appointments[] = [
                        'service_id'        =>  (int)$service['id'],
                        'appointment_id'    =>  null
                    ];
                }
            }
        }

        if( $packageBookingData->id > 0 )
        {
            $updateData = [
                'customer_id'       =>  $packageBookingData->customerId,
                'package_id'        =>  $packageBookingData->packageId,
                'note'              =>  $packageBookingData->note
            ];

            if( $createNewSlots )
            {
                $updateData['appointments'] = json_encode( $appointments );
            }

            PackageBooking::where('id', $packageBookingData->id)->update($updateData);
            $packageBookingId = $packageBookingData->id;
        }
        else
        {
            PackageBooking::insert([
                'created_at'        =>  Date::epoch(),
                'customer_id'       =>  $packageBookingData->customerId,
                'package_id'        =>  $packageBookingData->packageId,
                'note'              =>  $packageBookingData->note,
                'payment_method'    =>  $packageBookingData->paymentMethod,
                'payment_status'    =>  $packageBookingData->paymentStatus,
                'total_amount'      =>  $packageBookingData->totalAmount,
                'paid_amount'       =>  $packageBookingData->paidAmount,
                'locale'            =>  $packageBookingData->locale,
                'client_timezone'   =>  $packageBookingData->clientTimezone,
                'appointments'      =>  json_encode( $appointments ),
                'expires_on'        =>  Date::epoch('now', "+{$packageInfo->duration_value} {$packageInfo->duration}")
            ]);

            $packageBookingId = DB::lastInsertedId();
        }

        return $packageBookingId;
    }

    public static function deletePackageBooking( $packageBookingId )
    {
        $info = PackageBooking::get( $packageBookingId );
        $slots = json_decode( $info->appointments ?? '[]', true );

        foreach ( $slots AS $slot )
        {
            if( $slot['appointment_id'] > 0 )
                throw new Exception(bkntc__('You cannot delete this Package Booking because there are appointments associated with it.'));
        }

        PackageBooking::where('id', $packageBookingId)->delete();
    }

    public static function updatePackageBookingPayment( PackageBookingData $packageBookingData )
    {
        $packageBookingInfo = PackageBooking::get( $packageBookingData->id );

        if( ! $packageBookingInfo )
            throw new Exception(bkntc__('Something went wrong!'));

        if( $packageBookingData->paidAmount > $packageBookingData->totalAmount )
            throw new Exception(bkntc__('The Paid amount cannot be more than the Total amount.'));

        PackageBooking::where('id', $packageBookingData->id)->update([
            'payment_status'    =>  $packageBookingData->paymentStatus,
            'total_amount'      =>  $packageBookingData->totalAmount,
            'paid_amount'       =>  $packageBookingData->paidAmount
        ]);
    }

    public static function getPackagePrivateToken( $packageBookingId )
    {
        $packageBookingInf = PackageBooking::get( $packageBookingId )->toArray();

        return md5( json_encode( $packageBookingInf ) );
    }

    /**
     * @param int $packageBookingId
     * @param AppointmentRequests $requests
     *
     * @return void
     * @throws Exception
     */
    public static function validatePackageInAppointmentBooking( int $packageBookingId, int $packageBookingSlot, AppointmentRequests $requests )
    {
        $firstAppointment = $requests->appointments()[0];
        $isEditAction = $firstAppointment->appointmentId > 0;

        $packageBookingInfo = PackageBooking::get( $packageBookingId );

        if( ! $packageBookingInfo )
            throw new Exception( \BookneticAddon\Packages\bkntc__('Package not found!') );

        if( count( $requests->appointments ) !== 1 )
            throw new Exception( \BookneticAddon\Packages\bkntc__('The cart module isn\'t supported with the Package module!') );

        $packageAppointmentSlots = json_decode( $packageBookingInfo->appointments ?? '[]', true );

        if( ! isset( $packageAppointmentSlots[ $packageBookingSlot ] ) || ! (is_null( $packageAppointmentSlots[ $packageBookingSlot ]['appointment_id'] ) || ($isEditAction && $packageAppointmentSlots[ $packageBookingSlot ]['appointment_id'] == $firstAppointment->appointmentId)) )
            throw new Exception( \BookneticAddon\Packages\bkntc__('Package not found!') );

        $appointmentInfo = $requests->appointments[0];

        if( $appointmentInfo->customerId != $packageBookingInfo->customer_id )
            throw new Exception( \BookneticAddon\Packages\bkntc__('Something went wrong!') );

        if( $appointmentInfo->serviceId != $packageAppointmentSlots[ $packageBookingSlot ]['service_id'] )
            throw new Exception( \BookneticAddon\Packages\bkntc__('Something went wrong!') );

		if ( $packageBookingInfo->payment_status == 'not_paid' )
			throw new Exception( bkntc__( 'Package is not fully paid' ) );

    }

    public static function addAppointmentToPackage( $appointmentId, $packageBookingId, $packageBookingSlotIndex )
    {
        Appointment::setData( $appointmentId, 'package_booking_id', $packageBookingId );

        $packageBookingInfo = PackageBooking::get( $packageBookingId );

        $packageAppointmentSlots = json_decode( $packageBookingInfo->appointments, true );
        $packageAppointmentSlots[ $packageBookingSlotIndex ]['appointment_id'] = $appointmentId;

        PackageBooking::where('id', $packageBookingId)->update([
            'appointments'  =>  json_encode( $packageAppointmentSlots )
        ]);
    }

    public static function deleteAppointmentFromPackage( $appointmentId )
    {
        $packageBookingId = Appointment::getData( $appointmentId, 'package_booking_id' );

        $packageBookingInfo = PackageBooking::get( $packageBookingId );

        $packageAppointmentSlots = json_decode( $packageBookingInfo->appointments, true );

        foreach ( $packageAppointmentSlots AS $i => $slot )
        {
            if( $slot['appointment_id'] == $appointmentId )
            {
                $packageAppointmentSlots[$i]['appointment_id'] = null;
                break;
            }
        }

        PackageBooking::where('id', $packageBookingId)->update([
            'appointments'  =>  json_encode( $packageAppointmentSlots )
        ]);
    }

    public static function uploadImage()
    {
        if( !( isset( $_FILES['image'] ) && is_string( $_FILES['image']['tmp_name'] ) ) )
            return null;

        $path_info = pathinfo( $_FILES["image"]["name"] );
        $extension = strtolower( $path_info['extension'] );

        if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
            throw new Exception( bkntc__('Only JPG and PNG images allowed!') );

        $image = md5( base64_encode(rand(1,9999999) . microtime(true)) ) . '.' . $extension;
        $file_name = Helper::uploadedFile( $image, 'Packages' );

        move_uploaded_file( $_FILES['image']['tmp_name'], $file_name );

        return $image;
    }

    public static function removeImage( $image )
    {
        $filePath = Helper::uploadedFile( $image, 'Packages' );

        if( is_file( $filePath ) && is_writable( $filePath ) )
        {
            unlink( $filePath );

            return true;
        }

        return false;
    }

    public static function imageUrl( $image )
    {
        if( empty( $image ) )
            return PackagesAddon::loadAsset('assets/images/no-photo.svg');

        return Helper::uploadedFileURL( $image, 'Packages' );
    }

}
