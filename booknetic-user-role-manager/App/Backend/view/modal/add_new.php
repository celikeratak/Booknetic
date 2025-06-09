<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\UserRoleManager\UserRoleManagerAddon;
use BookneticApp\Providers\UI\TabUI;

use function BookneticAddon\UserRoleManager\bkntc__;

/**
 * @var mixed $parameters
 * @var mixed $_mn
 */
?>

<link rel="stylesheet" href="<?php echo UserRoleManagerAddon::loadAsset('assets/backend/css/add_new.css')?>">
<script type="application/javascript" src="<?php echo UserRoleManagerAddon::loadAsset('assets/backend/js/add_new.js')?>" id="add_new_JS" data-mn="<?php echo $_mn?>"  data-role-id="<?php echo (int)$parameters['role']['id']?>"  ></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $parameters[ 'role' ][ 'id' ] > 0 ? bkntc__( 'Edit Role' ) : bkntc__( 'Add Role' )?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form id="addRoleForm">

            <div class="nowrap overflow-auto">
                <ul class="nav nav-tabs nav-light" data-tab-group="role_add">
                    <?php foreach ( TabUI::get( 'role_add' )->getSubItems() as $tab ): ?>
                        <li class="nav-item"><a class="nav-link " data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'role_add' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="role_add_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="addRoleSave"><?php echo $parameters['id'] ? bkntc__('SAVE ROLE') : bkntc__('ADD ROLE')?></button>
</div>
