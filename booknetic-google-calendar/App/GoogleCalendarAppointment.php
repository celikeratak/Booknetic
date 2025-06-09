<?php

namespace BookneticAddon\Googlecalendar;

use BookneticAddon\Googlecalendar\Helpers\CalendarHelper;
use BookneticAddon\Googlecalendar\Integration\GoogleCalendarService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Helper;

class GoogleCalendarAppointment
{
    public static function syncAppointment( $old, $new )
    {
        $oldZ = is_null($old) ? null : $old->getData('google_event_id');
        [
            $manuallyHandledStatus,
            $handleStatusManually,
            $executorFunctions
        ] = apply_filters( 'bkntc_handle_status_manually', false, $old, $new, $oldZ );

        $oldGZ = Listener::getGroupEventId( $old, $manuallyHandledStatus );
        $newGZ = Listener::getGroupEventId( $new, $manuallyHandledStatus );

        // depends on status and staff
        $newStateNeedsEvent =
            ! empty( $new ) &&
            ! empty( Staff::getData( $new->staff_id, 'google_access_token') ) &&
            ! empty( Staff::getData( $new->staff_id, 'google_calendar_id') ) &&
            in_array( $new->status, CalendarHelper::getNecessaryStatus() );

	    if ( ! $handleStatusManually ) {
		    $executorFunctions = [];
	    }

	    if ( ! $handleStatusManually && $newStateNeedsEvent ) {
            if (
                ( ! empty( $old ) && ! empty( $new ) )
                &&
                $old->staff_id == $new->staff_id
                &&
                (
                    ! empty( $oldZ ) && empty( $oldGZ )
                    || ! empty( $oldZ ) && $old->appointment_id == $new->appointment_id
                )
            ) {
                $executorFunctions[] = [ self::class, 'updateWithOldZ' ];
            } else {
                $executorFunctions[] = [ self::class, 'createNew' ];
            }
        }

	    if (
            ! $handleStatusManually
            &&
            (
                empty( $new )
                ||
                ! $newStateNeedsEvent && ! empty( $oldZ )
                ||
                ! empty( $old ) && $old->staff_id != $new->staff_id
            )
        ) {
            $executorFunctions[] = [ self::class, 'deleteOld' ];
        }

        $kwargs = [
            'old'   => $old,
            'new'   => $new,
            'oldZ'  => $oldZ,
            'oldGZ' => $oldGZ,
            'newGZ' => $newGZ,
        ];

	    foreach ( $executorFunctions as $exef ) {
		    if ( ! empty( $exef ) ) {
			    call_user_func_array( $exef, [ $kwargs ] );
		    }
	    }

	    if ( ! $handleStatusManually && empty( $new ) || ! $newStateNeedsEvent && ! empty( $oldZ ) ) {
		    Appointment::deleteData( $old->id, 'google_event_id' );
	    }
    }

    private static function updateWithOldZ( array $kwargs )
    {
        $new = $kwargs[ 'new' ];
        $oldZ = $kwargs[ 'oldZ' ];

        // reuse $oldZ
        self::update( $new, $oldZ, in_array( $new->status, Helper::getBusyAppointmentStatuses() ) );
    }
    private static function createNew( array $kwargs )
    {
        $new = $kwargs[ 'new' ];

        // create new fresh Z
        $new->setData('google_event_id', self::create( $new, in_array( $new->status, Helper::getBusyAppointmentStatuses() ) ));
    }

    private static function deleteOld( array $kwargs )
    {
        $old = $kwargs[ 'old' ];
        $oldZ = $kwargs[ 'oldZ' ];

        self::delete( $old, $oldZ );
    }

    private static function update( $appointmentInf, $eventId, bool $isBusy )
    {
        $gcService = new GoogleCalendarService();
        $gcService->setAccessToken($appointmentInf->staff_id);

        return $gcService->event()
            ->setCalendarId( Staff::getData( $appointmentInf->staff_id, 'google_calendar_id' ) )
            ->setAppointmentInf( $appointmentInf )
            ->update( $eventId, $isBusy );
    }

    private static function delete($appointmentInf, $eventId)
    {
        $gcService = new GoogleCalendarService();
        $gcService->setAccessToken($appointmentInf->staff_id);

        return $gcService->event()
            ->setCalendarId( Staff::getData( $appointmentInf->staff_id, 'google_calendar_id' ) )
            ->setAppointmentInf( $appointmentInf )
            ->delete($eventId);
    }

    private static function create( $appointmentInf, bool $isBusy = true )
    {
        $gcService = new GoogleCalendarService();
        $gcService->setAccessToken($appointmentInf->staff_id);

        return $gcService->event()
            ->setCalendarId( Staff::getData( $appointmentInf->staff_id, 'google_calendar_id' ) )
            ->setAppointmentInf( $appointmentInf )
            ->insert( $isBusy );
    }
}
