<?php

namespace BookneticAddon\UserRoleManager;

use BookneticAddon\UserRoleManager\Model\UserRoleManager;
use BookneticApp\Config;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\Helper;

class Listener
{
    public static function userCapabilityFilter ( $can, $capability )
    {
        if( Permission::isAdministrator() )
            return true;

        if ( is_null( Config::getCapabilityCache() ) )
            self::setCapabilityCache();

        return isset( Config::getCapabilityCache()[ $capability ] ) && Config::getCapabilityCache()[ $capability ] !== 'off';
    }

    public static function queryBuilderGlobalScope($bool,$capability)
    {
        if(! empty(Config::getCapabilityCache()) && Capabilities::userCan( $capability ) && Capabilities::getUserCapabilityValue( $capability ) =='all')
            return true;

        return $bool;
    }

    public static function redirectOnDashboard()
    {
        if  ( Route::getCurrentModule() !== "dashboard" || Capabilities::userCan("dashboard") )
           return;

        foreach ( Capabilities::getUserCapabilitiesList() as $key =>  $value )
        {
            if ( Capabilities::userCan( $key ) )
            {
                Helper::redirect( Route::getURL( $key ) );
                return;
            }
        }
    }

    private static function setCapabilityCache()
    {
        //withoutGlobalScope added in order to avoid conflicting behaviour of a global scope - queryBuilderGlobalScope method's constraint
        $staff = Staff::withoutGlobalScope( 'user_id' )->where( 'user_id', Permission::userId() )->fetch();

        if( empty( $staff ) )
            return;

        $roles = UserRoleManager::whereFindInSet( 'staff', $staff->id )->fetchAll();
        $capabilities = [];

        foreach ( $roles as $role )
        {
            $permissions = json_decode( $role->permissions, true );

            if( empty( $capabilities ) )
            {
                $capabilities = $permissions;
                continue;
            }

            foreach ( $permissions as $key => $value )
            {
                if( $value != 'off' )
                {
                    $capabilities[ $key ] = $value;
                }
            }
        }

        Config::setCapabilityCache( $capabilities );
    }

    public static function assignNewStaffDefaultRole( int $id )
    {
        $defaultRole = UserRoleManager::where( 'is_default', 1 )->fetch();

        if ( empty( $defaultRole ) )
        {
            return;
        }

        $staffs = explode( ',', $defaultRole->staff );
        $staffs[] = $id;

        UserRoleManager::where( 'is_default', 1 )->update( [ 'staff' => implode( ',', $staffs ) ] );
    }

    public static function createDefaultRoleForTenant( $id )
    {
        UserRoleManager::noTenant()->insert( [
            'name'        => 'Default',
            'staff'       => '',
            'note'        => '',
            'is_default'  => 1,
            'tenant_id'   => $id,
            'permissions' => '{"dashboard":"off","appointments_add":"off","appointments_edit":"off","appointments_delete":"off","appointment_book_outside_working_hours":"off","appointments_customforms_tab":"off","appearance":"off","appearance_add":"off","appearance_edit":"off","appearance_delete":"off","appearance_select":"off","calendar":"off","customers_add":"off","customers_edit":"off","customers_delete":"off","customers_import":"off","customers_allow_to_login":"off","customers_delete_wordpress_account":"off","locations":"off","locations_add":"off","locations_edit":"off","locations_delete":"off","payments":"off","payments_edit":"off","workflow":"off","workflow_add":"off","workflow_edit":"off","workflow_delete":"off","services_add":"off","services_edit":"off","services_delete":"off","services_add_category":"off","services_edit_category":"off","services_delete_category":"off","services_add_extra":"off","services_edit_extra":"off","services_delete_extra":"off","staff_edit":"off","staff_add":"off","staff_delete":"off","staff_allow_to_login":"off","staff_delete_wordpress_account":"off","roles":"off","roles_add":"off","roles_edit":"off","roles_delete":"off","settings":"off","settings_general":"off","settings_booking_panel_steps":"off","settings_booking_panel_labels":"off","page_settings":"off","settings_payments":"off","settings_payment_gateways":"off","settings_company":"off","settings_business_hours":"off","settings_holidays":"off","settings_integrations_facebook_api":"off","settings_integrations_google_login":"off","settings_backup":"off","booking_limit_manager_settings":"off","customer_panel_settings":"off","google_calendar_settings":"off","vipps_settings":"off","woocommerce_settings":"off","twilio_sms_verification_settings":"off","telegram_bot_settings":"off","boostore":"off","back_to_wordpress":"off","custom-duration":"off","custom_forms":"off","custom_forms_add":"off","custom_forms_edit":"off","custom_forms_delete":"off","appointments":"off","customers":"off","services":"off","staff":"off"}'
        ] );
    }
}
