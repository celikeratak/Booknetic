<?php

namespace BookneticAddon\Inventory\Backend\Providers;

use BookneticAddon\Inventory\Model\Product;
use BookneticAddon\Inventory\Model\ProductPurchaseLog as Log;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\DataTable\DataTable;
use BookneticApp\Providers\DataTable\DateFilter;

class ProductPurchaseLogProvider {
	public static function getDataTable( int $currentPage, int $limit, string $sort, string $orderBy, DateFilter $dateFilter, array $filter ): DataTable {
		$table  = new DataTable();
		$fields = [
			Log::getFieldAs( 'id', 'purchase_id' ),
			Product::getFieldAs( 'name', 'product_name' ),
			Customer::getFieldAs( 'first_name', 'customer_name' ),
			Service::getFieldAs( 'name', 'service_name' ),
			Log::getFieldAs( 'created_at', 'purchased_at' ),
			Log::getFieldAs( 'amount', 'amount' ),
			Appointment::getFieldAs( 'payment_status', 'status' )
		];

		$query = Log::select( $fields )
		            ->leftJoin( Appointment::class, [], Appointment::getField( 'id' ), Log::getField( 'appointment_id' ) )
		            ->leftJoin( Product::class, [], Product::getField( 'id' ), Log::getField( 'product_id' ) )
		            ->leftJoin( Customer::class, [], Customer::getField( 'id' ), Appointment::getField( 'customer_id' ) )
		            ->leftJoin( Service::class, [], Service::getField( 'id' ), Appointment::getField( 'service_id' ) );

		if ( ! empty( $filter[ 'service' ] ) ) {
			$query = $query->where( Service::getField( 'id' ), $filter[ 'service' ] );
		}

		if ( ! empty( $filter[ 'staff' ] ) ) {
			$query = $query->where( Staff::getField( 'id' ), $filter[ 'staff' ] );
		}

		if ( ! empty( $filter[ 'customer' ] ) ) {
			$query = $query->where( Customer::getField( 'id' ), $filter[ 'customer' ] );
		}

		if ( ! empty( $filter[ 'status' ] ) ) {
			$query = $query->where( Appointment::getField( 'payment_status' ), $filter[ 'status' ] );
		}

		$table->setQuery( $query );
		$table->setOrderBy( $orderBy, $sort );
		$table->setLimit( $limit );
		$table->setCurrentPage( $currentPage );
		$table->setDateFilter( $dateFilter, Log::getField( 'created_at' ) );

		return $table;
	}
}