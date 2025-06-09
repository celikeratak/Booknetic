<?php

namespace BookneticAddon\Googlecalendar;

use BookneticApp\Models\Data;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Session;
use BookneticAddon\Googlecalendar\Model\StaffBusySlot;
use BookneticApp\Backend\Appointments\Helpers\CalendarService;
use BookneticAddon\Googlecalendar\Integration\GoogleCalendarService;

class Listener
{

    public static function add_calendar_row_to_staff_view($parameters)
    {
        $cid = $parameters[ 'staff' ][ 'id' ];
        $staffInfo = Staff::get( $cid );
        return ['staff'=>$staffInfo];
    }

    public static function save_staff_google_calendar ( $arr )
    {
	    if ( empty( $arr[ 'staff_id' ] ) || $arr[ 'is_edit' ] !== true ) {
            return $arr;
        }

	    $googleCalendarId = Helper::_post( 'google_calendar_id', '', 'string' );
        $accessToken = Staff::get( $arr[ 'staff_id' ] )->getData( 'google_access_token' );

	    if ( empty( $accessToken ) || empty( $googleCalendarId ) ) {
            return $arr;
        }

	    Staff::setData( $arr[ 'staff_id' ], 'google_calendar_id', $googleCalendarId );


	    if ( Helper::_post( 'sync_previous_appointments', '', 'string' ) === '1' ) {
            self::syncPreviousAppointments( $arr[ 'staff_id' ] );
        }

        return $arr;
    }

    public static function cronjob_google_calendar()
    {
        GoogleCalendarService::syncEventsOnBackground();
    }

    public static function merge_busy_slots_google_calendar( $busyRanges, CalendarService $calendarService )
    {
        $t0 = (new \DateTime($calendarService->dateFrom, $calendarService->clientTz))->setTimezone($calendarService->serverTz)->modify("-{$calendarService->serviceMarginBefore} minutes")->format("Y-m-d");
        $t1 = (new \DateTime($calendarService->dateTo, $calendarService->clientTz))->setTimezone($calendarService->serverTz)->modify("+{$calendarService->serviceMarginAfter} minutes")->format("Y-m-t");
        $staffGoogleCalendar = self::staffCalendar( $calendarService->getStaffInf(), $t0, $t1, $calendarService->getExcludeAppointmentId() );

        foreach ( $staffGoogleCalendar as $event )
        {
            $busyRanges[] = [ Date::epoch($event['date'] . " {$event['start_time']}") , Date::epoch($event['date'] . " {$event['start_time']}") + $event['duration'] * 60 ];
        }

        return $busyRanges;
    }

    public static function staffCalendar( $staff, $start_date, $end_date, $exclude_appointment_id )
    {
        if( is_numeric( $staff ) )
            $staff = Staff::get( $staff );

        $google_calendar_2way_sync = Helper::getOption('google_calendar_2way_sync', 'off', false);

        if( $google_calendar_2way_sync == 'off' || ! $staff || empty( $staff->getData( 'google_access_token' ) ) || empty( $staff->getData( 'google_calendar_id' ) ) )
            return [];

        if( $google_calendar_2way_sync == 'on_background' ) {
            $fetchBusySlotsFromDB = StaffBusySlot::where('staff_id', $staff['id'])->where('date', '>=', $start_date)->where('date', '<=', $end_date)->fetchAll();
            $all_events = [];

            foreach ( $fetchBusySlotsFromDB AS $busySlotInf )
            {
                $all_events[] = [
                    'date'					=>	$busySlotInf->date,
                    'start_time'			=>	$busySlotInf->start_time,
                    'duration'				=>	$busySlotInf->duration,
                    'extras_duration'		=>	0,
                    'buffer_before'			=>	0,
                    'buffer_after'			=>	0,
                    'service_id'			=>	0,
                    'staff_id'				=>	$staff['id'],
                    'weight'                =>	1,
                    'id'					=>	0
                ];
            }

            return $all_events;
        }

        $access_token = $staff->getData( 'google_access_token' );
        $calendar_id = $staff->getData( 'google_calendar_id' );

        $googleCalendarSerivce = new GoogleCalendarService();
        $googleCalendarSerivce->setAccessToken( $staff );

        return $googleCalendarSerivce->getEvents( $start_date, $end_date, $calendar_id, $exclude_appointment_id, $staff['id'] );
    }

