<?php

namespace BookneticAddon\Packages\Model;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\Translation\Translator;

/**
 * @property-read int $id
 * @property-read string $image
 * @property-read string $color
 * @property-read string $name
 * @property-read string $duration
 * @property-read int $duration_value
 * @property-read int $is_public
 * @property-read string $description
 * @property-read string $services
 * @property-read string $discount_type
 * @property-read int $discount
 * @property-read float $price
 * @property-read float $final_price
 * @property-read int $tenant_id
 * @property-read string $payment_methods
 * @property-read string $notes
 */
class Package extends Model
{
    use MultiTenant;
	use Translator;

	protected static $translations = [ 'name', 'notes' ];

}