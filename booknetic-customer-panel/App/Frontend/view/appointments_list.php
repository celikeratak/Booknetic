<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use BookneticAddon\Customerpanel\CustomerPanelHelper;
use function BookneticAddon\Customerpanel\bkntc__;

/**
 * @var $parameters array
 */

foreach ( $parameters[ 'appointments' ] as $appointment ):
	Permission::setTenantId( $appointment->tenant_id );
    $clientTimeZoneIsOpen = Helper::getOption('client_timezone_enable', 'off') == 'on';

    $duration   = (int)$appointment->ends_at - (int)$appointment->starts_at;
    $dateFormat = Helper::isSaaSVersion() ? get_option( 'date_format', 'Y-m-d' ) : Helper::getOption( 'date_format', 'Y-m-d' );

    $clientDate = Helper::isSaaSVersion() ? Date::format( get_option( 'date_format', 'Y-m-d' ), $appointment->starts_at, false, $clientTimeZoneIsOpen ) : Date::datee( $appointment->starts_at, false, $clientTimeZoneIsOpen );
    $clientTime = $duration >= 24 * 60 * 60 ? '' : ( Helper::isSaaSVersion() ? Date::timeSQL( $appointment->starts_at ,false,$clientTimeZoneIsOpen ) : Date::time( $appointment->starts_at,false,$clientTimeZoneIsOpen ) );

    $originalDate = Helper::isSaaSVersion() ? Date::format( get_option( 'date_format', 'Y-m-d' ), $appointment->starts_at ) : Date::datee( $appointment->starts_at );
    $originalTime = $duration >= 24 * 60 * 60 ? '' : ( Helper::isSaaSVersion() ? Date::timeSQL( $appointment->starts_at ) : Date::time( $appointment->starts_at ) );

	?>
	<tr data-id="<?php echo $appointment->id;?>" data-tenant-id="<?php echo $appointment->tenant_id ?>"
        data-date="<?php echo $clientDate; ?>"
        data-date-original="<?php echo $originalDate; ?>" data-time-original="<?php echo $originalTime; ?>" data-time="<?php echo $clientTime; ?>" data-date-format="<?php echo $dateFormat; ?>" data-datebased="<?php echo ( int ) ($appointment->duration >= 24 * 60); ?>">
		<td class="pl-4"><?php echo htmlspecialchars($appointment->id)?></td>
		<?php if( Helper::isSaaSVersion() ):?>
			<td><a class="booknetic_company_link" target="_blank" href="<?php echo CustomerPanelHelper::getCompanyLink() ?>"><?php echo htmlspecialchars( Helper::getOption( 'company_name', Permission::tenantInf()->domain ) )?></a></td>
        <?php endif;?>
		<td><?php echo htmlspecialchars($appointment->service_name)?></td>
		<td><?php echo Helper::profileCard( $appointment->staff_name, $appointment->staff_profile_image, '', 'Staff' );?></td>
		<td class="td_datetime"><?php echo $clientDate . ' ' . $clientTime; ?></td>
		<td><?php echo Helper::price( $appointment->total_price )?></td>
		<td><?php echo Helper::secFormat( $duration )?></td>
		<td class="booknetic_appointment_status_td">
			<span class="booknetic_appointment_status_all" style="color: <?php echo htmlspecialchars( $appointment->status_color ) ?>"><?php echo htmlspecialchars( $appointment->status_text ) ?></span>
		</td>
		<td>
			<?php do_action( 'bkntc_customer_panel_appointment_actions', $appointment->id ); ?>
			<?php if( CustomerPanelHelper::canRescheduleAppointment( $appointment ) ): ?>
				<button class="booknetic_reschedule_btn" type="button" title="<?php echo bkntc__('Reschedule')?>"><i class="far fa-clock"></i></button>
			<?php endif; ?>
			<?php if ( CustomerPanelHelper::canChangeAppointmentStatus( $appointment ) ): ?>
				<button class="booknetic_change_status_btn" type="button" title="<?php echo bkntc__('Change Status')?>"><i class="fa fa-exchange-alt"></i></button>
			<?php endif; ?>
            <?php if ( Helper::getOption('hide_pay_now_btn_customer_panel', 'off')=='off' && $appointment->total_price != $appointment->paid_amount && ! in_array( $appointment->status, [ 'canceled', 'rejected' ] ) ): ?>
                <button class="booknetic_pay_now_btn" type="button" title="<?php echo bkntc__('Pay Now')?>"
                        data-tenant-id="<?php echo $appointment->tenant_id ?>"><i class="fa
                        fa-credit-card"></i></button>
            <?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
