<?php

namespace BookneticAddon\UserRoleManager;

use BookneticAddon\UserRoleManager\Backend\Ajax;
use BookneticAddon\UserRoleManager\Backend\Controller;
use BookneticAddon\UserRoleManager\Model\UserRoleManager;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\QueryBuilder;
use BookneticApp\Providers\UI\MenuUI;

function bkntc__ ($text, $params = [], $esc = true )
{
	return \bkntc__( $text, $params, $esc, UserRoleManagerAddon::getAddonTextDomain() );
}

class UserRoleManagerAddon extends AddonLoader
{
	public function init ()
	{
        Capabilities::registerTenantCapability('user_roles',bkntc__('User Role Manager'));
		Capabilities::register( 'appointment_book_outside_working_hours', bkntc__('Book outside working hours'), 'appointments' );

		add_filter( 'bkntc_query_builder_global_scope', [ Listener::class,'queryBuilderGlobalScope' ], 10, 2 );
        add_filter( 'bkntc_user_capability_filter', [ Listener::class, 'userCapabilityFilter' ], 20, 2 );
        add_action( 'bkntc_user_role_redirect_on_dashboard', [ Listener::class, 'redirectOnDashboard' ] );

        add_action( 'bkntc_staff_created', [ Listener::class, 'assignNewStaffDefaultRole' ] );

        self::addScopes();
    }

    public function initSaaS()
    {
        add_action( 'bkntcsaas_tenant_created', [ Listener::class, 'createDefaultRoleForTenant' ] );
    }

    public function initFrontend()
    {

    }

	public function initBackend ()
	{
        do_action( "bkntc_user_role_redirect_on_dashboard" );
        if( ! Capabilities::userCan( "roles") ) {
            return;
        }

        if( Capabilities::tenantCan( 'user_roles' ) )
        {
            MenuUI::get( 'user_role_manager' )
                ->setTitle( bkntc__( 'User Role Manager' ) )
                ->setIcon( 'fa fa-user-plus' )
                ->setPriority( 300 );
        }

        if( Capabilities::tenantCan('user_roles') )
        {
            Route::get( 'user_role_manager', Controller::class );
            Route::post( 'user_role_manager', Ajax::class );
        }

        add_filter('bkntc_add_tables_for_export', [ self::class, 'getAddonTables' ]);
    }

    public static function enqueueAssets ( $module, $action )
    {

    }

    private static function addScopes()
    {
        //these scopes are to be added on the backend part of the application
        if ( Permission::isBackEnd() ) {
            Service::addGlobalScope( 'my_services', [self::class, 'addServiceScopes']);
            ServiceCategory::addGlobalScope( 'my_service_categories', [self::class, 'addServiceCategoryScopes']);
        }
    }

    /**
     * @description Adds the given scopes if the user does not have the relevant permissions.
     *
     * @param QueryBuilder $builder
     * @param $queryType
     *
     * @return QueryBuilder|void
     */
    public static function addServiceScopes( QueryBuilder $builder, $queryType )
    {
        if( Permission::isAdministrator() || Permission::isSuperAdministrator() )
            return;

        if( apply_filters( 'bkntc_query_builder_global_scope', false, 'services' ) )
            return;

        $services = ServiceStaff::select( 'service_id' )->where( 'staff_id', Permission::myStaffId() );

        return $builder->where( Service::getField( 'id' ), $services );
    }

    /**
     * @description Adds the given scopes if the user does not have the relevant permissions.
     *
     * @param Model|QueryBuilder $builder
     * @param $queryType
     *
     * @return void
     */
    public static function addServiceCategoryScopes( QueryBuilder $builder, $queryType )
    {
        if( Permission::isAdministrator() || Permission::isSuperAdministrator() )
            return;

        if( apply_filters( 'bkntc_query_builder_global_scope', false, 'services' ) )
            return;

        $myServiceCategories = Service::select('DISTINCT category_id')->fetchAll();
        $myServiceCategories = array_column( $myServiceCategories, 'category_id' );

        $allCategories = ServiceCategory::withoutGlobalScope('my_service_categories')->select(['id', 'parent_id'])->fetchAll();
        $allCategories = array_combine(array_column($allCategories, 'id'), array_column($allCategories, 'parent_id'));

        $myCategoriesId = [];

        foreach ( $myServiceCategories as $serviceCategoryId ) {
            do {
                $myCategoriesId[$serviceCategoryId] = true;
            } while( isset( $allCategories[$serviceCategoryId] ) && $serviceCategoryId = $allCategories[$serviceCategoryId] );
        }
        $myCategoriesId = array_keys($myCategoriesId);

	    if ( ! empty( $myCategoriesId ) ) {
		    $builder->where( fn( $query ) => $query->where( ServiceCategory::getField( 'id' ), $myCategoriesId )
		                                           ->orWhere( ServiceCategory::getField( 'created_by' ), Permission::userId() ) );
	    } else {
		    $builder->where( ServiceCategory::getField( 'created_by' ), Permission::userId() );
	    }
    }

    public static function getAddonTables($tables)
    {
        $tables[] = UserRoleManager::getTableName();

        return $tables;
    }
}