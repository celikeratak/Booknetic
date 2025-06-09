<?php

use BookneticAddon\Templates\Backend\Helpers\Helper;

defined( 'ABSPATH' ) or die();

/**
 * @var array $parameters
 * @var array $cols
*/

$columns  = $parameters[ 'columns' ];
$template = $parameters[ 'template' ];

$counts   = $template[ 'counts' ] ?: [];
?>

<?php foreach ( $columns as $k => $enabled ): ?>
    <?php $count = !! $counts && isset( $counts[ $k ] ) ? '(' . $counts[ $k ] . ')' : ''; ?>
    <?php if ( ! isset( Helper::baseColumns()[ $k ] ) ) continue; ?>
    <div class="form-group">
        <div class="form-control-checkbox">
            <label class="tenant-data-field-label" for="col_<?php echo $k ?>">
                <?php echo Helper::getLabel( $k ) ?> <b><?php echo $count ?></b>
            </label>
            <div class="fs_onoffswitch">
                <input type="checkbox" class="fs_onoffswitch-checkbox template-data-column" data-key="<?php echo $k ?>" id="col_<?php echo $k ?>" <?php echo $enabled ? 'checked' : '' ?>>
                <label class="fs_onoffswitch-label" for="col_<?php echo $k ?>"></label>
            </div>
        </div>
    </div>
<?php endforeach; ?>