    public static function merge_google_calendar_events($events, $startTime, $endTime , $staffFilterSanitized=[])
    {
        if( Session::get('show_gc_events', 'off') === "on" )
        {
            if( ! ( Permission::isAdministrator() || Capabilities::userCan( 'calendar' ) ) )
            {
                Helper::response( false, bkntc__( 'You do not have sufficient permissions to perform this action' ) );
            }

            $staffList = Staff::select(DB::table('staff').'.id as id, '.DB::table('staff').'.name as name')
                ->leftJoin('data', ['row_id', 'data_key'],  DB::table('data').'.row_id', DB::table('staff').'.id')
                ->where(DB::table('data').'.data_key', 'google_calendar_id')
                ->where('IFNULL('.DB::table('staff').'.id,\'\')', '<>', '' );

            if( ! empty( $staffFilterSanitized ) )
            {
                $staffList->where( Staff::getField( 'id' ) , $staffFilterSanitized );
            }

            $staffList = $staffList->groupBy( 'row_id' )
                ->fetchAll();

            $allStaffsEvents = [];
            foreach ( $staffList as $staffInf )
            {
               $access_token = $staffInf->getData('google_access_token');
               $calendar_id = $staffInf->getData('google_calendar_id');

               $googleCalendarEvents = [];

               if( $access_token && $calendar_id )
               {
                   $googleCalendarService = new GoogleCalendarService();
                   $googleCalendarService->setAccessToken( $staffInf );
                   $googleCalendarEvents = $googleCalendarService->getEvents($startTime, $endTime, $calendar_id, 0, $staffInf->id, true);
               }

               $allStaffsEvents = array_merge( $allStaffsEvents, $googleCalendarEvents );
            }

            foreach ($allStaffsEvents as $val)
			{
                $staff = Staff::get($val['staff_id']);
                $events[] = [
                    'appointment_id'        => 0,
                    'title'                 => htmlspecialchars($val['title']),
                    'event_title'           => htmlspecialchars(Helper::cutText($val['title'], 15)),
                    'color'                 => htmlspecialchars($val['color']),
                    'text_color'            => static::getContrastColor($val['color']),
                    'location_name'         => '',
                    'service_name'          => 'gc_event',
                    'staff_name'            => empty($staff) ? '' : htmlspecialchars($staff->name),
                    'staff_profile_image'   => empty($staff) ? '' : Helper::profileImage($staff->profile_image, 'Staff'),
                    'gc_icon'               => GoogleCalendarAddon::loadAsset('assets/icons/gc.png'),
                    'start_time'            => Date::time($val['start_time']),
                    'end_time'              => Date::time(Date::epoch($val['start_time']) + ($val['duration'] + $val['extras_duration']) * 60),
                    'start'                 => Date::dateSQL($val['date']) . 'T' . Date::format('H:i:s', $val['start_time']),
                    'end'                   => Date::format('Y-m-d\TH:i:s', Date::epoch($val['date'] . ' ' . $val['start_time']) + ($val['duration'] + $val['extras_duration']) * 60),
                    'customer'              => '',
                    'customers_count'       => 0,
                    'status'                => '',
                    'resourceId'            => empty( $staff ) ? '' : $staff->id,
                    'staff_id'              => empty( $staff ) ? '' : $staff->id
                ];
            }
        }

        return $events;
    }

