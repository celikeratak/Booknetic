<?php
/**
 * Menu Link Modal
 * 
 * This file contains the modal for adding/editing menu links in the Booknetic Help Center.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Modal for Add/Edit Menu Link -->
<div class="modal" id="menuLinkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuLinkModalTitle"><?php echo bkntc__('Add Menu Link'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="menuLinkForm">
                    <input type="hidden" id="menu_link_id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="menu_label"><?php echo bkntc__('Label'); ?></label>
                        <input type="text" class="form-control" id="menu_label" name="label" required>
                        <small class="form-text text-muted"><?php echo bkntc__('The text that will be displayed in the menu'); ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="menu_url"><?php echo bkntc__('URL'); ?></label>
                        <input type="url" class="form-control" id="menu_url" name="url" required>
                        <small class="form-text text-muted"><?php echo bkntc__('The link destination'); ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="menu_order"><?php echo bkntc__('Order'); ?></label>
                        <input type="number" class="form-control" id="menu_order" name="order" min="1" value="1" required>
                        <small class="form-text text-muted"><?php echo bkntc__('The display order in the menu (lower numbers appear first)'); ?></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo bkntc__('Cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="saveMenuLink"><?php echo bkntc__('Save'); ?></button>
            </div>
        </div>
    </div>
</div>
