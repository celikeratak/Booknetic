<?php

namespace BookneticAddon\Templates;

use BookneticAddon\Templates\Backend\Helpers\Helper;
use BookneticAddon\Templates\Model\Template;
use BookneticApp\Providers\Core\FSCodeAPI;
use BookneticApp\Providers\Core\Templates\Applier;
use BookneticApp\Providers\Helpers\Helper as RegularHelper;
use BookneticSaaS\Models\Tenant;

class Listener
{
    /**
     * @param boolean $_
     * @return true
     */
    public static function templateExists( $_ )
    {
        return true;
    }

    /**
     * @param array $_
     * @return array
     */
    public static function getTemplates( $_ )
    {
        $templates = Template::select( [ 'id', 'name', 'description', 'image' ] )
            ->where( 'is_default', 0 )
            ->fetchAll();

        if ( ! $templates )
            return [];

        for ( $i = 0; $i < count( $templates ); $i++ )
        {
            $templates[ $i ][ 'full_image_url' ] = Helper::templateImage( $templates[ $i ][ 'image' ] );
        }

        return $templates;
    }

    /**
     * @param array $_
     * @param int $id
     * @return array
     */
    public static function getTemplate( $_, $id )
    {
        if ( ! $id )
            return [];

        $template = Template::select( [ 'data', 'from_server' ] )->where( 'is_default', 0 )->whereId( $id )->fetch();

        if ( ! $template )
            return [];

        return $template->toArray();
    }

    /**
     * @param int $id
     * @return void
     */
    public static function applyDefaultTemplates( $id )
    {
        $defaultTemplates = Template::select( 'data' )->where( 'is_default', 1 )->fetchAll();

        if ( empty( $defaultTemplates ) )
            return;

        Applier::setTenantId( $id );

        Applier::applyMultiple( $defaultTemplates );

        Applier::unsetTenantId();
    }

    /**
     * @return void
     */
    public static function initialSetup()
    {
        if ( Helper::getOption( 'templates_initial_setup', 0 ) > 0 )
            return;

        self::fetchTemplates();
        self::updateTenantOptions();

        Helper::setOption( 'templates_initial_setup', 1 );
    }

    /**
     * @return void
     */
    private static function fetchTemplates()
    {
        $api = new FSCodeAPI();

        $templates = $api->post( 'templates/all' );

        if ( ! $templates )
            return;

        foreach ( $templates as $template )
        {
            //if an image exists, download
            if ( !! $template[ 'image' ] )
            {
                $path = Helper::uploadedFile( $template[ 'image' ], 'Templates' );

                FSCodeAPI::uploadFileFromUrl( $template[ 'full_image_url' ], $path );
            }

            Template::insert( [
                'name'        => $template[ 'name' ],
                'description' => $template[ 'description' ],
                'image'       => $template[ 'image' ],
                'data'        => $template[ 'data' ],
                'is_default'  => $template[ 'is_default' ]
            ] );
        }
    }

    /**
     * @return void
     */
    private static function updateTenantOptions()
    {
        $tenants = Tenant::select( 'id' )->fetchAll();

        if ( ! $tenants )
            return;

        foreach ( $tenants as $tenant )
        {
            Helper::setOption( 'selected_a_template', '1', $tenant[ 'id' ] );
        }
    }

    /**
     * @param string $ogName
     * @param string $module
     * @return string
     */
    public static function uploadImage( $ogName, $module )
    {
        $ogPath  = Helper::uploadedFile( $ogName, sprintf( 'Templates/%s', $module  ) );

        $rand    = md5( base64_encode(rand( 1, 9999999 ) . microtime(true ) ) );
        $newName = $rand . '.' . pathinfo( $ogPath, PATHINFO_EXTENSION );

        $newPath = RegularHelper::uploadedFile( $newName, $module );

        copy( $ogPath, $newPath  );

        return $newName;
    }
}