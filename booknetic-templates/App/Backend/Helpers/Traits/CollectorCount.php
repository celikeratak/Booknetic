<?php

namespace BookneticAddon\Templates\Backend\Helpers\Traits;

use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Workflow;

trait CollectorCount
{
    /**
     * @returns int
     */
    private function locationsCount()
    {
        if ( !! $this->get( 'locations' ) )
            return count( $this->get( 'locations' ) );

        if ( !! $this->tenantId )
            return Location::noTenant()->where( 'tenant_id', $this->tenantId )->count();

        return 0;
    }

    /**
     * @returns int
     */
    private function servicesCount()
    {
        if ( !! $this->get( 'services' ) )
            return count( $this->get( 'services' ) );

        if ( !! $this->tenantId )
            return Service::noTenant()->where( 'tenant_id', $this->tenantId )->count();

        return 0;
    }

    /**
     * @returns int
     */
    private function staffCount()
    {
        if ( !! $this->get( 'staff' ) )
            return count( $this->get( 'staff' ) );

        if ( !! $this->tenantId )
            return Staff::noTenant()->where( 'tenant_id', $this->tenantId )->count();

        return 0;
    }

    /**
     * @return int
     */
    private function workflowsCount()
    {
        if ( !! $this->get( 'workflows' ) )
            return count( $this->get( 'workflows' ) );

        if ( !! $this->tenantId )
            return Workflow::noTenant()->where( 'tenant_id', $this->tenantId )->count();

        return 0;
    }

    /**
     * @return int
     */
    private function appearancesCount()
    {
        if ( !! $this->get( 'appearances' ) )
            return count( $this->get( 'appearances' ) );

        if ( !! $this->tenantId )
            return Appearance::noTenant()->where( 'tenant_id', $this->tenantId )->count();

        return 0;
    }
}