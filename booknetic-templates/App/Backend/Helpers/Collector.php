<?php

namespace BookneticAddon\Templates\Backend\Helpers;

use BookneticAddon\Templates\Backend\Helpers\Traits\CollectorCount;
use BookneticAddon\Templates\Backend\Helpers\Traits\CollectorFetch;
use BookneticApp\Providers\Core\Templates\Data;
use BookneticApp\Providers\Helpers\Helper as RegularHelper;
use Exception;
use function BookneticAddon\Templates\bkntc__;

class Collector
{
    use Data, CollectorCount, CollectorFetch;

    /**
     * tenant id
     * @var int $tenantId
     * */
    private $tenantId;

    /**
     * @param int|null $tenantId
     * @param string $data
     */
    public function __construct( $tenantId = null, $data = '' )
    {
        $this->tenantId = $tenantId;
        $this->data     = json_decode( $data, true );
    }

    /**
     * @returns void
     * @throws Exception
     */
    public function fetch()
    {
        if ( ! $this->tenantId )
            return;

        $this->fetchLocations();

        $this->fetchServices();
        $this->fetchServiceCategories();

        $this->fetchStaff();
        $this->fetchServiceStaff();

        $this->fetchWorkflows();
        $this->fetchWorkflowActions();

        $this->fetchTimesheets();

        $this->fetchAppearances();

        $this->fetchSettings();

        do_action( 'bkntc_template_fetch_template_data', $this );
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode( $this->data );
    }

    /*----------------------------GETTERS----------------------------*/

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @return array
     */
    public function getCounts()
    {
        return apply_filters( 'bkntc_template_field_counts', [
            'locations'   => $this->locationsCount(),
            'services'    => $this->servicesCount(),
            'staff'       => $this->staffCount(),
            'workflows'   => $this->workflowsCount(),
            'appearances' => $this->appearancesCount(),
        ], $this );
    }

    /*----------------------------SETTERS----------------------------*/

    /**
     * @param string $cols
     * @return void
     * @throws Exception
     */
    public function setColumns( $cols )
    {
        $columns = json_decode( $cols, true );

        if ( ! $columns )
            throw new Exception( bkntc__( 'Invalid Data' ) );

        //this condition ensures the provided $columns array is in fact not malformed and its keys is correct.
        if ( array_diff( array_keys( $columns ), array_keys( Helper::baseColumns() ) ) )
            throw new Exception( bkntc__( 'Invalid Data' ) );

        // by merging $columns to baseColumns we are adding any new keys from base to the columns array, while preserving the data on the $columns
        $columns = array_merge( Helper::baseColumns(), $columns );

        $this->setData( 'columns', $columns );
    }

    /**
     * @param string $key
     * @param array $value
     * @return void
     */
    public function setData( $key, $value )
    {
        $this->data[ $key ] = $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addRow( $key, $value )
    {
        $this->data[ $key ][] = $value;
    }

    /*----------------------------SETTERS----------------------------*/

    /**
     * @param string $ogName
     * @param string $module
     * @return string
     */
    public function upload( $ogName, $module )
    {
        $ogPath  = RegularHelper::uploadedFile( $ogName, $module );

        $rand    = md5( base64_encode(rand( 1, 9999999 ) . microtime(true ) ) );
        $newName = $rand . '.' . pathinfo( $ogPath, PATHINFO_EXTENSION );
        $newPath = Helper::uploadedFile( $newName, sprintf( 'Templates/%s', $module ) );

        if ( ! copy( $ogPath, $newPath ) )
            return '';

        return $newName;
    }
}