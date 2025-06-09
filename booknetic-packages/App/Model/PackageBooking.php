<?php

namespace BookneticAddon\Packages\Model;

use BookneticApp\Models\Customer;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

/**
 * @property-read int $id
 * @property-read int $created_at
 * @property-read int $customer_id
 * @property-read int $package_id
 * @property-read string $note
 * @property-read string $status
 * @property-read string $payment_method
 * @property-read string $payment_status
 * @property-read float $total_amount
 * @property-read float $paid_amount
 * @property-read string $locale
 * @property-read string $client_timezone
 * @property-read int $tenant_id
 * @property-read string $appointments
 * @property-read int $expires_on
 *
 * @property-read Customer $customer
 * @property-read Package $package
 *
 */
class PackageBooking extends Model
{
    use MultiTenant;

    public static $relations = [
        'customer'      => [ Customer::class, 'id', 'customer_id' ],
        'package'       => [ Package::class, 'id', 'package_id' ]
    ];

}
