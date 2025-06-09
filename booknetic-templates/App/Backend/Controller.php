<?php

namespace BookneticAddon\Templates\Backend;

use BookneticAddon\Templates\Model\Template;
use BookneticAddon\Templates\Backend\Helpers\Helper;
use BookneticApp\Providers\UI\Abstracts\AbstractDataTableUI;
use BookneticSaaS\Providers\UI\DataTableUI;
use function BookneticAddon\Templates\bkntc__;

class Controller extends \BookneticApp\Providers\Core\Controller
{
    public function index()
    {
        $templates = Template::select( [ 'id', 'name', 'image', 'is_default', 'created_at' ] )->orderBy( 'is_default DESC' );
        $dataTable = new DataTableUI( $templates );

        $dataTable->setTitle( bkntc__( 'Templates' ) );

        $dataTable->addAction( 'edit', bkntc__( 'Edit' ) );
        $dataTable->addAction( 'delete', bkntc__( 'Delete' ), function ( $ids )
        {
            Template::where( 'id', $ids )->delete();
        }, AbstractDataTableUI::ACTION_FLAG_BULK_SINGLE );

        $dataTable->addNewBtn( 'ADD TEMPLATE' );

        $dataTable->searchBy( [ 'name', 'created_at' ] );

        $dataTable->addColumns( bkntc__( 'ID' ), 'id' );
        $dataTable->addColumns( bkntc__( 'NAME' ), function ( $template ) {
            return Helper::templateCard( $template );
        }, [ 'order_by_field' => 'name', 'is_html' => true ] );

        $dataTable->addColumns( bkntc__( 'CREATED_AT' ), 'created_at' );

        $table = $dataTable->renderHTML();

        $this->view( 'index', [
            'table' => $table
        ] );
    }
}