<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\Inventory\bkntc__;

/**
 * @var array $parameters
 */

?>

<section class="booknetic-products-container">
    <h2 class="booknetic-"><?php echo bkntc__( "Products" ) ?></h2>
    <div class="booknetic_service_extra_title">
		<?php foreach ( $parameters[ 'products' ] as $product ): ?>
            <label id="booknetic-product-item-checkbox-<?php echo $product[ 'id' ] ?>"
                   class="booknetic-product-item booknetic-products-flex" data-id="<?php echo $product[ 'id' ] ?>">
                <input type="checkbox" class="booknetic-product-item-checkbox"
                       id="booknetic-product-item-checkbox-<?php echo $product[ 'id' ] ?>">
                <div class="booknetic-product-image">
                    <img src="<?php echo ! ! $product[ 'image' ] ? Helper::profileImage( $product[ 'image' ], 'Products' ) : Helper::assets( 'icons/no-photo.svg' ) ?>"
                         alt="">
                </div>
                <div class="booknetic-product-info">
                    <h3><?php echo $product[ 'name' ] ?></h3>
                    <p><?php echo $product[ 'note' ] ?></p>
                </div>
                <span class="booknetic-product-price"><?php echo Helper::price( $product[ 'sell_price' ] ) ?></span>
            </label>
		<?php endforeach; ?>
    </div>
    <div style="height: 16px; line-height: 0; visibility: hidden; user-select: none; opacity: 0">hide</div>
</section>

