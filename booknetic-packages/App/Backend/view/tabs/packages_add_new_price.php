<?php

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */
use function BookneticAddon\Packages\bkntc__;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\Helpers\Helper;

$package = $parameters['info'];
$services = json_decode($package['services'] ?? '[]', true);
$serviceDetails = [];

foreach ($parameters['services'] as $service)
{
    $serviceDetails[(int)$service->id] = [
        'price' => Math::floor( $service->price ),
        'name'  => htmlspecialchars( $service->name )
    ];
}

$totalPackagePrice = !empty( $package['price'] ) ? Math::floor( $package['price'] ) : '';
?>

<div class="package-price-container" style="display: <?php echo count($services) > 0 ? 'block' : 'none'?>">
    <?php foreach ($services as $selectedService):
        $serviceId = (int)$selectedService['id'];
        $count = (int)$selectedService['count'];
        $price = $serviceDetails[$serviceId]['price'] ?? 0;
        $name = $serviceDetails[$serviceId]['name'] ?? '???';
        $subtotal = Math::mul($price, $count);
        ?>
        <div class="d-flex gap-6 align-items-center justify-content-between">
            <p class="product-name"><?php echo $name; ?> <span class="appointment-amount">[x<?php echo $count; ?>]</span></p>
            <div class="line"></div>
            <p class="product-price"><?php echo Helper::price( $subtotal ); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="total-price-container d-flex flex-column">
    <p class="product-name"><?php echo bkntc__("Total Price")?></p>
    <input type="text" data-type="currency" class="product-price package-form-element" id="totalPackagePrice" min="0" value="<?php echo Helper::price($totalPackagePrice); ?>" data-value="<?php echo $totalPackagePrice; ?>"/>
</div>
