<?php

namespace BookneticAddon\AdvancedReports\Backend;

use BookneticAddon\AdvancedReports\Helpers\AdvancedReports;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

class Ajax extends \BookneticApp\Providers\Core\Controller
{
    public function getAppointmentsData()
{
    \BookneticApp\Providers\Core\Capabilities::must('advancedreports');

    global $wpdb;
    $wpkq_bkntc_appointments = $wpdb->prefix . 'bkntc_appointments';

    $status  = \BookneticApp\Providers\Helpers\Helper::_post('status', '', 'string');
    $payment = \BookneticApp\Providers\Helpers\Helper::_post('payment', '', 'string');
    $staff   = \BookneticApp\Providers\Helpers\Helper::_post('staff', '', 'string');

    $sql = "SELECT * FROM `{$wpkq_bkntc_appointments}` WHERE 1=1 ";
    $args = [];

    if(!empty($status)){
        $sql .= " AND status = %s ";
        $args[] = $status;
    }
    if(!empty($payment)){
        $sql .= " AND payment_method = %s ";
        $args[] = $payment;
    }
    if(!empty($staff)){
        $sql .= " AND staff_id = %d ";
        $args[] = (int)$staff;
    }

    $sql .= " ORDER BY id DESC LIMIT 100";

    $rows = $wpdb->get_results($wpdb->prepare($sql, $args), ARRAY_A);

    return $this->response(true, [
        'data' => $rows
    ]);
}



}