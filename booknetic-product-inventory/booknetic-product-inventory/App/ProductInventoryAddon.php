<?php

namespace BookneticAddon\Inventory;

use BookneticAddon\Inventory\Backend\Ajax;
use BookneticAddon\Inventory\Backend\Controller;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Router\RestGroup;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Providers\UI\TabUI;

function bkntc__( $text, $params = [], $esc = true ) {
	return \bkntc__( $text, $params, $esc, ProductInventoryAddon::getAddonTextDomain() );
}

class ProductInventoryAddon extends AddonLoader {
	public static function getRestUrl(): string {
		return esc_url_raw( rest_url() ) . 'booknetic/v1/inventory/';
	}

	public function init() {
		Capabilities::registerTenantCapability( 'inventory', bkntc__( 'Product Inventory' ) );

		if ( ! Capabilities::tenantCan( 'inventory' ) ) {
			return;
		}

		Capabilities::register( 'inventory', bkntc__( 'Product Inventory' ) );
		Capabilities::register( 'inventory_add', bkntc__( 'Add new' ), 'inventory' );
		Capabilities::register( 'inventory_info', bkntc__( 'Info' ), 'inventory' );
		Capabilities::register( 'inventory_edit', bkntc__( 'Edit' ), 'inventory' );

		$group = new RestGroup( 'inventory' );

		$group->get( 'products-csv', [ Listener::class, 'productsCSV' ] );
		$group->get( 'logs-csv', [ Listener::class, 'logsCSV' ] );

		add_action( 'bkntc_appointment_requests_load', [ Listener::class, 'initProducts' ], 101 );
		add_action( 'bkntc_appointment_created', [ Listener::class, 'appointmentCreated' ] );
		add_action( 'bkntc_appointment_before_edit', [ Listener::class, 'appointmentBeforeEdited' ] );
		add_action( 'bkntc_appointment_after_edit', [ Listener::class, 'appointmentEdited' ] );
		add_action( 'bkntc_appointment_deleted', [ Listener::class, 'appointmentDeleted' ] );
        add_filter( 'bkntc_price_name' , [ Listener::class, 'priceName' ]);
	}

	public function initBackend() {
		if ( ! Capabilities::tenantCan( 'inventory' ) ) {
			return;
		}

		if ( ! Capabilities::userCan( 'inventory' ) ) {
			return;
		}

		Route::get( 'inventory', Controller::class );
		Route::post( 'inventory', Ajax::class );

		MenuUI::get( 'inventory' )
		      ->setTitle( bkntc__( 'Product Inventory' ) )
		      ->setIcon( 'fa fa-magic' )
		      ->setPriority( 820 );

        TabUI::get( 'appointments_add_new' )
             ->item( 'products' )
             ->setTitle( bkntc__( 'Products' ) )
             ->addView( __DIR__ . '/Backend/view/tabs/appointment_add_edit_modal.php' );

        TabUI::get( 'appointments_edit' )
             ->item( 'products' )
             ->setTitle( bkntc__( 'Products' ) )
             ->addView( __DIR__ . '/Backend/view/tabs/appointment_add_edit_modal.php' );

        TabUI::get( 'appointments_info' )
             ->item( 'products' )
             ->setTitle( bkntc__( 'Products' ) )
             ->addView( __DIR__ . '/Backend/view/tabs/appointment_info_modal.php', [ Listener::class,  'appointmentInfoProductsTab' ] );
	}

	public function initFrontend()
    {
		if ( ! Capabilities::tenantCan( 'inventory' ) )
        {
			return;
		}

		add_filter( 'bkntc_extras_step_components', [ Listener::class, 'addProductsToExtrasStep' ] );

        add_filter('bkntc_booking_panel_assets', function ( $assets )
        {
            $assets[] = [
                'id'    => 'booknetic-product-inventory-init',
                'type'  => 'js',
                'src'   => self::loadAsset( 'assets/frontend/js/init.js' ),
                'deps'  => ['booknetic']
            ];
            $assets[] = [
                'id'    => 'booknetic-product-inventory-init',
                'type'  => 'css',
                'src'   => self::loadAsset( 'assets/frontend/css/inventory.css' ),
                'deps'  => ['booknetic']
            ];

            return $assets;
        });
	}
}
