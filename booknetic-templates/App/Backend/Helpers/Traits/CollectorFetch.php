<?php

namespace BookneticAddon\Templates\Backend\Helpers\Traits;

use BookneticAddon\Templates\Backend\Helpers\Helper;
use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use Exception;
use function BookneticAddon\Templates\bkntc__;

trait CollectorFetch
{
    /**
     * @return void
     */
    private function fetchLocations()
    {
        $locations = Location::noTenant()->where( 'tenant_id', $this->tenantId )->fetchAll();

        foreach ( $locations as $location )
        {
            unset( $location[ 'tenant_id' ] );

            if ( !! $location[ 'image' ] )
            {
                $location[ 'image' ] = $this->upload( $location[ 'image' ], 'Locations' );
            }

            $this->addRow( 'locations', $location->toArray() );
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function fetchServices()
    {
        $services = Service::noTenant()->where( 'tenant_id', $this->tenantId )->fetchAll();

        foreach ( $services as $service )
        {
            unset( $service[ 'tenant_id' ] );

            if ( !! $service[ 'image' ] )
            {
                $service[ 'image' ] = $this->upload( $service[ 'image' ], 'Services' );
            }

            $this->addRow( 'services', $service->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchServiceCategories()
    {
        if ( empty( $this->get( 'services' ) ) )
            return;

        $sCategories = ServiceCategory::select( [ 'id', 'name', 'parent_id' ] )
            ->noTenant()
            ->where( 'tenant_id', $this->tenantId )
            ->fetchAll();

        foreach ( $sCategories as $sCategory )
        {
            $this->addRow( 'serviceCategories', $sCategory->toArray() );
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function fetchStaff()
    {
        if ( empty( $this->get( 'locations' ) ) )
            throw new Exception( bkntc__( 'Unable to export the staffs. No active locations found. Please, make sure the locations checkbox above is enabled and you have at least 1 location attached to a staff.' ) );

        $staff = Staff::noTenant()->where( 'tenant_id', $this->tenantId )->fetchAll();

        foreach ( $staff as $s ) {
            unset( $s[ 'tenant_id' ] );
            unset( $s[ 'user_id' ] );

            if ( !! $s[ 'profile_image' ] )
            {
                $s[ 'profile_image' ] = $this->upload( $s[ 'profile_image' ], 'Staff' );
            }

            $this->addRow( 'staff', $s->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchServiceStaff()
    {
        if ( empty( $this->get( 'staff' ) ) || empty( $this->get( 'services' ) ) )
            return;

        $staffs = Staff::noTenant()->where( 'tenant_id', $this->tenantId )->select( 'id' );
        $sStaff = ServiceStaff::where( 'staff_id', 'in', $staffs )->fetchAll();

        foreach ( $sStaff as $ss )
        {
            unset( $ss[ 'id' ] );

            $this->addRow( 'serviceStaff', $ss->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchWorkflows()
    {
        $workflows = Workflow::noTenant()
            ->where( 'tenant_id', $this->tenantId )
            ->fetchAll();

        foreach ( $workflows as $workflow )
        {
            unset( $workflow[ 'tenant_id' ] );

            $this->addRow( 'workflows', $workflow->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchWorkflowActions()
    {
        if ( ! $this->get( 'workflows' ) )
            return;

        $workflows = Workflow::noTenant()
            ->where( 'tenant_id', $this->tenantId )
            ->select( 'id' );
        $actions   = WorkflowAction::select( [ 'workflow_id', 'driver', 'data', 'is_active' ] )
            ->where( 'workflow_id', 'in', $workflows )
            ->fetchAll();

        foreach ( $actions as $action )
        {
            $this->addRow( 'workflowActions', $action->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchTimesheets()
    {
        $timesheets = Timesheet::select( [ 'service_id', 'staff_id', 'timesheet' ] )
            ->noTenant()
            ->where( 'tenant_id', $this->tenantId )
            ->fetchAll();

        foreach ( $timesheets as $timesheet )
        {
            $this->addRow( 'timesheets', $timesheet->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchAppearances()
    {
        $appearances = Appearance::select( [ 'name', 'is_default', 'colors', 'height', 'fontfamily', 'custom_css' ] )
            ->noTenant()
            ->where( 'tenant_id', $this->tenantId )
            ->fetchAll();

        foreach ( $appearances as $appearance )
        {
            $this->addRow( 'appearances', $appearance->toArray() );
        }
    }

    /**
     * @return void
     */
    private function fetchSettings()
    {
        $this->setData( 'settings', [
            //General Settings
            'week_starts_on'           => $this->getOption( 'week_starts_on', 'sunday' ),
            'date_format'              => $this->getOption( 'date_format', 'Y-m-d' ),
            'time_format'              => $this->getOption( 'time_format', 'H:i' ),
            'timezone'                 => $this->getOption( 'timezone', '' ),
            //Payment Settings
            'currency'                 => $this->getOption( 'currency', 'USD' ),
            'currency_symbol'          => $this->getOption( 'currency_symbol', '$' ),
            'currency_format'          => $this->getOption( 'currency_format', '1' ),
            'price_number_format'      => $this->getOption( 'price_number_format', '1' ),
            'price_number_of_decimals' => $this->getOption( 'price_number_of_decimals', '2' ),
        ] );
    }

    /**
     * @param $name
     * @param $default
     * @return false|mixed|string|null
     */
    private function getOption( $name, $default )
    {
        return Helper::getOption( $name, $default, $this->tenantId );
    }
}