<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Models\Appointment;

$appointmentId = $parameters['id'];
$packageBookingId = Appointment::getData($appointmentId, 'package_booking_id');

if( !( $packageBookingId > 0 ) )
    return;
?>

<script type="application/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/appointment_edit_modal.js')?>"></script>
