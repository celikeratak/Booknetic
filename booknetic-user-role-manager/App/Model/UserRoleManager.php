<?php

namespace BookneticAddon\UserRoleManager\Model;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

/**

 */
class UserRoleManager extends Model
{
	use MultiTenant;

    static $tableName = 'user_role_managers';

}
