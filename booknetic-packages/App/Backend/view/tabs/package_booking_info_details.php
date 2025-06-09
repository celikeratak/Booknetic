<?php

defined('ABSPATH') or die();

/**
 * @var mixed $parameters
 */

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticAddon\Packages\PackagesAddon;

use function BookneticAddon\Packages\bkntc__;

?>

<div class="package-info-accordion">
    <div class="d-flex align-items-center justify-content-between">
        <label class="m-0"><?php echo bkntc__('Package Info') ?></label>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             class="accordion-icon">
            <path d="M6 9L12 15L18 9" stroke="#14151A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <div class="package-accordion-content">
        <div class="d-flex align-items-center gap-12 border-bottom">
            <div class="image-container">
                <img src="<?php echo $parameters['info']['customer_profile_image'] ?? Helper::assets('icons/no-user.webp') ?>"
                     alt="">
            </div>
            <div>
                <h2 class="m-0"><?php echo htmlspecialchars($parameters['info']['customer_first_name']) . ' ' . htmlspecialchars($parameters['info']['customer_last_name']) ?></h2>
                <p class="m-0"><?php echo htmlspecialchars($parameters['info']['customer_email']) ?></p>
            </div>
        </div>
        <div class="package-expiration">
            <h2 class="m-0"><?php echo bkntc__('Package') ?></h2>
            <p class="m-0"><?php echo htmlspecialchars($parameters['info']->package_name) ?></p>
        </div>
        <div class="package-info d-flex align-items-center justify-content-between">
            <div class="flex-1">
                <h2 class="m-0"><?php echo bkntc__('Created at') ?></h2>
                <p class="m-0"><?php echo Date::dateTime($parameters['info']->created_at); ?></p>
            </div>
            <div class="flex-1">
                <h2 class="m-0"><?php echo bkntc__('Expires at') ?></h2>
                <p class="m-0"><?php echo Date::dateTime($parameters['info']->created_at, sprintf('+%d %s', (int)$parameters['info']->package_duration_value, (string)$parameters['info']->package_duration)); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="package-service-container">
    <?php foreach ($parameters['slots'] as $serviceId => $service): ?>
        <?php
        $emptySlotsCount = array_sum( array_map( fn($slot) => (!$slot['appointment_id']?1:0), $service['slots'] ) );
        ?>
        <div class="package-service">
            <header class="package-service-header d-flex align-items-center justify-content-between gap-8">
                <p class="service-name m-0"><?php echo htmlspecialchars($service['info']->name) ?></p>
                <p class="service-category m-0 mr-auto service-secondary">(<?php echo htmlspecialchars($service['category']->name) ?>)</p>
                <span class="session-left service-secondary"><?php echo bkntc__('%d sessions left', [$emptySlotsCount])?></span>
            </header>
            <div class="service-item-container d-flex flex-column gap-16">
                <?php foreach ($service['slots'] as $slotInf): ?>
                    <?php if ($slotInf['appointment_id'] > 0): ?>
                        <div class="d-flex align-items-center service-item scheduled-service-item justify-content-between">
                            <div class="d-flex align-items-center">
                                <p class="m-0"><?php echo htmlspecialchars($slotInf['appointment_info']->getStaffInf()->name) ?></p>
                                <div class="disc"></div>
                                <p class="m-0"><?php echo htmlspecialchars($slotInf['appointment_info']->getLocationInf()->name) ?></p>
                                <div class="disc"></div>
                                <p class="m-0"><?php echo Date::datee($slotInf['appointment_info']->getInfo()->starts_at) ?> / <?php echo Date::time($slotInf['appointment_info']->getInfo()->starts_at) ?>-<?php echo Date::time($slotInf['appointment_info']->getInfo()->ends_at) ?></p>
                            </div>
                            <div class="package-more-menu-container">
                                <button class="package-more-menu-btn">
                                    <img src="<?php echo Helper::assets('icons/three-dots.webp') ?>" alt="">
                                </button>
                                <ul class="package-more-menu">
                                    <li class="edit-package-btn d-flex" data-load-modal="appointments.edit" data-parameter-id="<?php echo (int)$slotInf['appointment_id']; ?>"><img src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/edit.webp') ?>" alt=""><span>Edit</span></li>
                                    <li class="info-package-btn d-flex" data-load-modal="appointments.info" data-parameter-id="<?php echo (int)$slotInf['appointment_id']; ?>"><img src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/info_square.webp') ?>" alt=""><span>Info</span></li>
                                    <li class="delete-package-btn d-flex" data-delete-id="<?php echo (int)$slotInf['appointment_id']; ?>"><img src="<?php echo Helper::assets('icons/delete-icon.svg') ?>" alt=""><span>Delete</span></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="service-item d-flex align-items-center gap-10" type="button" data-load-modal="appointments.add_new" data-parameter-package_booking_id="<?php echo (int)$parameters['info']['id'] ?>" data-parameter-package_booking_slot="<?php echo (int)$slotInf['slot_id'] ?>">
                            <div>
                                <img src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/schedule-icon.webp') ?>" alt="">
                            </div>
                            <p class="m-0"><?php echo bkntc__("Schedule now") ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
