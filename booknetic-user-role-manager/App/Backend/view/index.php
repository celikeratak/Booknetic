<?php
defined( 'ABSPATH' ) or die();

use BookneticAddon\UserRoleManager\UserRoleManagerAddon;

/**
 * @var mixed $parameters
 */
echo $parameters['table'];
?>

<script type="text/javascript" src="<?php echo UserRoleManagerAddon::loadAsset('assets/backend/js/roles.js')?>"></script>
<link rel="stylesheet" href="<?php echo UserRoleManagerAddon::loadAsset('assets/backend/css/roles.css')?>">
