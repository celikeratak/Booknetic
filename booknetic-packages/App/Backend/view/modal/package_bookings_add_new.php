<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Packages\bkntc__;

?>

<link rel="stylesheet" href="<?php echo PackagesAddon::loadAsset('assets/backend/css/package_bookings_add_new.css')?>">
<script type="text/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/package_bookings_add_new.js')?>" id="package_bookings_add_new_JS" data-package-booking-id="<?php echo (int)$parameters['info']['id']?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $parameters['info']['id'] > 0 ? bkntc__( 'Edit Package Booking' ) : bkntc__('Add Package Booking')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form>

            <ul class="nav nav-tabs nav-light" data-tab-group="package_bookings_add_new">
                <?php foreach ( TabUI::get( 'package_bookings_add_new' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'package_bookings_add_new' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="package_bookings_add_new_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="addPackageBookingSave"><?php echo bkntc__( 'SAVE' )?></button>
</div>
