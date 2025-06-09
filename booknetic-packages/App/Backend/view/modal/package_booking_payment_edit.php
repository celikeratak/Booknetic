<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use BookneticAddon\Packages\Helpers\PackageBookingData;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use function BookneticAddon\Packages\bkntc__;

/**
 * @var mixed $parameters
 */
?>

<script type="text/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/package_booking_edit_payment.js')?>" id="package_booking_edit_payment_JS" data-package-booking-id="<?php echo (int)$parameters['info']->id?>"></script>

<div class="fs-modal-title">
    <div class="title-icon"><img src="<?php echo Helper::icon('payment-appointment.svg')?>"></div>
    <div class="title-text"><?php echo bkntc__('Payment')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="input_total_amount"><?php echo bkntc__('Total amount')?> <span class="required-star">*</span></label>
                    <input class="form-control" id="input_total_amount" value="<?php echo Math::floor( $parameters['info']->total_amount )?>" placeholder="0">
                </div>
                <div class="form-group col-md-6">
                    <label for="input_paid_amount"><?php echo bkntc__('Paid amount')?> <span class="required-star">*</span></label>
                    <input class="form-control" id="input_paid_amount" value="<?php echo Math::floor( $parameters['info']->paid_amount )?>" placeholder="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="input_payment_status"><?php echo bkntc__('Payment status')?> <span class="required-star">*</span></label>
                    <select class="form-control" id="input_payment_status">
                        <option value="<?php print PackageBookingData::PAYMENT_STATUSES['PAID']?>"<?php echo ( $parameters['info']->payment_status == PackageBookingData::PAYMENT_STATUSES['PAID'] ? ' selected' : '' )?>><?php echo bkntc__('Paid')?></option>
                        <option value="<?php print PackageBookingData::PAYMENT_STATUSES['NOT_PAID']?>"<?php echo ( $parameters['info']->payment_status == PackageBookingData::PAYMENT_STATUSES['NOT_PAID'] ? ' selected' : '' )?>><?php echo bkntc__('Not paid')?></option>
                    </select>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('BACK')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="savePaymentButton"><?php echo bkntc__('SAVE')?></button>
</div>
