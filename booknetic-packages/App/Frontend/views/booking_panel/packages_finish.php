<?php
defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Providers\Helpers\Date;
use function BookneticAddon\Packages\bkntc__;
?>

<section class="bkntc_package-confirmation_container">
    <div>
        <header class="bkntc_package-confirmation_header">
            <div class="bkntc_package-flex bkntc_gap-6"><img src="<?php echo PackagesAddon::loadAsset('assets/frontend/icons/check-circle.svg')?>" alt=""><span><?php echo bkntc__('Confirmed')?></span></div>
            <h1 class="bkntc_package-m-0 bkntc_package-p-0"><?php echo bkntc__("Thank you for your request!")?></h1>
            <p class="bkntc_package-m-0 bkntc_package-p-0"><?php echo bkntc__('We sent an email to you with all the details.')?></p>
        </header>

        <?php foreach ( $parameters['services'] AS $serviceId => $service ): ?>
        <?php
        $bookedCount = array_sum( array_map( fn($slot) => (!$slot['appointment_id']?0:1), $service['slots'] ) );
        ?>
        <div class="bkntc_package_summary">
            <header>
                <h2 class="bkntc_package-p-0 bkntc_package-m-0"><?php print htmlspecialchars($service['info']->name)?></h2>
                <p class="bkntc_package-p-0 bkntc_package-m-0"><?php echo bkntc__('Booked %d of %d sessions', [$bookedCount, count($service['slots'])])?></p>
            </header>
            <div class="bkntc_package_appointment_container bkntc_gap-16">
                <?php foreach( $service['slots'] AS $slotInf ):?>
                <?php if( $slotInf['appointment_id'] > 0 ):?>
                    <div class="bkntc_package_appointment booked">
                        <div class="bkntc_package_appointment_info bkntc_package_flex-center bkntc_gap-10">
                            <p class="bkntc_package-m-0 bkntc_package-p-0"><?php print htmlspecialchars( $slotInf['appointment_info']->getStaffInf()->name )?></p>
                            <div class="dot"></div>
                            <p class="bkntc_package-m-0 bkntc_package-p-0"><?php print htmlspecialchars( $slotInf['appointment_info']->getLocationInf()->name )?></p>
                            <div class="dot"></div>
                            <p class="bkntc_package-m-0 bkntc_package-p-0"><?php print Date::datee( $slotInf['appointment_info']->getInfo()->starts_at )?> / <?php print Date::time( $slotInf['appointment_info']->getInfo()->starts_at )?>-<?php print Date::time( $slotInf['appointment_info']->getInfo()->ends_at )?></p>
                        </div>
                        <div class="bkntc_package_confirmation bkntc_package_flex-center bkntc_gap-12">
                            <span><?php echo bkntc__('Confirmation number:')?></span>
                            <p class="bkntc_package-m-0 bkntc_package-p-0"><?php print sprintf('%04d', (int)$slotInf['appointment_id']);?></p>
                        </div>
                    </div>
                <?php else:?>
                    <div class="bkntc_package_appointment empty bkntc_package_flex-center bkntc_gap-10 bnktc_package_booking_popup_btn" data-service-id="<?php print (int)$serviceId?>" data-slot-id="<?php print (int)$slotInf['slot_id']?>">
                        <div class="bkntc_package_icon"><img src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/schedule-icon.webp')?>" alt=""></div>
                        <p class="bkntc_package-p-0 bkntc_package-m-0"><?php echo bkntc__('Schedule now')?></p>
                    </div>
                <?php endif?>
                <?php endforeach;?>
            </div>
        </div>
        <?php endforeach;?>
    </div>
    <footer class="bkntc_package-flex bkntc_package_flex_footer bkntc_gap-12">
        <button class="bkntc_new_booking_btn bkntc_package-flex" id="booknetic_finish_btn" data-redirect-url=""><div><img src="<?php echo PackagesAddon::loadAsset('assets/frontend/icons/plus_circle.webp')?>" alt=""></div> <span><?php echo bkntc__('New booking')?></span></button>
        <button class="bkntc_finish_btn bkntc_package-flex" id="booknetic_finish_btn" data-redirect-url="<?php echo htmlspecialchars(Helper::getOption('redirect_url_after_booking'))?>"><div><img src="<?php echo PackagesAddon::loadAsset('assets/frontend/icons/calendar_check.webp')?>" alt=""></div> <span><?php echo bkntc__('Finish booking')?></span></button>
    </footer>
</section>
