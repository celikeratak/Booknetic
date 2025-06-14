<?php

namespace BookneticAddon\Inventory\Backend;

use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Inventory\bkntc__;

class Controller extends \BookneticApp\Providers\Core\Controller {
	public function index() {
		$this->setLogsTab();
		$this->setProductsTab();

		$noProductImage   = Helper::assets( "icons/no-photo.svg" );
		$noUserImage      = Helper::assets( 'icons/no-user.webp' );
		$productPhotoPath = Helper::uploadFolderURL( 'Products' );

		$this->view( 'index', [
			'noUserImage'      => $noUserImage,
			'noProductImage'   => $noProductImage,
			'productPhotoPath' => $productPhotoPath
		] );
	}

	public function setLogsTab() {
		$staffs    = Staff::select( [ 'id', 'name' ] )->fetchAll();
		$services  = Service::select( [ 'id', 'name' ] )->fetchAll();
		$customers = Customer::select( [ 'id', 'first_name', 'last_name' ] )->fetchAll();

		TabUI::get( 'inventory' )
		     ->item( 'logs' )
		     ->setTitle( bkntc__( 'Logs' ) )
		     ->addView( __DIR__ . '/view/logs.php', [
			     'staffs'    => $staffs,
			     'services'  => $services,
			     'customers' => $customers,
			     'statuses'  => Helper::getPaymentStatuses()
		     ] )
		     ->setPriority( 1 );
	}

	public function setProductsTab() {

		$trashIcon = Helper::assets( "icons/trash.webp" );

		TabUI::get( 'inventory' )
		     ->item( 'inventory' )
		     ->setTitle( bkntc__( 'Products' ) )
		     ->addView( __DIR__ . '/view/products.php', [
			     'trashIcon' => $trashIcon,
		     ] )
		     ->setPriority( 2 );
	}
}