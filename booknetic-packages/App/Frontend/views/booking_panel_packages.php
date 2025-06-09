<?php

defined( 'ABSPATH' ) or die();

use \BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\Packages\bkntc__;
?>

<div class="booknetic_package_category"><?php echo bkntc__('Packages')?></div>
<?php
foreach ( $packages AS $package )
{
    ?>
    <div class="booknetic_package_card" data-package-id="<?php echo (int)$package[ 'id' ]; ?>">
        <div class="booknetic_package_card_header">
            <div class="booknetic_package_card_image">
                <img class="booknetic_card_package_image" src="<?php echo \BookneticAddon\Packages\Helpers\PackageService::imageUrl( $package['image'] ) ?>">
            </div>

            <div class="booknetic_package_card_title">
                <span class="booknetic_package_title_span"><?php echo htmlspecialchars($package[ 'name' ]); ?></span>
            </div>

            <div class="booknetic_package_card_price">
                <?php if ($package[ 'services_sum_price' ] !== $package[ 'price' ] ):?>
                    <span><?php echo Helper::price( $package[ 'services_sum_price' ] );?></span>
                <?php endif; ?>
                <?php echo Helper::price( $package[ 'price' ] );?>
            </div>
        </div>

        <div class="booknetic_package_card_description">
            <span class="booknetic_package_card_description_fulltext"><?php echo nl2br( htmlspecialchars($package[ "notes" ]) )?></span>
            <span class="booknetic_package_card_description_wrapped"><?php echo nl2br( htmlspecialchars($package['wrapped_note']) ); ?></span>
            <?php if( $package['should_wrap'] ) {?>
                <span class="booknetic_view_more_package_notes_button">
                    <?php echo bkntc__("Show more") ?>
                </span>
                <span class="booknetic_view_less_package_notes_button">
                    <?php echo bkntc__("Show less") ?>
                </span>
            <?php } ?>
        </div>

        <div class="booknetic_package_card_services">
            <?php foreach ( $package['services'] AS $service ):?>
            <div class="booknetic_package_card_service_el">
                <span><?php print htmlspecialchars($service['service_inf']->name)?></span>
                <span>x<?php print (int)$service['count']?></span>
            </div>
            <?php endforeach;?>
        </div>
    </div>

    <?php
}
?>
