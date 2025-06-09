<?php

defined( 'ABSPATH' ) or die();

use function BookneticAddon\Googlecalendar\bkntc__;

/**
 * @var mixed $parameters
 */

if(isset($parameters['staff'])):

?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="google_calendar_select"><?php echo bkntc__('Google calendar')?></label>

        <div class="input-group">

            <select class="form-control" id="google_calendar_select" <?php echo (empty( $parameters['staff']->getData( 'google_access_token' ) ) ? 'disabled' : '') ?>>
                <?php
                if( !empty( $parameters['staff']->getData( 'google_calendar_id' ) ) )
                {
                    ?>
                    <option value="<?php echo htmlspecialchars($parameters['staff']->getData( 'google_calendar_id' ))?>"><?php echo htmlspecialchars($parameters['staff']->getData( 'google_calendar_id' ))?></option>
                    <?php
                }
                ?>
            </select>

            <div class="input-group-append">
                <button type="button" class="btn btn-lg btn-primary <?php echo (empty( $parameters['staff']->getData( 'google_access_token' ) ) ? '' : 'hidden') ?>" id="login_google_account">
                    <div class="login_google_account_container">
                        <img src="<?php echo \BookneticAddon\Googlecalendar\GoogleCalendarAddon::loadAsset('assets/icons/icons8-google-48.png') ?>" alt="">
                        <span><?php echo __('GOOGLE SIGN IN')?></span>
                    </div>
                </button>
                <button type="button" class="btn btn-lg btn-danger <?php echo (!empty( $parameters['staff']->getData( 'google_access_token' ) ) ? '' : 'hidden') ?>" id="logout_google_account"><?php echo __('GOOGLE SIGN OUT')?></button>
            </div>

            <?php if ( $parameters['staff']->getData( 'google_calendar_signed_in' ) === '1' ) :?>
                <?php $parameters['staff']->setData( 'google_calendar_signed_in', '0' ); ?>

                <div id="sync-previous-appointments-wrapper" class="form-group col-md-12 mt-4 d-flex hidden-important">
                    <input id="sync-previous-appointments" type="checkbox" class="form-control" checked="">
                    <label for="sync-previous-appointments">
                        <?php echo bkntc__('Sync previous appointments') ?>
                        <i class="fa fa-info-circle help-icon do_tooltip" data-content="<?php echo bkntc__( 'Enable this option to synchronize all relevant appointments booked prior to the initialization of Google Calendar. Please note that this process may take some time to complete.' ) ?>" data-original-title="" title=""></i>
                    </label>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php endif; ?>