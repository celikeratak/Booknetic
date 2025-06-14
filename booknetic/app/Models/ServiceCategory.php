<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Auditable;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\Translation\Translator;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $parent_id
 * @property-read int $tenant_id
 */
class ServiceCategory extends Model
{
	use MultiTenant, Translator, Auditable {
		MultiTenant::booted as private tenantBoot;
		Auditable::booted as private audBoot;
	}

	protected static $tableName = 'service_categories';

    protected static $translations = [ 'name' ];

	public static function booted()
	{
		self::tenantBoot();
		self::audBoot();
	}
}