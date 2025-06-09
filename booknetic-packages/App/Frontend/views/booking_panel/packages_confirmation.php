<?php

use BookneticApp\Providers\Helpers\Helper;
use function \bkntc__ as bkntccore__;
use function BookneticAddon\Packages\bkntc__;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */
?>
<div class="booknetic_confirm_container">

    <div class="booknetic_confirm_date_time booknetic_portlet">
        <div>
            <span class="booknetic_text_primary"><?php echo bkntc__('Package')?>:</span>
            <span><?php echo htmlspecialchars($parameters['package']->name)?></span>
        </div>
    </div>
    <div class="booknetic_confirm_step_body">

        <div class="booknetic_confirm_sum_body<?php echo ($parameters['hide_payments'] && !$parameters['hide_price_section'] ? ' booknetic_confirm_sum_body_full_width' : '') . ($parameters['hide_price_section'] ? ' booknetic_hidden' : '');?>">
            <div class="booknetic_portlet booknetic_portlet_cols">
                <div class="booknetic_portlet_content">
                    <div class="booknetic_prices_box">

                        <?php foreach ( $parameters['services'] AS $service ):?>
                        <div class="booknetic_confirm_details " data-price-id="service_price">
                            <div class="booknetic_confirm_details_title"><?php print sprintf('%s x%d', htmlspecialchars($service['name']), (int)$service['count'])?></div>
                            <div class="booknetic_confirm_details_price"><?php print Helper::price( $service['price'] )?></div>
                        </div>
                        <?php endforeach;?>

                        <div class="booknetic_confirm_details " data-price-id="discount">
                            <div class="booknetic_confirm_details_title"><?php print bkntccore__('Discount')?></div>
                            <div class="booknetic_confirm_details_price"><?php print Helper::price( $parameters['discount'] )?></div>
                        </div>
                    </div>
                </div>

                <div class="booknetic_panel_footer"></div>

                <div class="booknetic_confirm_sum_price">
                    <div><?php echo bkntccore__('Total price')?></div>
                    <div class="booknetic_sum_price"><?php echo Helper::price( $parameters['package']->price )?></div>
                </div>
            </div>
        </div>

        <div class="booknetic_confirm_deposit_body<?php echo ($parameters['hide_price_section'] && !$parameters['hide_payments'] ? ' booknetic_confirm_deposit_body_full_width' : '') . ($parameters['hide_payments'] ? ' booknetic_hidden' : '');?>">

            <div class="booknetic_portlet booknetic_payment_methods_container">
                <div class="booknetic_payment_methods">
                    <?php foreach ( $parameters['payment_methods'] AS $i => $gateway ): ?>
                        <div class="booknetic_payment_method <?php echo $i === 0 ? 'booknetic_payment_method_selected' : ''; ?>" data-payment-type="<?php echo $gateway->getSlug(); ?>">
                            <img src="<?php echo $gateway->getIcon(); ?>" alt="<?php echo htmlspecialchars( $gateway->getTitle() ); ?>">
                            <span><?php echo $gateway->getTitle(); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="booknetic_payment_methods_footer"></div>
            </div>
        </div>
    </div>
</div>
