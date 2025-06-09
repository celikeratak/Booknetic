<?php


defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use function BookneticAddon\Packages\bkntc__;

/**
 * @var mixed $parameters
 */

$appointments = json_decode( $parameters['info']->appointments ?? '[]', true );
$busySlotsCount = array_sum( array_map( fn($slot) => ($slot['appointment_id'] > 0 ? 1 : 0), $appointments ) );
$allSlots = count( $appointments );
?>

<div class="booknetic-package-manage-package-header">
    <div>
        <button type="button" class="booknetic-packages-manage-package-backbtn"> < Back</button>
        <span class="booknetic-packages-manage-package-name"><?php echo htmlspecialchars($parameters['info']->package()->noTenant()->fetch()->name)?></span>
    </div>
    <div>
        <div><?php echo bkntc__('Booked %d of %d totals', [$busySlotsCount, $allSlots]);?></div>
        <div class="booknetic-package-progressbar"><span style="width: <?php echo (int)($busySlotsCount / $allSlots * 100)?>%"></span></div>
    </div>
</div>

<div class="booknetic-package-service-container">
    <?php foreach ( $parameters['slots'] AS $serviceId => $service ): ?>
        <?php
        $emptySlotsCount = array_sum( array_map( fn($slot) => (!$slot['appointment_id']?1:0), $service['slots'] ) );
        ?>
        <div class="booknetic-package-service">
            <header class="booknetic-package-service-header">
                <div>
                    <p class="booknetic-package-service-name"><?php print htmlspecialchars($service['info']->name)?></p>
                    <p class="booknetic-package-service-category booknetic-package-service-secondary">(<?php print htmlspecialchars($service['category']->name)?>)</p>
                </div>
                <span class="booknetic-package-service-secondary"><?php echo bkntc__('%d sessions left', [$emptySlotsCount])?></span>
            </header>
            <div class="booknetic-package-service-item-container">

                <?php foreach( $service['slots'] AS $slotInf ):?>

                    <?php if( $slotInf['appointment_id'] > 0 ):?>
                        <div class="booknetic-package-service-item booknetic-package-scheduled-service-item">
                            <div class="booknetic-package-service-item-appointment-info">
                                <p><?php print htmlspecialchars( $slotInf['appointment_info']->getStaffInf()->name )?></p>
                                <div class="booknetic-package-disc"></div>
                                <p><?php print htmlspecialchars( $slotInf['appointment_info']->getLocationInf()->name )?></p>
                                <div class="booknetic-package-disc"></div>
                                <p><?php print $slotInf['appointment_date']?> / <?php print $slotInf['appointment_start_time']?>-<?php print $slotInf['appointment_end_time']?></p>
                            </div>
                        </div>
                    <?php else:?>

                        <div class="booknetic-package-service-item booknetic-package-schedule-now" data-package_booking_id="<?php print (int)$parameters['info']['id']?>" data-package_booking_slot="<?php print (int)$slotInf['slot_id']?>" data-package_service_id="<?php print (int)$serviceId?>">
                            <img src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/schedule-icon.webp') ?>" alt="">
                            <p><?php echo bkntc__("Schedule now") ?></p>
                        </div>

                    <?php endif;?>

                <?php endforeach;?>

            </div>
        </div>
    <?php endforeach;?>
</div>
