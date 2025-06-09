<?php

defined('ABSPATH') or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Packages\PackagesAddon;

/***
 * @var mixed $parameters
 */

echo $parameters['table'];
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/datatables/main.css') ?>">
<link rel="stylesheet" href="<?php echo PackagesAddon::loadAsset('assets/backend/css/package_bookings.css') ?>">
<script type="text/javascript" src="<?php echo \BookneticAddon\Packages\PackagesAddon::loadAsset('assets/backend/js/package_bookings.js')?>"></script>

