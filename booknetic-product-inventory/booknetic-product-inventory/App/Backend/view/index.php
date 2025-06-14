<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Inventory\ProductInventoryAddon;
use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var array $parameters
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets('plugins/datatables/datatables.min.css') ?>">
<link rel="stylesheet" href="<?php echo Helper::assets('css/datatables/customDataTables.css') ?>">
<link rel="stylesheet" href="<?php echo Helper::assets('css/datatables/main.css') ?>">
<link rel="stylesheet" href="<?php echo ProductInventoryAddon::loadAsset('assets/backend/css/main.css') ?>"
      type="text/css">

<script>
    var productPhotoPath = '<?php echo $parameters['productPhotoPath'] ?>';
    var noUserImage = '<?php echo $parameters['noUserImage'] ?>';
    var noProductImage = '<?php echo $parameters['noProductImage'] ?>';
    var restBaseUrl = '<?php echo ProductInventoryAddon::getRestUrl() ?>';
</script>

<script type="application/javascript"
        src="<?php echo Helper::assets('plugins/datatables/datatables.min.js') ?>"></script>
<script type="application/javascript"
        src="<?php echo ProductInventoryAddon::loadAsset( 'assets/backend/js/main.js' ) ?>"></script>

<div class="fs-modal-body inventory-container table-wrapper-container">
    <div class="fs-modal-body-inner scroll-wrapper">
        <div class="position-relative">
            <div class="first-step">
                <ul class="nav nav-tabs nav-light table-navigations" data-tab-group="inventory">
					<?php foreach ( TabUI::get( 'inventory' )->getSubItems() as $tab ): ?>
                        <li class="nav-item">
                            <a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#">
								<?php echo $tab->getTitle(); ?>
                            </a>
                        </li>
					<?php endforeach; ?>
                </ul>

                <div class="tab-content">
					<?php foreach ( TabUI::get( 'inventory' )->getSubItems() as $tab ): ?>
                        <div class="tab-pane" data-tab-content="inventory_<?php echo $tab->getSlug(); ?>"
                             id="tab_<?php echo $tab->getSlug(); ?>">
							<?php echo $tab->getContent(); ?>
                        </div>
					<?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>