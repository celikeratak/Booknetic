<?php

namespace BookneticAddon\Inventory\Backend;

use BookneticAddon\Inventory\Model\Product;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Request\Post;
use Exception;
use function BookneticAddon\Inventory\bkntc__;

class ProductObject {
	public int $id;
	public string $name;
	public int $quantity;
	public int $purchasePrice;
	public int $sellPrice;
	public string $services;
	public bool $disableSelect;
	public string $note;
	public bool $isEdit;
	private array $data = [];
	private string $image;

	public function __construct() {
		$this->id            = Post::int( 'id' );
		$this->name          = Post::string( 'name' );
		$this->quantity      = Post::int( 'quantity' );
		$this->purchasePrice = Post::int( 'purchasePrice' );
		$this->sellPrice     = Post::int( 'sellPrice' );
		$this->services      = Post::string( 'services' );
		$this->disableSelect = Post::int( 'disableSelect', 0, [ 0, 1 ] ) === 1;
		$this->note          = Post::string( 'note' );

		$this->isEdit = $this->id > 0;
	}

	/**
	 * @throws CapabilitiesException
	 */
	public function hasCapability(): void {
		if ( $this->isEdit ) {
			Capabilities::must( 'inventory_edit' );
		} else {
			Capabilities::must( 'inventory_add' );
		}
	}

	/**
	 * @throws Exception
	 */
	public function validate(): void {
		if ( empty( $this->name ) ) {
			throw new Exception( bkntc__( 'Please fill in the "Name" field correctly!' ) );
		}

		if ( empty( $this->quantity ) ) {
			throw new Exception( bkntc__( 'Please fill in the "Quantity" field correctly!' ) );
		}

		$this->validateServices();
	}

	/**
	 * @throws Exception
	 */
	private function validateServices(): void {
		if ( empty( $this->services ) ) {
			return;
		}

		$providedServices = explode( ',', trim( $this->services ) );

		if ( empty( $providedServices ) ) {
			return;
		}

		$validatedServiceCount = Service::whereId( $providedServices )->count();

		if ( $validatedServiceCount == count( $providedServices ) ) {
			return;
		}

		throw new Exception( bkntc__( 'An invalid service id is provided.' ) );
	}

	public function initData(): void {
		$this->data = [
			'name'           => $this->name,
			'quantity'       => $this->quantity,
			'purchase_price' => $this->purchasePrice,
			'sell_price'     => $this->sellPrice,
			'disable_select' => $this->disableSelect,
			'service_ids'    => $this->services,
			'note'           => $this->note,
		];
	}

	/**
	 * @throws Exception
	 */
	public function handleImage(): void {
		if ( ! isset( $_FILES[ 'image' ] ) || ! is_string( $_FILES[ 'image' ][ 'tmp_name' ] ) ) {
			return;
		}

		$pathInfo  = pathinfo( $_FILES[ "image" ][ "name" ] );
		$extension = strtolower( $pathInfo[ 'extension' ] );

		if ( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ] ) ) {
			throw new Exception( bkntc__( 'Only JPG and PNG images allowed!' ) );
		}

		$this->image = md5( base64_encode( rand( 1, 9999999 ) . microtime( true ) ) ) . '.' . $extension;
		$fileName    = Helper::uploadedFile( $this->image, 'Products' );

		move_uploaded_file( $_FILES[ 'image' ][ 'tmp_name' ], $fileName );

		$this->data[ 'image' ] = $this->image;
	}

	public function save(): int {
		if ( ! $this->isEdit ) {
			return $this->create();
		}

		$this->update();

		return $this->id;
	}

	public function create(): int {
		Product::insert( $this->data );

		return Product::lastId();
	}

	private function update(): void {
		if ( ! empty( $this->image ) ) {
			$this->removeOldImage();
		}

		Product::whereId( $this->id )->update( $this->data );
	}

	private function removeOldImage(): void {
		$oldInfo = Product::get( $this->id );

		if ( empty( $oldInfo[ 'image' ] ) ) {
			return;
		}

		$filePath = Helper::uploadedFile( $oldInfo[ 'image' ], 'Products' );

		if ( is_file( $filePath ) && is_writable( $filePath ) ) {
			unlink( $filePath );
		}
	}
}