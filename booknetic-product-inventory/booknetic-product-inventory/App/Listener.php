<?php

namespace BookneticAddon\Inventory;

use BookneticAddon\Inventory\Model\Product;
use BookneticAddon\Inventory\Model\ProductPurchaseLog;
use BookneticAddon\Inventory\Model\ProductPurchaseLog as Log;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests as Request;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticVendor\FileExporter\Exporters\CSVExporter;

class Listener {
	public static function logsCSV() {
		$fields = [
			Log::getFieldAs( 'id', 'purchase_id' ),
			Product::getFieldAs( 'id', 'product_id' ),
			Product::getFieldAs( 'name', 'product_name' ),
			Customer::getFieldAs( 'first_name', 'customer_name' ),
			Service::getFieldAs( 'name', 'service_name' ),
			Log::getFieldAs( 'created_at', 'purchased_at' ),
			Log::getFieldAs( 'amount', 'amount' ),
			Appointment::getFieldAs( 'payment_status', 'status' )
		];

		$data = Log::select( $fields )
		           ->leftJoin( Appointment::class, [], Appointment::getField( 'id' ), Log::getField( 'appointment_id' ) )
		           ->leftJoin( Product::class, [], Product::getField( 'id' ), Log::getField( 'product_id' ) )
		           ->leftJoin( Customer::class, [], Customer::getField( 'id' ), Appointment::getField( 'customer_id' ) )
		           ->leftJoin( Service::class, [], Service::getField( 'id' ), Appointment::getField( 'service_id' ) )
		           ->fetchAllAsArray();

		$exporter = new CSVExporter( $data );

		$exporter->withHeaders( [
			'Id',
			'Product Id',
			'Product Name',
			'Customer Name',
			'Service Name',
			'Purchased At',
			'Amount',
			'Status'
		] );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="product-purchase-logs.csv"' );

		echo $exporter->export()->getContent();
		exit;
	}

	public static function productsCSV() {
		$data = Product::select( [
			'id',
			'name',
			'price',
			'sell_price',
			'quantity',
			'created_at'
		] )->fetchAllAsArray();

		$exporter = new CSVExporter( $data );

		$exporter->withHeaders( [
			'Id',
			'Name',
			'Purchase Price',
			'Sell Price',
			'Quantity',
			'Created At'
		] );


		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="products.csv"' );

		echo $exporter->export()->getContent();
		exit;
	}

	public static function addProductsToExtrasStep( array $components ): array {
		$serviceId = Request::self()->currentRequest()->serviceId;

		$products = Product::where( 'quantity', '>', 0 )
		                   ->where( fn( $query ) => $query
			                   ->where( 'service_ids', '' )
			                   ->orWhereFindInSet( 'service_ids', $serviceId )
		                   )
		                   ->fetchAll();

        if( empty( $products ) )
            return $components;

		$htmlContent = Helper::renderView( __DIR__ . '/Frontend/view/product_list.php', [
			'products' => $products
		] );

		$components[] = $htmlContent;

		return $components;
	}

	public static function initProducts() {
		foreach ( Request::appointments() as $appointment ) {
			$productIds = $appointment->getData( 'products' );

			if ( empty( $productIds ) ) {
				continue;
			}

			$products = Product::whereId( $productIds )
			                   ->where( 'quantity', '>', 0 )
			                   ->fetchAll();

			if ( empty( $products ) ) {
				continue;
			}

			foreach ( $products as $product ) {
				$appointment->price( 'product-' . $product[ 'id' ], 'products' )
				            ->setLabel( $product[ 'name' ] )
				            ->setPrice( $product[ 'sell_price' ], true );
			}
		}
	}

