<?php

use BookneticAddon\Inventory\ProductInventoryAddon;
use function BookneticAddon\Inventory\bkntc__;

defined('ABSPATH') or die();

/**
 * @var array $parameters
 */

?>

<script type="application/javascript"
        src="<?php echo ProductInventoryAddon::loadAsset('assets/backend/js/product.js') ?>"></script>
<script>
    var inventoryAssetsURL = "<?php echo ProductInventoryAddon::loadAsset('') ?>";
</script>

<section class="table-wrapper">
    <header class="inventory-product-header-wrapper d-flex justify-content-between align-items-center">
        <h1 class="inventory-product-header"><?php echo bkntc__('Products') ?></h1>
        <div class="d-flex justify-content-between table-wrapper-buttons">
            <button class="create-product-button d-flex align-items-center">
                <span><?php echo bkntc__("Create product") ?></span>
                <span>
                    <img src="<?php echo ProductInventoryAddon::loadAsset('assets/backend/icons/plus.svg') ?>" alt="">
                </span>
            </button>
        </div>
    </header>
    <div class="datatables-container table-responsive inventory-data-table">
        <div class="bulk-action">
            <div class="d-flex justify-content-between align-items-center bulk-selector">
                <div class="select-all">
                    <input type="checkbox" id="bulkActionCheckbox">
                    <label for="bulkActionCheckbox" id="bulkActionLabel"></label>
                </div>
                <div class="selected-items">
                    <span class="selected-product-count">1</span>
                    <span><?php echo bkntc__("selected") ?></span>
                </div>
                <button class="delete-products-btn d-flex align-items-center">
                    <div class="inventory-trash-icon">
                        <img src="<?php echo $parameters["trashIcon"] ?>" alt="">
                    </div>
                    <span><?php echo bkntc__("Delete Products") ?></span>
                </button>
            </div>
        </div>
        <div class="responsive-datatables" style="overflow-y: hidden">
            <div class="datatables-container table-responsive">
                <table id="inventoryProductTable" class="hover nowrap dataTable">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="showBulkAction"></th>
                        <th data-key="id"><?php echo bkntc__('ID') ?></th>
                        <th data-key="name"><?php echo bkntc__('Product name') ?></th>
                        <th data-key="purchase_price"><?php echo bkntc__('Purchase price') ?></th>
                        <th data-key="sell_price"><?php echo bkntc__('Sell price') ?></th>
                        <th data-key="created_at"><?php echo bkntc__('Created date') ?></th>
                        <th data-key="quantity"><?php echo bkntc__('Quantity') ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
