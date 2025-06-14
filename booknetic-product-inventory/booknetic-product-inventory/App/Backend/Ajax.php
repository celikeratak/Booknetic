<?php

namespace BookneticAddon\Inventory\Backend;

use BookneticAddon\Inventory\Backend\Providers\ProductProvider;
use BookneticAddon\Inventory\Backend\Providers\ProductPurchaseLogProvider;
use BookneticAddon\Inventory\Model\Product;
use BookneticAddon\Inventory\Model\ProductPurchaseLog;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\DataTable\DateFilter;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Request\Post;
use Exception;

class Ajax extends \BookneticApp\Providers\Core\Controller {
	/**
	 * @throws CapabilitiesException
	 */
	public function add_new() {
		Capabilities::must( 'inventory_add' );

		$id = Post::int( 'id' );

		$product = [];

		if ( ! empty( $id ) ) {
			$product = Product::get( $id );

			if ( ! empty( $product[ 'service_ids' ] ) ) {
				$serviceIds            = explode( ',', $product[ 'service_ids' ] );
				$product[ 'services' ] = Service::whereId( $serviceIds )->fetchAll();
			}
		}

		return $this->modalView( 'add_new', [
			'product' => $product
		] );
	}

	public function info() {
		$id = Post::int( 'id' );

		$product = [];

		if ( empty( $id ) ) {
			return $this->response( false );
		}

		return $this->modalView( 'add_new', [
			'product' => $product
		] );

	}

	/**
	 * @throws CapabilitiesException
	 * @throws Exception
	 */
	public function save() {
		$product = new ProductObject();

		$product->hasCapability();
		$product->validate();
		$product->initData();
		$product->handleImage();

		$id = $product->save();

		return $this->response( true, [ 'id' => $id ] );
	}

	public function remove_image() {
		$id = Helper::_post( 'id', '0', 'int' );

		$service = Product::whereId( $id )->select( 'image' )->fetch();

		if ( empty( $service[ 'image' ] ) ) {
			return $this->response( true );
		}

		$filePath = Helper::uploadedFile( $service[ 'image' ], 'Products' );

		if ( is_file( $filePath ) && is_writable( $filePath ) ) {
			unlink( $filePath );
		}

		Product::whereId( $id )->update( [ 'image' => null ] );

		return $this->response( true );
	}

	public function remove_products() {
		$ids = Post::array( 'ids' );

		ProductPurchaseLog::where( 'product_id', $ids )->delete();
		Product::whereId( $ids )->delete();

		return $this->response( true );
	}

	public function set_quantity() {
		$id       = Post::int( 'id' );
		$quantity = Post::int( 'quantity' );

		Product::whereId( $id )->update( [ 'quantity' => $quantity ] );

		return $this->response( true );
	}

	/**
	 * @throws Exception
	 */
	public function get_products() {
		$allowedFields = [ 'id', 'name', 'purchase_price', 'sell_price', 'quantity', 'created_at' ];
		$currentPage   = Post::int( 'currentPage' );
		$limit         = Post::int( 'limit', 10 );
		$sort          = Post::string( 'sort', 'desc', [ 'asc', 'desc' ] );
		$orderBy       = Post::string( 'orderBy', $allowedFields[ 0 ], $allowedFields );

		$table = ProductProvider::getDataTable( $currentPage, $limit, $sort, $orderBy );
		$page  = $table->getPage();

		return $this->response( true, [ 'page' => $page ] );
	}

	/**
	 * @throws Exception
	 */
	public function get_logs() {
		$allowedFields = [
			'purchase_id',
			'product_name',
			'customer_name',
			'service_name',
			'purchased_at',
			'amount',
			'status'
		];
		$currentPage   = Post::int( 'currentPage' );
		$limit         = Post::int( 'limit', 10 );
		$sort          = Post::string( 'sort', 'desc', [ 'asc', 'desc' ] );
		$orderBy       = Post::string( 'orderBy', $allowedFields[ 0 ], $allowedFields );
		$dateFilterArr = Post::array( 'dateFilter', [ 'type' => 'last_30_days' ] );
		$filter        = Post::array( 'filter' );

		$dateFilter = new DateFilter( $dateFilterArr );

		$table = ProductPurchaseLogProvider::getDataTable( $currentPage, $limit, $sort, $orderBy, $dateFilter, $filter );
		$page  = $table->getPage();

		return $this->response( true, [ 'page' => $page ] );
	}

	/**
	 * @throws Exception
	 */
	public function get_log_statistics() {
		$dateFilterArr = Post::array( 'dateFilter', [ 'type' => 'last_30_days' ] );

		$dateFilter = new DateFilter( $dateFilterArr );

		$fields = [
			ProductPurchaseLog::getCountFieldAs( 'id', 'total_sold' ),
			ProductPurchaseLog::getSumFieldAs( 'amount', 'total_revenue' ),
		];

		$statistics = ProductPurchaseLog::select( $fields )
		                                ->where( ProductPurchaseLog::getField( 'created_at' ), '>', $dateFilter->getFrom() )
		                                ->where( ProductPurchaseLog::getField( 'created_at' ), '<', $dateFilter->getTo() )
		                                ->fetch();

		return $this->response( true, [
			'stats' => $statistics
		] );
	}

    public function get_appointment_products() {
        $appointmentId  = Post::int( 'appointment' );

        $products = ProductPurchaseLog::where( 'appointment_id', $appointmentId )->fetchAll();

        return $this->response( true, [
            'products' => array_column( $products, 'product_id' )
        ] );
    }

    public function get_service_products() {
        $serviceId      = Post::int( 'service' );

        $products = Product::where( 'service_ids', '' )
                            ->orWhereFindInSet( 'service_ids', $serviceId )
                            ->fetchAll();

        return $this->response( true, [
            'products' => $products
        ] );
    }
}
