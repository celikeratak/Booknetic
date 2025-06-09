<?php

namespace BookneticAddon\Templates;

define( 'TEMPLATES_DIR', __DIR__ );

use BookneticAddon\Templates\Backend\Controller;
use BookneticAddon\Templates\Backend\Ajax;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticSaaS\Providers\Core\Route;
use BookneticSaaS\Providers\UI\MenuUI;

function bkntc__( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, TemplatesAddon::getAddonTextDomain() );
}

class TemplatesAddon extends AddonLoader
{
    public function init()
    {
        Listener::initialSetup();

        add_filter( 'bkntc_template_upload_image', [ Listener::class, 'uploadImage' ], 10, 2 );
    }

    public function initSaaS()
    {
        add_action( 'bkntcsaas_tenant_created', [ Listener::class, 'applyDefaultTemplates' ] );
    }

    public function initBackend()
    {
        add_filter( 'bkntc_localization' , function ( $lang )
        {
            return array_merge( [ 'use_default' => bkntc__( 'USE DEFAULT' ) ], $lang );
        } );

        add_filter( 'bkntc_template_exists', [ Listener::class, 'templateExists' ] );
        add_filter( 'bkntc_templates_get_all', [ Listener::class, 'getTemplates' ] );
        add_filter( 'bkntc_template_get', [ Listener::class, 'getTemplate' ], 10, 2 );
    }

    public function initSaaSBackend()
    {
        add_filter( 'bkntc_localization', function ( $lang )
        {
            return array_merge( [
                'empty_template_name' => bkntc__( 'Please, insert the template name first!' )
            ], $lang );
        } );

        Route::get( 'templates', Controller::class );
        Route::post( 'templates', Ajax::class, [ 'add_new', 'update', 'create', 'get_tenants', 'get_tenant_data_count' ] );

        MenuUI::get( 'templates' )
            ->setTitle( bkntc__( 'Templates' ) )
            ->setIcon( 'far fa-clone' )
            ->setPriority( 650 );
    }
}