<?php

namespace BookneticAddon\UserRoleManager\Backend;

use BookneticAddon\UserRoleManager\Model\UserRoleManager;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function \BookneticAddon\UserRoleManager\bkntc__;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

    public function add_new()
    {
        $id = Helper::_post( 'id', '0', 'integer' );

        if ( $id > 0 ) {
            $role = UserRoleManager::get( $id );
            $currentCapabilities = json_decode( $role->permissions, true );
        } else {
            $role = new Collection();
            $currentCapabilities = [];
        }

        TabUI::get( 'role_add' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'Role Details' ) )
            ->addView( __DIR__ . '/view/tab/role_details.php', [], 1 )
            ->setPriority( 1 );

        TabUI::get( 'role_add' )
            ->item( 'permissions' )
            ->setTitle( bkntc__( 'Permissions' ) )
            ->addView( __DIR__ . '/view/tab/role_permissions.php', [], 2 )
            ->setPriority( 2 );

        $capabilityList = Capabilities::getUserCapabilitiesList();

        $staff = Staff::fetchAll();

        return $this->modalView( 'add_new', [
            'id' => $id,
            'role' => $role,
            'staff' => $staff,
            'capabilityList' => $capabilityList,
            'current_capabilities' => $currentCapabilities,
        ] );
    }

    public function save_role()
    {
        $id	          = Helper::_post('id', '0', 'integer');
        $name         = Helper::_post('name', '', 'string');
        $staff        = Helper::_post('staff', '', 'string');
        $note         = Helper::_post('note', '', 'string');
        $capabilities = Helper::_post('capabilities', '', 'string');

        if ( $id > 0 ) {
            Capabilities::must("roles_edit");
        } else {
            Capabilities::must( "roles_add" );
        }

        if( empty($name) )
        {
            return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
        }

        if( $id > 0 )
        {
            $getOldInf = UserRoleManager::get( $id );

            if( !$getOldInf )
            {
                return $this->response(false, bkntc__('Role not found or permission denied!'));
            }
        }

        $sqlData = [
            'name'        => $name,
            'staff'       => $staff,
            'note'        => $note,
            'permissions' => $capabilities
        ];

        if( $id > 0 )
        {
            UserRoleManager::where('id', $id)->update( $sqlData );
        }
        else
        {
            UserRoleManager::insert( $sqlData );
        }

        return $this->response(true );
    }

    public function set_as_default()
    {
        $id = Helper::_post('id', '', 'int');

        if ( empty( $id ) )
        {
            return $this->response( false );
        }

        UserRoleManager::where( 'is_default', 1 )->update( [ 'is_default' => 0 ] );
        UserRoleManager::where( 'id', $id )->update( [ 'is_default' => 1 ] );

        return $this->response( true );
    }
}