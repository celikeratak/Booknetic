<?php

defined('ABSPATH') or die();

/**
 * @var mixed $parameters
 */

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Packages\Helpers\PackageService;

use function BookneticAddon\Packages\bkntc__;

$package = $parameters['info'];

$id = $package['id'] ?? 0;
$name = $package['name'];
$expirationValue = (int)($package['duration_value'] ?? 0);
$expirationDate = $package['duration'];
$selectedPaymentMethods = json_decode($package['payment_methods'] ?? '[]', true);
$isPrivate = isset($package['is_public']) && $package['is_public'] == 0;
$image = $package['image'];
$description = $package['notes'];
?>

<script>
    var noProductImage = "<?php echo Helper::assets('icons/no-photo.svg')?>";
</script>

<section class="package-details-container" data-package-id="<?php echo (int)$id; ?>"
         data-package-image="<?php echo $image ?>">
    <div class="package-image d-flex align-items-center">
        <div class="selected-package-image">
            <img id="selectedPackageImage"
                 src="<?php echo PackageService::imageUrl( $image ); ?>"
                 alt="">
        </div>
        <div>
            <p class="package-modal-label selected-package-image-text"><?php echo bkntc__('Package image') ?></p>
            <input type="file" id="uploadPackageImage" class="hide-upload-input" name="package-image"
                   accept="image/png, image/jpeg, image/jpg">
            <div class="d-flex align-items-center modal-btn-container">
                <button class="booknetic-primary-btn package-upload-btn"><?php echo bkntc__('Upload Image') ?></button>
                <button class="booknetic-secondary-btn package-remove-btn <?php echo (isset($image) && $image !== '' && $image !== 'undefined' && $image !== 'null') ? "" : 'disabled' ?>"><?php echo bkntc__('Remove') ?></button>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column package-form-container">
        <div>
            <label for="packageName" class="package-modal-label"><?php echo bkntc__("Package name") ?>
                <span>*</span></label>
            <input type="text" id="packageName" aria-describedby="packageName" class="package-form-element"
                   name="inventory-package-name"
                   data-multilang-fk="<?php echo (int)$id ?? 0 ?>" data-multilang="true"
                   value="<?php echo htmlspecialchars($name) ?>">
        </div>

        <div class="d-flex gap-32 align-items-center expiration-container">
            <label class="package-form-element flex-1 mb-0">
                <div class="package-expiration d-flex align-items-center gap-12">
                    <input type="checkbox" id="packageHasExpiration" <?php echo $expirationValue > 0 ? 'checked' : '' ?>>
                    <span class="expiration-span"><?php echo bkntc__('Expiration')?></span>
                    <i class="fa fa-info-circle help-icon do_tooltip" data-content="<?php echo 'Set the date which sold package will be expire.'?>" data-original-title="" title=""></i>
                </div>
            </label>
            <div class="d-flex align-items-center flex-1 expiration-select-container <?php echo $expirationValue == 0 ? "hidden" : ''?>">
                <input type="number" min="1" class="package-form-element expiration-input flex-1" value="<?php echo $expirationValue <= 0 ? 1 : $expirationValue ?>">
                <select class="form-control package-form-element flex-1" id="packageExpiration">
                    <option value="day" <?php echo ($expirationDate === 'day') ? 'selected' : ''; ?>><?php echo bkntc__('Day'); ?></option>
                    <option value="week" <?php echo ($expirationDate === 'week') ? 'selected' : ''; ?>><?php echo bkntc__('Week'); ?></option>
                    <option value="month" <?php echo ($expirationDate === 'month') ? 'selected' : ''; ?>><?php echo bkntc__('Month'); ?></option>
                    <option value="year" <?php echo ($expirationDate === 'year') ? 'selected' : ''; ?>><?php echo bkntc__('Year'); ?></option>
                </select>
            </div>
        </div>

        <div class="payment-method">
            <label class="package-modal-label"><?php echo bkntc__("Payment method") ?><span>*</span></label>
            <p class="package-label-desc"><?php echo bkntc__("This will override the services payment methods within Package") ?></p>
            <select class="form-control" multiple id="packagePaymentMethods">

                <?php foreach ($parameters['payment_methods'] as $paymentMethod):
                    $slug = htmlspecialchars($paymentMethod->getSlug());
                    $title = htmlspecialchars($paymentMethod->getTitle());
                    $isSelected = in_array($slug, $selectedPaymentMethods) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $slug; ?>" <?php echo $isSelected; ?>><?php echo $title; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="private-package d-flex justify-content-between align-items-center package-form-element gap-6">
            <p class="m-0 p-0"><?php echo bkntc__("Make Package Private") ?></p>
            <i class="fa fa-info-circle help-icon do_tooltip mr-auto" data-content="<?php echo 'Package will be only visible and can be booked by admin/staff member.' ?>" data-original-title="" title=""></i>
            <div class="fs_onoffswitch">
                <input type="checkbox" class="fs_onoffswitch-checkbox"
                       id="isPackagePrivate" <?php echo $isPrivate ? ' checked' : ''; ?>>
                <label class="fs_onoffswitch-label" for="isPackagePrivate"></label>
            </div>
        </div>

        <div class="package-description">
            <label class="package-modal-label"><?php echo bkntc__("Description") ?></label>
            <textarea id="packageDesc" class="package-description-textarea package-form-element"
                      data-multilang="true"
                      data-multilang-fk="<?php echo (int)$id ?? 0 ?>"
                      maxlength="1000" rows="4"><?php echo htmlspecialchars($description) ?></textarea>
        </div>
    </div>
</section>
