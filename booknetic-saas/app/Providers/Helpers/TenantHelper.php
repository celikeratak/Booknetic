<?php

namespace BookneticSaaS\Providers\Helpers;

use BookneticApp\Providers\DB\DB;
use BookneticSaaS\Models\Tenant;

class TenantHelper
{
    private static array $tables = [
        'data',
        'tenant_custom_data',
        'appearance',
        'appointments',
        'customers',
        'holidays',
        'locations',
        'service_categories',
        'services',
        'special_days',
        'staff',
        'timesheet',
        'tenant_billing',
        'workflows',
        'workflow_logs',
    ];

    /**
     * @var Tenant $tenant
    */
    public static function delete( $tenant )
    {
    	do_action( 'bkntcsaas_tenant_deleted', $tenant->id );
	    
        foreach ( self::$tables as $table ) {
            DB::DB()->delete( DB::table( $table ), [ 'tenant_id' => $tenant->id ] );
        }

        if ( $tenant->user_id > 0 ) {
            $userData = get_userdata( $tenant->user_id );
            if ( $userData && $userData->roles == [ 'booknetic_saas_tenant' ] ) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user( $tenant->user_id );
            }
        }

        Tenant::whereId( $tenant->id )->delete();
    }

    public static function tables(): array
    {
        return self::$tables;
    }
}
