<?php

use BookneticAddon\Templates\TemplatesAddon;

defined( 'ABSPATH' ) or die();

/**
 * @var array $parameters
*/

echo $parameters[ 'table' ];
?>

<link rel="stylesheet" type="text/css" href="<?php echo TemplatesAddon::loadAsset( 'assets/css/templates.css' )?>" />
<script type="application/javascript" src="<?php echo TemplatesAddon::loadAsset( 'assets/js/templates.js' )?>"></script>
