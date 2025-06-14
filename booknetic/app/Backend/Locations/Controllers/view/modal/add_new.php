<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Backend\Locations\DTOs\Response\LocationResponse;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var LocationResponse $parameters
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/add_new.css', 'Locations' ) ?>">
<script type="text/javascript" src="<?php echo Helper::assets( 'js/add_new.js', 'Locations' ) ?>" id="add_new_JS"
        data-latitude="<?php echo htmlspecialchars( $parameters->getLatitude() ) ?>"
        data-longitude="<?php echo htmlspecialchars( $parameters->getLongitude() ) ?>"
        data-location-id="<?php echo (int) $parameters->getId() ?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $parameters->getId() > 0 ? bkntc__( 'Edit Location' ) : bkntc__( 'Add Location' ) ?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form>

            <ul class="nav nav-tabs nav-light" data-tab-group="locations_add_new">
				<?php foreach ( TabUI::get( 'locations_add_new' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>"
                                            href="#"><?php echo $tab->getTitle(); ?></a></li>
				<?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
				<?php foreach ( TabUI::get( 'locations_add_new' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="locations_add_new_<?php echo $tab->getSlug(); ?>"
                         id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
				<?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<div class="fs-modal-footer">
	<?php
	if ( $parameters->getId() > 0 ):?>
        <button type="button" class="btn btn-lg btn-outline-secondary"

                id="hideLocationBtn"><?php echo ! $parameters->isActive() ? bkntc__( 'UNHIDE LOCATION' ) : bkntc__( 'HIDE LOCATION' ) ?></button>
	<?php endif; ?>
    <button type="button" class="btn btn-lg btn-outline-secondary"
            data-dismiss="modal"><?php echo bkntc__( 'CANCEL' ) ?></button>
    <button type="button" class="btn btn-lg btn-primary"
            id="addLocationSave"><?php echo $parameters->getId() > 0 ? bkntc__( 'SAVE' ) : bkntc__( 'ADD LOCATION' ) ?></button>
</div>
