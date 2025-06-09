<?php

namespace BookneticAddon\Googlecalendar\Helpers;

use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;

class CalendarHelper
{

    public static function getNecessaryStatus()
    {
        $necessaryStatus = Helper::getOption('google_calendar_necessary_status', '' );
        $necessaryStatus = explode(',',$necessaryStatus);
        $appointmentStatuses = Helper::getAppointmentStatuses();
        $necessaryStatus = array_filter(array_values($necessaryStatus) , function ($status) use($appointmentStatuses)
        {
            return array_key_exists($status , $appointmentStatuses );
        });
        return empty($necessaryStatus) ? Helper::getBusyAppointmentStatuses() : $necessaryStatus;
    }

    public static function isRegularOrTenant(): bool
    {
        return ! Helper::isSaaSVersion() || ( Helper::isSaaSVersion() && ! empty( Permission::tenantId() ) );
    }

    public static function isRegularOrSAASAdmin(): bool
    {
        return ! Helper::isSaaSVersion() || ( Helper::isSaaSVersion() && empty( Permission::tenantId() ) );
    }
}