    private static function getContrastColor( $hexcolor )
    {
        $r = hexdec(substr($hexcolor, 1, 2));
        $g = hexdec(substr($hexcolor, 3, 2));
        $b = hexdec(substr($hexcolor, 5, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 185) ? '#292D32' : '#FFF';
    }

    private static $oldAppointmentInf = null;

    public static function bkntc_appointment_before_mutation($appointmentId)
    {
        self::$oldAppointmentInf = is_null($appointmentId) ? null : Appointment::get($appointmentId);
    }

    public static function bkntc_appointment_after_mutation( $appointmentId )
    {
	    $newAppointment = is_null( $appointmentId ) ? null : Appointment::get( $appointmentId );
	    $staffId        = null;

	    if ( ! is_null( $newAppointment ) ) {
		    $staffId = $newAppointment[ 'staff_id' ];
	    } else if ( ! is_null( self::$oldAppointmentInf ) ) {
            $staffId = self::$oldAppointmentInf[ 'staff_id' ];
        }

	    if ( empty( $staffId ) ) {
		    return;
	    }

	    $staff = Staff::get( $staffId );

	    if ( empty( $staff ) ) {
		    return;
	    }

	    $accessToken = $staff->getData( 'google_access_token' );

	    if ( empty( $accessToken ) ) {
		    return;
	    }

	    /**
	     * todo:// there's a case where $appointmentId can be null,
	     *  this might cause some bugs that we are yet to uncover.
	     */

	    {
		    /**sync new appointment to google calendar*/
		    $googleId     = $staff->getData( 'google_calendar_id' );
		    $appointments = $staff->getData( 'last_synced_appointments' );
		    $appointments = json_decode( $appointments, true );

		    GoogleCalendarAppointment::syncAppointment( self::$oldAppointmentInf, $newAppointment );

		    $appointments[ base64_encode( $googleId ) ] = $appointmentId;

		    Staff::setData( $staffId, 'last_synced_appointments', json_encode( $appointments ) );
	    }
    }

	public static function getGroupEventId( $appointmentInf, $manuallyHandledStatus )
    {
        if ( is_null($appointmentInf) )
            return null;

        $row = Appointment::where('service_id' , $appointmentInf->service_id )
            ->where('location_id' , $appointmentInf->location_id)
            ->where('staff_id' , $appointmentInf->staff_id)
            ->where( 'starts_at' , $appointmentInf->starts_at )
            ->where( function ($query){
                $query->where('payment_method' ,'local')->orWhere('payment_status','paid');
            } )
            ->where(Appointment::getField('id'), '<>', $appointmentInf->id)
            ->where( Appointment::getField( 'status' ), '<>', $manuallyHandledStatus )
            ->innerJoin( Data::getTableName() , ['data_value'] , [
                [Data::getField('row_id'), '=', Appointment::getField('id')],
                [Data::getField('table_name'), '=', "'" . Appointment::getTableName() . "'"],
                [Data::getField('data_key'), '=', "'google_event_id'"]
            ] )
            ->select(Data::getField('data_value'), true)
            ->limit(1)
            ->fetch();

        if ( empty($row) )
            return null;

        return $row->data_value;

    }

    private static function syncPreviousAppointments( $staffId )
    {
	    $googleId                = Staff::getData( $staffId, 'google_calendar_id' );
	    $lastSyncedAppointments  = Staff::getData( $staffId, 'last_synced_appointments' );
	    $lastSyncedAppointments  = json_decode( $lastSyncedAppointments, true );
	    $encodedId               = base64_encode( $googleId );
	    $lastSyncedAppointmentId = $lastSyncedAppointments[ $encodedId ] ?? 0;

        $currentTimestamp = (new \DateTime())->getTimestamp();

        $appointments = Appointment::where( 'staff_id', $staffId )
            ->where( 'id', '>', (int)$lastSyncedAppointmentId )
            ->where( 'starts_at', '>', $currentTimestamp )
            ->fetchAll();

        if ( empty( $appointments ) )
        {
            return;
        }

	    $lastSyncedAppointments[ $encodedId ] = end( $appointments )[ 'id' ];

	    Staff::setData( $staffId, 'last_synced_appointments', json_encode( $lastSyncedAppointments ) );

        reset( $appointments );

        foreach ( $appointments as $a )
        {
            GoogleCalendarAppointment::syncAppointment( null, $a );
        }
    }
}