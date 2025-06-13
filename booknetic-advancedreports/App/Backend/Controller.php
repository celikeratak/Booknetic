<?php

namespace BookneticAddon\AdvancedReports\Backend;

use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Capabilities;


class Controller extends \BookneticApp\Providers\Core\Controller
{

    public function index()
    {
        Capabilities::must('advancedreports');

        $data = [];
        $data['locations'] = Location::fetchAll();
        $data['staff'] = Staff::fetchAll();
        $data['services'] = Service::fetchAll();
        $data['status'] = Helper::getAppointmentStatuses();
        $this->view( 'index', $data );
    }
    public function table()
{
    \BookneticApp\Providers\Core\Capabilities::must('advancedreports');

    $data = [];
    $this->view('table', $data);
}


}

