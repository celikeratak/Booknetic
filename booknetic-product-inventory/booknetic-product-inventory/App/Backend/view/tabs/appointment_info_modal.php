<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

$saveCustomerId = 0;

if ( empty( $parameters['products'] ) )
{
    echo '<div class="text-secondary font-size-14 text-center">' . bkntc__( 'No products found' ) . '</div>';
}
else
{
    foreach ( $parameters['products'] AS $product )
    {
        ?>

        <div class="form-row extra_row dashed-border" data-id="3" data-active="1">
            <div class="form-group col-md-4">
                <label class="text-primary"><?php echo bkntc__('Name:'); ?></label>
                <div class="form-control-plaintext" data-tag="name"><?php echo $product['name'] ?></div>
            </div>
            <div class="form-group col-md-2">
                <label><?php echo bkntc__('Price:'); ?></label>
                <div class="form-control-plaintext" data-tag="price"><?php echo Helper::price( $product['sell_price'] )?></div>
            </div>
        </div>

        <?php
    }
}
?>