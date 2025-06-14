<?php

namespace BookneticAddon\Inventory\Backend\Providers;

use BookneticAddon\Inventory\Model\Product;
use BookneticApp\Providers\DataTable\DataTable;
use Exception;

class ProductProvider {
	/**
	 * @throws Exception
	 */
	public static function getDataTable( int $currentPage, int $limit, string $sort, string $orderBy ): DataTable {
		$table = new DataTable();

		$table->setQuery( new Product() );
		$table->setOrderBy( $orderBy, $sort );
		$table->setLimit( $limit );
		$table->setCurrentPage( $currentPage );

		return $table;
	}
}