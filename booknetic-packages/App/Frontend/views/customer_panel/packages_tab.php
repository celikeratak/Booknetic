<?php


defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use function BookneticAddon\Packages\bkntc__;

/**
 * @var mixed $parameters
 * @var PackageBooking $packageBooking
 */

$uniqId = uniqid();
?>

<div class="booknetic-cp-tab-body">
    <div class="booknetic-packages-list">
        <table class="booknetic_data_table booknetic_elegant_table">
            <thead>
            <tr>
                <th class="pl-4"><?php echo bkntc__('ID')?></th>
                <th><?php echo bkntc__('PACKAGE NAME')?></th>
                <th><?php echo bkntc__('PRICE')?></th>
                <th><?php echo bkntc__('APPOINTMENTS')?></th>
                <th><?php echo bkntc__('CREATED AT')?></th>
                <th class="width-100px"></th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ( $parameters['package_bookings'] AS $packageBooking ):?>

                <tr data-row-id="<?php print (int)$packageBooking->id?>">
                    <td><?php echo (int)$packageBooking->id?></td>
                    <td><?php echo htmlspecialchars( $packageBooking->package()->noTenant()->fetch()->name )?></td>
                    <td><?php echo Helper::price( $packageBooking->total_amount )?></td>
                    <td>
                        <div>
                            <?php
                            $appointments = json_decode( $packageBooking->appointments ?? '[]', true );
                            $busySlotsCount = array_sum( array_map( fn($slot) => ($slot['appointment_id'] > 0 ? 1 : 0), $appointments ) );
                            $allSlots = count( $appointments );
                            echo bkntc__('Booked %d of %d totals', [$busySlotsCount, $allSlots]);
                            ?>
                        </div>
                        <div class="booknetic-package-progressbar"><span style="width: <?php echo (int)($busySlotsCount / $allSlots * 100)?>%"></span></div>
                    </td>
                    <td><?php echo Date::dateTime( $packageBooking->created_at )?></td>
                    <td>
                        <?php if ( $packageBooking->payment_status !== 'not_paid' ): ?>
                        <button class="booknetic_manage_package_btn" type="button"
                                data-package-booking-id="<?php echo (int) $packageBooking->id ?>"><i
                                    class="fa-solid fa-edit"></i><?php echo bkntc__( "MANAGE PACKAGE" ) ?></button>
                        <?php else: ?>
                            <button class="booknetic_pay_package_btn"
                                    type="button"
                                    data-package-booking-id="<?php echo (int) $packageBooking->id ?>"
                                    data-payment-method="">
                                <i class="fa-solid fa-credit-card"></i><?php echo bkntc__( "PAY NOW" ) ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach;?>

            </tbody>
        </table>
    </div>

    <div id="booknetic_packages_pay_now_popup" class="booknetic_popup booknetic_hidden">
        <div class="booknetic_popup_body">
            <div class="booknetic_packages_pay_now_popup_body">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="<?php echo $uniqId . '_3' ?>"><?php echo bkntc__('Select Payment Gateway')?></label>
                        <select id="<?php echo $uniqId . '_3' ?>" class="booknetic_packages_pay_now_popup_select form-control"></select>
                    </div>
                </div>
            </div>
            <div class="booknetic_reschedule_popup_footer">
                <button class="booknetic_btn_secondary booknetic_pay_now_popup_cancel" type="button" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
                <button class="booknetic_btn_danger booknetic_packages_pay_now_popup_confirm" type="button"><?php echo bkntc__('Pay')?></button>
            </div>
        </div>
    </div>

    <div class="booknetic-packages-manage-package"></div>
</div>