    public static function appointmentCreated( AppointmentRequestData $data ) {
        $productIds = $data->getData( 'products' );

        if ( empty( $productIds ) ) {
            return;
        }

        $products = Product::whereId( $productIds )
                           ->where( 'quantity', '>', 0 )
                           ->fetchAll();

        if ( empty( $products ) ) {
            return;
        }

        //discount-un bura dəxlisi yoxdu
        foreach ( $products as $product ) {
            ProductPurchaseLog::insert( [
                'product_id'     => $product[ 'id' ],
                'appointment_id' => $data->appointmentId,
                'amount'         => $product[ 'sell_price' ],
            ] );
        }

        Product::whereId( $productIds )->update( [
            'quantity' => DB::field( 'quantity - 1' )
        ] );
    }

    public static function appointmentBeforeEdited( AppointmentRequestData $data ) {
        $productIds = $data->getData( 'products' );
        $appointmentId = $data->appointmentId;

        $getOldProducts = ProductPurchaseLog::where( 'appointment_id', $appointmentId )->fetchAll();

        $oldProductIds = $getOldProducts ? array_column( $getOldProducts, 'product_id' ) : [];

        if( ! empty( $productIds ) )
        {
            $oldProductIdsString = implode( ',', $oldProductIds );

            $quantityToCompare = $getOldProducts ? DB::field("0 - if(`id` in ({$oldProductIdsString}), 1,0)") : 0;

            $products = Product::whereId( $productIds )
                               ->where( 'quantity', '>', $quantityToCompare )
                               ->fetchAll();

            if ( count( $products ) !== count( $productIds ) ) {
                throw new \Exception( bkntc__('Some products are out of stock!') );
            }
        }
    }

    public static function appointmentEdited( AppointmentRequestData $data ) {
        $productIds = $data->getData( 'products' );
        $appointmentId = $data->appointmentId;

        $getOldProducts = ProductPurchaseLog::where( 'appointment_id', $appointmentId )->fetchAll();

        $oldProductIds = $getOldProducts ? array_column( $getOldProducts, 'product_id' ) : [];

        if( ! empty( $productIds ) )
        {
            $oldProductIdsString = implode( ',', $oldProductIds );

            $quantityToCompare = $getOldProducts ? DB::field("0 - if(`id` in ({$oldProductIdsString}), 1,0)") : 0;

            $products = Product::whereId( $productIds )
                               ->where( 'quantity', '>', $quantityToCompare )
                               ->fetchAll();

            if ( empty( $products ) ) {
                return;
            }
        }

        if( ! empty( $getOldProducts ) )
        {
            Product::whereId( $oldProductIds )->update( [
                'quantity' => DB::field( 'quantity + 1' )
            ] );

            ProductPurchaseLog::where( 'appointment_id', $appointmentId )->delete();
        }

        if( ! empty( $productIds ) )
        {
            //discount-un bura dəxlisi yoxdu
            foreach ( $products as $product ) {
                ProductPurchaseLog::insert( [
                    'product_id'     => $product[ 'id' ],
                    'appointment_id' => $data->appointmentId,
                    'amount'         => $product[ 'sell_price' ],
                ] );
            }

            Product::whereId( $productIds )->update( [
                'quantity' => DB::field( 'quantity - 1' )
            ] );
        }
    }

    public static function appointmentDeleted( $appointmentId ) {
        $getOldProducts = ProductPurchaseLog::where( 'appointment_id', $appointmentId )->fetchAll();

        if( ! empty( $getOldProducts ) )
        {
            Product::whereId( array_column( $getOldProducts, 'product_id' ) )->update( [
                'quantity' => DB::field( 'quantity + 1' )
            ] );

            ProductPurchaseLog::where( 'appointment_id', $appointmentId )->delete();
        }
    }

    public static function appointmentInfoProductsTab ( $appointmentId )
    {
        $productLogs = ProductPurchaseLog::where( 'appointment_id', $appointmentId )->fetchAll();
        $products = ! empty( $productLogs ) ? Product::whereId( array_column( $productLogs, 'product_id' ) )->fetchAll() : [];

        return [
            'products'  =>  $products
        ];
    }

    public static function priceName( $key ) {
        if ( $key === "products" ) {
            return bkntc__('Products price');
        }

        return $key;
    }
}