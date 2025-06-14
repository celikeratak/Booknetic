<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Inventory\ProductInventoryAddon;
use function BookneticAddon\Inventory\bkntc__;

?>

<script type="application/javascript" src="<?php echo ProductInventoryAddon::loadAsset('assets/backend/js/appointments_modal.js')?>"></script>

<div id="product-inventory-edit-tab">
    <div class="text-secondary font-size-14 text-center">
        <?php echo bkntc__( 'No products found' ); ?>
    </div>
</div>
