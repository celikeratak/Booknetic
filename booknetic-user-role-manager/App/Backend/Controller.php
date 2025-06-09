<?php

namespace BookneticAddon\UserRoleManager\Backend;

use BookneticAddon\UserRoleManager\Model\UserRoleManager;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\UI\Abstracts\AbstractDataTableUI;
use BookneticApp\Providers\UI\DataTableUI;
use function \BookneticAddon\UserRoleManager\bkntc__;

class Controller extends \BookneticApp\Providers\Core\Controller
{

    public function index()
    {
        $dataTable = new DataTableUI( new UserRoleManager() );

        if ( Capabilities::userCan( "roles_edit" ) ) {
            $dataTable->addAction('set_as_default', bkntc__('Set as default') );
            $dataTable->addAction('edit', bkntc__('Edit'));
        }

        if ( Capabilities::userCan( "roles_delete" ) ) {
            $dataTable->addAction('delete', bkntc__('Delete'), [static::class, '_delete'], AbstractDataTableUI::ACTION_FLAG_SINGLE | AbstractDataTableUI::ACTION_FLAG_BULK);
        }

        $dataTable->setTitle(bkntc__('Roles'));
        if ( Capabilities::userCan( "roles_add" ) ) {
            $dataTable->addNewBtn(bkntc__('ADD ROLE'));
        }
        $dataTable->addColumns(bkntc__('ID'), 'id');
        $dataTable->addColumns(bkntc__('NAME'), function( $data )
        {
            return htmlspecialchars($data['name']) . ( $data['is_default'] ? '<i class="fa fa-star is_default" title="'.bkntc__('Default Role').'"></i>' : '' );
        }, ['order_by_field' => 'name', 'is_html' => true]);

        $table = $dataTable->renderHTML();

        $this->view( 'index', ['table' => $table] );
    }

    public static function _delete($ids)
    {
        Capabilities::must( 'roles_delete' );

        foreach ( $ids AS $id )
        {
            UserRoleManager::where('id',$id)->delete();
        }
    }

}
