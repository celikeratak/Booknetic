<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;
use function BookneticAddon\Packages\bkntc__;

?>
<script type="application/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/workflow_event_package_booking_created.js')?>"></script>

<div class="fs-modal-title">
    <div class="title-text"><?php echo bkntc__('Edit event settings')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_called_from"><?php echo bkntc__('Called from')?></label>

                <select class="form-control" id="input_called_from">
                    <?php foreach ($parameters['call_from'] as $key => $call_from): ?>
                        <option value="<?php echo $key ?>" <?php echo $key == $parameters['called_from'] ? 'selected' : ''; ?>><?php echo $call_from ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label><?php print bkntc__( 'Locale filter' ); ?></label>

                <select class="form-control" name="locale" id="input_locale">
                    <?php foreach ( $parameters[ 'locales' ] as $lang ): ?>
                        <option value="<?php echo htmlspecialchars( $lang[ 'language' ] ); ?>" lang="<?php echo htmlspecialchars( current( $lang[ 'iso' ] ) ); ?>" <?php echo $parameters[ 'locale' ] == $lang[ 'language' ] ? 'selected' : ''; ?>><?php echo htmlspecialchars( $lang[ 'native_name' ] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div>
</div>


<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="eventSettingsSave"><?php echo bkntc__('SAVE')?></button>
</div>