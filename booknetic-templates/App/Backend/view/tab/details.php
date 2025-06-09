<?php

use BookneticAddon\Templates\Backend\Helpers\Helper;
use function BookneticAddon\Templates\bkntc__;

defined( 'ABSPATH' ) or die();

/**
 * @var array $parameters
 */

$template = $parameters[ 'template' ];
$isEdit   = !! $template->id;

?>

<div class="template_picture_div">
    <div class="template_picture">
        <input type="file" id="input_image" class="d-none">
        <div class="img-circle1"><img src="<?php echo Helper::templateImage( $template->image )?>"></div>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-12">
        <label for="name"><?php echo bkntc__( 'Name' ) ?> <span class="required-star">*</span></label>
        <input type="text" class="form-control" data-multilang="true" data-multilang-fk="0" id="name" value="<?php echo $isEdit ? $template->name : '' ?>">
    </div>
    <?php if ( ! $isEdit ): ?>
        <div class="form-group col-md-12">
            <label for="tenant">
                <?php echo bkntc__('Select Tenant')?> <span class="required-star">*</span>
                <i class="far fa-question-circle do_tooltip" data-content=" <?php echo bkntc__( 'Select Tenant to export its data as a template' ) ?>"></i>
            </label>
            <select id="tenant" class="form-control"></select>
        </div>
    <?php endif; ?>
    <div class="form-group col-md-12">
        <label for="description"><?php echo bkntc__( 'Description' ) ?></label>
        <textarea type="text" class="form-control" data-multilang="true" data-multilang-fk="0" id="description"><?php echo $isEdit ? $template->description : '' ?></textarea>
    </div>
    <div class="form-group col-md-12">
        <div class="form-control-checkbox">
            <label for="default"><?php echo bkntc__( 'Set As Default' ) ?></label>
            <div class="fs_onoffswitch">
                <input type="checkbox" class="fs_onoffswitch-checkbox" data-multilang="true" id="default" <?php echo $isEdit && $template->is_default ? 'checked' : '' ?>>
                <label class="fs_onoffswitch-label" for="default"></label>
            </div>
        </div>
    </div>
</div>