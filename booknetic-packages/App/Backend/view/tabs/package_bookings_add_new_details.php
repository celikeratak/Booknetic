<?php

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

use function BookneticAddon\Packages\bkntc__;

?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_package"><?php echo bkntc__('Package')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_packages">
            <option></option>
            <?php
            foreach ( $parameters['packages'] AS $package )
            {
                echo '<option value="' . (int)$package['id'] . '" '.($parameters['info']['package_id']==(int)$package['id']?' selected':'').'>' . htmlspecialchars( $package['name'] ) . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_customer"><?php echo bkntc__('Customer')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_customer">
            <?php if( $parameters['customer'] ):?>
                <option value="<?php echo (int)$parameters['customer']->id?>"><?php echo htmlspecialchars($parameters['customer']->full_name)?></option>
            <?php endif;?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_note"><?php echo bkntc__('Note')?> </label>
        <textarea id="input_note" class="form-control" cols="30" rows="10"><?php print htmlspecialchars($parameters['info']['note'] ?: '')?></textarea>
    </div>
</div>
