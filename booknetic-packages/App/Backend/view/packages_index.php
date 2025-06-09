<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Packages\PackagesAddon;

/***
 * @var mixed $parameters
 */

echo $parameters['table'];
?>

<script type="text/javascript" src="<?php echo PackagesAddon::loadAsset('assets/backend/js/packages.js' )?>"></script>
