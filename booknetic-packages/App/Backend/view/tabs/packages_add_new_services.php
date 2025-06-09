<?php

defined('ABSPATH') or die();

/**
 * @var mixed $parameters
 */

use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\Packages\bkntc__;

$package = $parameters['info'];

$services = json_decode(
    $package["services"] ?? '[]',
    false,
    512,
    JSON_THROW_ON_ERROR
);

$serviceDetails = [];
foreach ($parameters['services'] as $service) {
    $serviceDetails[(int)$service->id] = [
        'price' => \BookneticApp\Providers\Helpers\Math::floor($service->price),
        'name' => htmlspecialchars($service->name)
    ];
}

$detailedServices = [];
foreach ($services as $service) {
    $serviceId = (int) $service->id;
    $detailedServices[] = [
        'id' => $serviceId,
        'count' => $service->count,
        'price' => $serviceDetails[$serviceId]['price'] ?? 0,
        'name' => $serviceDetails[$serviceId]['name'] ?? ''
    ];
}

$detailedServicesJson = htmlspecialchars(json_encode($detailedServices, JSON_THROW_ON_ERROR));

?>

<script>
    var deleteIcon = "<?php echo Helper::assets('icons/delete-icon.svg') ?>";
    var infoOutlineIcon = "<?php echo PackagesAddon::loadAsset('assets/backend/icons/info-outline.svg') ?>";
</script>

<div class="add-service-container" data-detailed-services-json="<?php echo $detailedServicesJson; ?>">
    <select class="form-control" id="packageServices">
        <option></option>
        <?php foreach ($parameters['services'] as $service): ?>
            <option value="<?php echo (int)$service->id ?>"
                    data-price="<?php echo \BookneticApp\Providers\Helpers\Math::floor($service->price) ?>"
                <?php if (in_array((int)$service->id, array_column($services, 'id'), true)): ?> disabled<?php endif; ?>>
                <?php echo htmlspecialchars($service->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p class="services-helper d-flex align-items-center gap-6"><span><img
                    src="<?php echo PackagesAddon::loadAsset('assets/backend/icons/info.svg') ?>"
                    alt=""/></span><?php echo bkntc__("Services with custom durations will only be added with default duration.") ?>
    </p>

    <div class="services d-flex flex-column gap-16" id="selectedServicesContainer">
        <?php foreach ($detailedServices as $detailedService): ?>
            <div class="package-service" data-id="<?php echo $detailedService['id'] ?>" data-price="<?php echo $detailedService['price']; ?>">
                <div class="package-service-header d-flex align-items-center justify-content-between">
                    <h2 class="package-service-name m-0"><?php echo htmlspecialchars($detailedService['name']) ?></h2>
                    <button id="deletePackageService">
                        <img src="<?php echo Helper::assets('icons/delete-icon.svg') ?>" alt="">
                    </button>
                </div>
                <p class="d-flex align-items-center gap-6">
                    <span class="number-of-appointments"><?php echo bkntc__("Number of appointments") ?></span>
                    <i class="fa fa-info-circle help-icon do_tooltip"
                       data-content="<?php echo bkntc__('Set available appointment count for the service.') ?>"
                       data-original-title="" title=""></i>
                </p>
                <div class="number-appointments d-flex align-items-center justify-content-between">
                    <button class="decrement amount-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M4.16675 10H15.8334" stroke="#14151A" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <input type="text" min="0" value="<?php echo $detailedService['count'] ?>" class="flex-1 number-of-appointments-input">
                    <button class="increment amount-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10.0001 4.1665V15.8332M4.16675 9.99984H15.8334" stroke="#14151A"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
