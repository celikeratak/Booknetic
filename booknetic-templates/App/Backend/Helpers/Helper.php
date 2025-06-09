<?php

namespace BookneticAddon\Templates\Backend\Helpers;

use BookneticAddon\Templates\TemplatesAddon;
use BookneticApp\Providers\DB\Collection;
use BookneticSaaS\Providers\Helpers\Helper as SaaSHelper;
use Exception;
use function BookneticAddon\Templates\bkntc__;

class Helper extends SaaSHelper
{
    /**
     * @param string $image
     * @return string
     */
    public static function templateImage( $image )
    {
        if ( ! $image )
            return TemplatesAddon::loadAsset( 'assets/images/no-photo.png' );

        return self::uploadedFileURL( $image, 'Templates' );
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public static function uploadImage()
    {
        if( ! isset( $_FILES[ 'image' ] ) || ! is_string( $_FILES[ 'image' ][ 'tmp_name' ] ) )
            return null;

        $path      = pathinfo( $_FILES[ 'image' ][ 'name' ] );
        $extension = strtolower( $path[ 'extension' ] );

        if( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ] ) )
            throw new Exception( bkntc__('Only JPG and PNG images allowed!' ) );

        $imageName = md5( base64_encode(rand( 1, 9999999 ) . microtime( true ) ) ) . '.' . $extension;
        $filePath  = self::uploadedFile( $imageName, 'Templates' );

        move_uploaded_file( $_FILES['image']['tmp_name'], $filePath );

        return $imageName;
    }

    /**
     * @param string|null $image
     * @return void
     */
    public static function deleteOldImage( $image )
    {
        if ( ! $image )
            return;

        $path = self::uploadedFile( $image, 'Templates' );

        if( ! is_file( $path ) || ! is_writable( $path ) )
            return;

        unlink( $path );
    }

    /**
     * @param Collection $template
     * @return string
     */
    public static function templateCard( $template )
    {
        $defaultStar = ! $template[ 'is_default' ] ? '' : '<i class="fa fa-star is_default" title="' . bkntc__( 'Default template' ) . '"></i>';

        return '<div class="user_visit_card">
					<div class="circle_image"><img src="' . self::templateImage( $template[ 'image' ] ) . '" alt=""></div>
					<div class="user_visit_details">
						<span>' . htmlspecialchars( $template[ 'name' ] ) . $defaultStar . '</span>
					</div>
				</div>';
    }

    /**
     * @return array
     */
    public static function baseColumns()
    {
        return apply_filters( 'bkntc_template_base_fields', [
            'locations'   => true,
            'services'    => true,
            'staff'       => true,
            'workflows'   => true,
            'timesheets'  => true,
            'appearances' => true,
            'settings'    => true,
        ] );
    }

    /**
     * @param $key
     * @return string
     */
    public static function getLabel( $key )
    {
        $labels = apply_filters( 'bkntc_template_field_labels', [
            'locations'   => bkntc__( 'Locations' ),
            'services'    => bkntc__( 'Services' ),
            'staff'       => bkntc__( 'Staff' ),
            'workflows'   => bkntc__( 'Workflows' ),
            'timesheets'  => bkntc__( 'Business Hours' ),
            'appearances' => bkntc__( 'Appearances' ),
            'settings'    => bkntc__( 'General Settings' ),
        ] );

        if ( isset( $labels[ $key ] ) )
            return $labels[ $key ];

        return '';
    }
}