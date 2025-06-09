<?php
/**
 * Social Link Modal
 * 
 * This file contains the modal for adding/editing social media links in the Booknetic Help Center.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Modal for Add/Edit Social Media Link -->
<div class="modal" id="socialLinkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo bkntc__('Social Media Link')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="socialLinkForm">
                    <input type="hidden" name="id" id="link_id">
                    <div class="form-group">
                        <label><?php echo bkntc__('Platform')?> *</label>
                        <input type="text" class="form-control" name="platform" id="platform" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo bkntc__('Icon')?></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-icons text-muted preview-icon"></i>
                            </span>
                            <input type="text" class="form-control" name="icon" id="icon" required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#iconPickerModal">
                                <i class="fas fa-search"></i> <?php echo bkntc__('Browse')?>
                            </button>
                        </div>
                        <small class="form-text text-muted"><?php echo bkntc__('Select or enter Font Awesome icon class')?></small>
                    </div>
                    <div class="form-group">
                        <label><?php echo bkntc__('URL')?></label>
                        <input type="url" class="form-control" name="url" id="url" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo bkntc__('Display Order')?></label>
                        <input type="number" class="form-control" name="display_order" id="display_order" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo bkntc__('Close')?></button>
                <button type="button" class="btn btn-primary" id="saveSocialLink"><?php echo bkntc__('Save')?></button>
            </div>
        </div>
    </div>
</div>
