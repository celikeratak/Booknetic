<?php

defined('ABSPATH') or die();

/**
 * @var mixed $parameters
 */

use BookneticAddon\Packages\PackagesAddon;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Packages\bkntc__;

?>

<script>
    //var noProductImage = '<?php //echo $parameters['noProductImage'] ?>//';
</script>
<link rel="stylesheet" href="<?php echo PackagesAddon::loadAsset('assets/backend/css/packages_add_new.css') ?>">
<script type="text/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/packages_add_new.js') ?>"
        id="packages_add_new_JS" data-package-booking-id="<?php echo (int)$parameters['info']['id'] ?>"></script>

<section class="package-modal">
    <header class="package-modal__header d-flex justify-content-between align-items-center">
        <div class="d-flex justify-content-between align-items-center">
            <span class="modal-plus-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 16 16" fill="none">
                    <path d="M7.99992 3.3335V12.6668M3.33325 8.00016H12.6666" stroke="white" stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="package-modal__header-text"><?php echo $parameters['info']['id'] > 0 ? bkntc__('Edit Package') : bkntc__('Add Package') ?></span>
        </div>
        <button data-dismiss="modal"><img src="<?php echo Helper::assets("icons/x-close.svg") ?>" alt=""></button>
    </header>
    <div class="scroll-wrapper">
        <div class="package-modal-body">
            <form>
                <ul class="nav nav-tabs nav-light table-navigations" data-tab-group="packages_add_new">
                    <?php foreach (TabUI::get('packages_add_new')->getSubItems() as $tab): ?>
                        <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>"
                                                href="#"><?php echo $tab->getTitle(); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content">
                    <?php foreach (TabUI::get('packages_add_new')->getSubItems() as $tab): ?>
                        <div class="tab-pane" data-tab-content="packages_add_new_<?php echo $tab->getSlug(); ?>"
                             id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent($parameters); ?></div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>
    <footer class="d-flex justify-content-end align-items-center">
        <button data-dismiss="modal" class="booknetic-secondary-btn"><?php echo bkntc__('CANCEL') ?></button>
        <button class="d-flex align-items-center booknetic-primary-btn" id="addPackageBookingSave">
            <span><?php echo bkntc__('SAVE') ?></span>
        </button>
    </footer>
</section>
