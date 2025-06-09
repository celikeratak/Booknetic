<?php

defined( 'ABSPATH' ) or die();

use function BookneticAddon\UserRoleManager\bkntc__;

/**
 * @var array $parameters
 */
?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_name"><?php echo bkntc__('Name')?> <span class="required-star">*</span></label>
        <input type="text" class="form-control" id="input_name" value="<?php echo htmlspecialchars($parameters['role']['name'])?>">
    </div>

</div>

<?php if (\BookneticApp\Providers\Core\Capabilities::tenantCan('staff')) : ?>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="input_staff"><?php echo bkntc__('Staff')?> <span class="required-star">*</span></label>
            <select class="form-control" id="input_staff" multiple>
                <?php
                $selectedLocations = explode(',', $parameters['role']['staff']);
                foreach( $parameters['staff'] AS $location )
                {
                    echo '<option value="' . (int)$location['id'] . '"' . ( in_array($location['id'], $selectedLocations) ? ' selected' : '' ) .'>' . htmlspecialchars( $location['name'] ) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
<?php endif; ?>


<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_note"><?php echo bkntc__('Note')?></label>
        <textarea id="input_note" class="form-control"><?php echo htmlspecialchars($parameters['role']['note'])?></textarea>
    </div>
</div>
