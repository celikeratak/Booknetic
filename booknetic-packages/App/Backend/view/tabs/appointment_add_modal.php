<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use \BookneticApp\Providers\Helpers\Helper;

$packageBookingId = Helper::_post('package_booking_id', 0, 'int');
$packageBookingSlot = Helper::_post('package_booking_slot', -1, 'int');

if( !( $packageBookingId > 0 && $packageBookingSlot > -1 ) )
    return;

$packageBookingInf = \BookneticAddon\Packages\Model\PackageBooking::get( $packageBookingId );
$services = json_decode( $packageBookingInf->appointments, true );

if( ! isset( $services[$packageBookingSlot] ) )
    return;

$slotInf = $services[$packageBookingSlot];
$serviceInf = \BookneticApp\Models\Service::get( $slotInf['service_id'] );
$serviceCategoryInf = \BookneticApp\Models\ServiceCategory::get( $serviceInf->category_id );
$customerInf = \BookneticApp\Models\Customer::get( $packageBookingInf->customer_id );

$serviceArr = [
    'id'				=>	(int)$serviceInf['id'],
    'text'				=>	htmlspecialchars($serviceInf['name']),
    'repeatable'		=>	(int)$serviceInf['is_recurring'],
    'repeat_type'		=>	htmlspecialchars( $serviceInf['repeat_type'] ),
    'repeat_frequency'	=>	htmlspecialchars( $serviceInf['repeat_frequency'] ),
    'full_period_type'	=>	htmlspecialchars( $serviceInf['full_period_type'] ),
    'full_period_value'	=>	(int)$serviceInf['full_period_value'],
    'max_capacity'		=>	(int)$serviceInf['max_capacity'],
    'date_based'		=>	$serviceInf['duration'] >= 1440
];
$serviceCategoryArr = [
    'id'                => (int)$serviceCategoryInf['id'],
    'text'              => htmlspecialchars($serviceCategoryInf['name']),
    'have_sub_categ'    => $serviceCategoryInf['sub_categs']
];
$customerArr = [
    'id'	            => (int) $customerInf[ 'id' ],
    'text'	            => htmlspecialchars($customerInf[ 'first_name' ] . ' ' . $customerInf[ 'last_name' ] )
];
$preSelectData = [
    'service'   =>  $serviceArr,
    'category'  =>  $serviceCategoryArr,
    'customer'  =>  $customerArr
];
?>

<script type="application/javascript" id="package_booking_appointment_modal_JS" data-package_booking_id="<?php print (int)$packageBookingId?>" data-package_booking_slot="<?php print (int)$packageBookingSlot?>" data-preselect="<?php print htmlspecialchars(json_encode($preSelectData))?>" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/appointment_add_modal.js')?>"></script>


