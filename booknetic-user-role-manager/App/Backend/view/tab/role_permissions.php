<?php

defined( 'ABSPATH' ) or die();
use function BookneticAddon\UserRoleManager\bkntc__;

/**
 * @var mixed $parameters
 */
?>

<?php foreach ( $parameters[ 'capabilityList' ] as $slug => $info ): ?>
    <?php
    $select = in_array( $slug, [ 'appointments', 'customers', 'staff', 'services' ] );
    $currCapability = $parameters[ 'current_capabilities' ][ $slug ] ?? '';

    ?>
    <div class="form-group">
        <div class="form-control-checkbox <?php echo $select ? 'pr-0' : '' ?>">
            <label for="input_permission_<?php echo htmlspecialchars( $slug ) ?>"><?php echo $info[ 'title' ] ?></label>
            <?php if ( ! $select ): ?>
            <div class="fs_onoffswitch">
                <input type="checkbox" class="fs_onoffswitch-checkbox"
                       id="input_permission_<?php echo htmlspecialchars( $slug ) ?>"<?php echo ( isset( $currCapability ) && $currCapability == 'on' ) ? ' checked' : '' ?>>
                <label class="fs_onoffswitch-label"
                       for="input_permission_<?php echo htmlspecialchars( $slug ) ?>"></label>
            </div>
            <?php else: ?>
            <div>
                <select class="form-control" id="input_permission_<?php echo htmlspecialchars( $slug ) ?>">
                    <option <?php echo ( isset( $currCapability ) && $currCapability == 'off' ) ? ' selected' : '' ?>
                            value="off"><?php echo bkntc__( 'None' ); ?></option>
                    <option <?php echo ( isset( $currCapability ) && $currCapability == 'all' ) ? ' selected' : '' ?>
                            value="all"><?php echo bkntc__( 'All' ); ?></option>
                    <?php /*todo://bu yamaq bura atildi. Ne vaxtsa ele bir sistem qurmaq lazimdir ki ne yuxaridaki $select-e ne de buna ehtiyac olmasin.*/ ?>
                    <option <?php echo ( isset( $currCapability ) && $currCapability == 'my' ) ? ' selected' : '' ?>
                            value="my"><?php echo $slug === 'staff' ? bkntc__( 'Me' ) : bkntc__( 'My' ); ?></option>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ( ! empty( $info[ 'children' ] ) ): ?>
        <div class="form-groups-list children-container-<?php echo $slug ?>">
            <?php foreach ( $info[ 'children' ] as $childSlug => $childInf ): ?>
                <?php /*todo://bu da yuxaridaki yamağın davamıdı.*/ ?>
                <div class="form-group pl-4 child-container" id="child_permission_<?php echo $childSlug ?>">
                    <div class="form-control-checkbox">
                        <label for="input_permission_<?php echo htmlspecialchars( $childSlug ) ?>"><?php echo $childInf[ 'title' ] ?></label>
                        <div class="fs_onoffswitch">
                            <input type="checkbox" class="fs_onoffswitch-checkbox"
                                   id="input_permission_<?php echo htmlspecialchars( $childSlug ) ?>"<?php echo ( isset( $parameters[ 'current_capabilities' ][ $childSlug ] ) && $parameters[ 'current_capabilities' ][ $childSlug ] == 'on' ) ? ' checked' : '' ?>>
                            <label class="fs_onoffswitch-label"
                                   for="input_permission_<?php echo htmlspecialchars( $childSlug ) ?>"></label>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    <?php endif;?>
<?php endforeach; ?>