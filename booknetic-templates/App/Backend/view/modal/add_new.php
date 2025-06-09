<?php

use BookneticAddon\Templates\TemplatesAddon;
use BookneticSaaS\Providers\UI\TabUI;
use function BookneticAddon\Templates\bkntc__;

defined( 'ABSPATH' ) or die();

/**
 * @var array $parameters
 * @var bool $isEdit
*/

$isEdit = !! $parameters[ 'template' ]->id;
$tabs   = TabUI::get( 'template_add' )->getSubItems();

?>

<link rel="stylesheet" href="<?php echo TemplatesAddon::loadAsset( 'assets/css/add_new.css' )?>">
<script type="application/javascript" src="<?php echo TemplatesAddon::loadAsset( 'assets/js/add_new.js' )?>" id="add_new_JS" data-template-id="<?php echo $parameters[ 'template' ]->id ?>" ></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $isEdit ? bkntc__( 'Edit Template' ) : bkntc__( 'Add Template' )?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form id="templateForm">
            <div class="nowrap overflow-auto">
                <ul class="nav nav-tabs nav-light" data-tab-group="template_add">
                    <?php foreach ( $tabs as $tab ): ?>
                        <li class="nav-item"><a class="nav-link " data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="tab-content mt-5">
                <?php foreach ( $tabs as $tab ): ?>
                    <div class="tab-pane " data-tab-content="template_add_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__( 'CANCEL' )?></button>
    <button type="button" class="btn btn-lg btn-primary" id="save">
        <?php echo $isEdit ? bkntc__( 'SAVE' ) : bkntc__( 'ADD' ) ?>
    </button>
</div>