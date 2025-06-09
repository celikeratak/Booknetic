<?php

namespace BookneticAddon\Templates\Backend;

use BookneticAddon\Templates\Backend\Helpers\Collector;
use BookneticAddon\Templates\Backend\Helpers\Helper;
use BookneticAddon\Templates\Model\Template;
use BookneticApp\Providers\DB\Collection;
use BookneticSaaS\Providers\UI\TabUI;
use Exception;
use function BookneticAddon\Templates\bkntc__;

class Ajax extends \BookneticApp\Providers\Core\Controller
{
    /**
     * @throws Exception
     */
    public function add_new()
    {
        $template = $this->getTemplate();
        $columns  = $this->getColumns( $template );

        //sets tabs to the modal
        TabUI::get( 'template_add' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'DETAILS' ) )
            ->addView( TEMPLATES_DIR . '/Backend/view/tab/details.php' )
            ->setPriority( 1 );

        TabUI::get( 'template_add' )
            ->item( 'data' )
            ->setTitle( bkntc__( 'DATA' ) )
            ->addView( TEMPLATES_DIR . '/Backend/view/tab/data.php' )
            ->setPriority( 2 );

        return $this->modalView( 'add_new', [
            'template' => $template,
            'columns'  => $columns
        ] );
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        $tenantId    = Helper::_post( 'tenant', 0, 'int' );
        $name        = Helper::_post( 'name', '', 'string' );
        $default     = Helper::_post( 'default', 0, 'int', [ 0, 1 ] );
        $description = Helper::_post( 'description', '', 'string' );
        $cols        = Helper::_post( 'columns', '', 'string' );

        if ( ! $name || ! $cols )
            return $this->response( false );

        $collector = new Collector( $tenantId );

        $collector->setColumns( $cols );
        $collector->fetch();

        Template::insert( [
            'name'        => $name,
            'is_default'  => $default,
            'description' => $description,
            'data'        => $collector->toJson(),
            'image'       => Helper::uploadImage()
        ] );

        return $this->response( true );
    }

    /**
     * @throws Exception
     */
    public function update()
    {
        $id          = Helper::_post( 'id', 0, 'int' );
        $default     = Helper::_post( 'default', 0, 'int', [ 0, 1 ] );
        $name        = Helper::_post( 'name', '', 'string' );
        $description = Helper::_post( 'description', '', 'string' );
        $cols        = Helper::_post( 'columns', '', 'string' );

        if ( $id <= 0 || ! $name || ! $cols )
            return $this->response( false );

        $template = Template::select( [ 'image', 'data' ] )->whereId( $id )->fetch();

        if ( ! $template )
            return $this->response( false );

        $columns = json_decode( $cols, true );

        if ( ! $columns )
            throw new Exception( bkntc__( 'Invalid Data' ) );

        $data              = json_decode( $template[ 'data' ], true );
        $data[ 'columns' ] = array_merge( Helper::baseColumns(), $columns );

        $row = [
            'name'        => $name,
            'is_default'  => $default,
            'description' => $description,
            'data'        => json_encode( $data )
        ];

        $image = Helper::uploadImage();

        if ( ! empty( $image ) )
        {
            $row[ 'image' ] = $image;

            Helper::deleteOldImage( $template[ 'image' ] );
        }

        Template::whereId( $id )->update( $row );

        return $this->response( true );
    }

    /**
     * @throws Exception
     */
    public function get_tenant_data_count()
    {
        $id = Helper::_post( 'id', 0, 'int' );

        $counts = ( new Collector( $id ) )->getCounts();

        return $this->response( true, [ 'counts' => $counts ] );
    }

    public function get_tenants()
    {
        return $this->response(true, [ 'results' => Helper::getTenants() ] );
    }

    //-------------------HELPERS-------------------//

    /**
     * @description returns an empty Template object if the id is not provided
     * @return Template|Collection
     * @throws Exception
     */
    public function getTemplate()
    {
        $id = Helper::_post( 'id', 0, 'int' );

        if ( empty( $id ) )
            return new Collection();

        $template = Template::  get( $id );

        if ( empty( $template ) )
            throw new Exception( bkntc__( 'Couldn\'t find the template!' ) );

        $collector = new Collector( null, $template[ 'data' ] );

        $template[ 'collector' ] = $collector;
        $template[ 'counts' ]    = $collector->getCounts();

        return $template;
    }

    /**
     * @param Collection $template
     * @return array
     */
    public function getColumns( $template )
    {
        if ( ! $template[ 'id' ] )
            return Helper::baseColumns();

        /**
         * @var Collector $collector
         */
        $collector = $template[ 'collector' ];

        return $collector->get( 'columns' );
    }
}