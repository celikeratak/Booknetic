<?php

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Inventory\ProductInventoryAddon;
use function BookneticAddon\Inventory\bkntc__;

defined('ABSPATH') or die();

/**
 * @var array $parameters
 */
$product = $parameters['product'];
$id = $product['id'] ?? '';
$productName = $product['name'] ?? '';
$productPrice = $product['purchase_price'] ?? '';
$productSellingPrice = $product['sell_price'] ?? '';
$quantity = $product['quantity'] ?? '';
$createdAt = $product['created_at'] ?? '';
$productImage = $product['image'] ?? '';
$note = $product['note'] ?? '';
$isDisabled = $product['disable_select'] ?? '';
$services = $product['services'] ?? [];
$serviceIDS  = $product['service_ids'] ?? [];
?>

<link rel="stylesheet" href="<?php echo ProductInventoryAddon::loadAsset('assets/backend/css/add_new.css') ?>"
      type="text/css">

<script type="application/javascript"
        src="<?php echo ProductInventoryAddon::loadAsset('assets/backend/js/add_new.js') ?>"></script>

<section class="inventory-modal" data-product-id="<?php echo $id; ?>" data-product-image="<?php echo $productImage ?>">
    <header class="inventory-modal__header d-flex justify-content-between align-items-center">
        <div class="d-flex justify-content-between align-items-center">
            <span class="modal-plus-icon">+</span>
            <span class="inventory-modal__header-text"><?php echo $id > 0 ? bkntc__('Edit Product') : bkntc__('Add Product') ?></span>
        </div>
        <button data-dismiss="modal"><img src="<?php echo Helper::assets("icons/x-close.svg") ?>" alt=""></button>
    </header>
    <div class="scroll-wrapper">
        <div class="inventory-modal-body">
            <div class="product-image d-flex align-items-center">
                <div class="selected-product-image">
                    <img id="selectedProductImage"
                         src="<?php echo !! $productImage ? Helper::profileImage( $productImage, 'Products' ) : Helper::assets('icons/no-photo.svg'); ?>"
                         alt="">
                </div>
                <div>
                    <p><?php echo bkntc__('Product image') ?></p>
                    <input type="file" id="uploadInventoryProductImage" name="inventory-product-image"
                           accept="image/png, image/jpeg, image/jpg">
                    <div class="d-flex align-items-center inventory-btn-container">
                        <button class="booknetic-primary-btn inventory-upload-btn"><?php echo bkntc__('Upload Image') ?></button>
                        <button class="booknetic-secondary-btn inventory-remove-btn <?php echo !$productImage ? "disabled" : '' ?>"><?php echo bkntc__('Remove') ?></button>
                    </div>
                </div>
            </div>
            <form class="d-flex flex-column inventory-form">
                <div>
                    <label for="inventoryProductName"><?php echo bkntc__("Product name") ?><span>*</span></label>
                    <div class="inventory-product-name-wrapper">
                        <input type="text" id="inventoryProductName" aria-describedby="inventoryProductName"
                               name="inventory-product-name"
                               data-multilang-fk="<?php echo $id ?? 0 ?>" data-multilang="true"
                               value="<?php echo $productName ?>">
                    </div>
                </div>
                <div>
                    <label for="inventoryProductQuantity"><?php echo bkntc__("Product quantity") ?>
                        <span>*</span></label>
                    <input type="number" min="0" id="inventoryProductQuantity"
                           aria-describedby="inventoryProductQuantity" value="<?php echo $quantity ?>"
                           placeholder="EX: 50">
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <label for="inventoryProductPrice"><?php echo bkntc__("Purchase price ($)") ?>
                            <span>*</span></label>
                        <input type="number" min="0" id="inventoryProductPrice" aria-describedby="inventoryProductPrice"
                               placeholder="EX: 50"
                               value="<?php echo $productPrice ?>">
                    </div>
                    <div>
                        <label for="inventoryProductSellPrice"><?php echo bkntc__("Sell price ($)") ?>
                            <span>*</span></label>
                        <input type="number" min="0" id="inventoryProductSellPrice"
                               aria-describedby="inventoryProductSellPrice" placeholder="0.00"
                               value="<?php echo $productSellingPrice ?>">
                    </div>
                </div>
            </form>
            <div class="inventory-service">
                <h4><?php echo bkntc__("Services") ?></h4>
                <p><?php echo bkntc__("List of services that product will be used.") ?></p>
                <select class="form-control" name="inventory-select-service" id=inventorySelectService multiple>
                    <?php
                    foreach( $services AS $service )
                    {
                        echo '<option value="' . (int)$service['id'] . '"' . ( in_array( (string)$service['id'], explode(',', $serviceIDS ) ) ? ' selected' : '' ) . '>' . htmlspecialchars( $service['name'] ) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="disable-in-booking d-flex justify-content-between align-items-center">
                <p class="m-0 p-0"><?php echo bkntc__("Disable to select in Booking panel") ?></p>
                <i class="fa fa-info-circle help-icon do_tooltip mr-auto" data-content="<?php echo bkntc__('The product will not be visible on the booking panel but can be sold by an admin or staff member.') ?>" data-original-title="" title=""></i>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox"
                           id="disableSelect" <?php echo $isDisabled ? ' checked' : ''; ?>>
                    <label class="fs_onoffswitch-label" for="disableSelect"></label>
                </div>
            </div>
            <div class="inventory-description">
                <h4><?php bkntc__("Description") ?></h4>
                <div class="inventory-description-wrapper d-flex justify-content-between">
                    <textarea id="inventoryNote" class="inventory-description-textarea" data-multilang="true"
                              data-multilang-fk="<?php echo $id ?? 0 ?>"
                              maxlength="1000" rows="4"><?php echo htmlspecialchars($note) ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <footer class="d-flex justify-content-end align-items-center">
        <button data-dismiss="modal" class="booknetic-secondary-btn"><?php echo bkntc__('Cancel') ?></button>
        <button id="save_product" class="booknetic-primary-btn"><?php echo bkntc__('Save') ?></button>
    </footer>
</section>