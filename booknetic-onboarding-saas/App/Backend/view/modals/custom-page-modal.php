<?php
/**
 * Custom Page Modal
 * 
 * This file contains the modal for adding/editing custom pages in the Booknetic Help Center.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Live Chat Section -->
<div class="livechat-section mb-4" id="livechat-section">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-pen text-primary"></i>
            <h5 class="card-title"><?php echo bkntc__('Edit Custom Page')?></h5>
        </div>
        <div class="card-body">
            <div id="livechat-error-message" class="alert alert-danger" style="display: none;"></div>
            <div id="livechat-success-message" class="alert alert-success" style="display: none;"></div>
            
            <form id="livechatPageForm">
                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Icon')?></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i id="livechat-icon-preview" class="<?php echo esc_attr(get_help_setting('livechat_icon', 'fas fa-comments')); ?> text-primary"></i>
                        </span>
                        <input type="text" class="form-control" name="icon" id="icon" value="<?php echo esc_attr(get_help_setting('livechat_icon', 'fas fa-comments')); ?>" required>
                        <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#customPageIconPickerModal">
                            <i class="fas fa-search"></i> <?php echo bkntc__('Browse')?>
                        </button>
                    </div>
                    <small class="form-text text-muted"><?php echo bkntc__('Enter Font Awesome icon class (e.g., fas fa-comments)')?></small>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Title')?></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-heading text-muted"></i>
                        </span>
                        <input type="text" class="form-control" name="livechat_title" id="livechat_title" value="<?php echo esc_attr(get_help_setting('livechat_title', '')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Subtitle')?></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-align-left text-muted"></i>
                        </span>
                        <input type="text" class="form-control" name="livechat_subtitle" id="livechat_subtitle" value="<?php echo esc_attr(get_help_setting('livechat_subtitle', '')); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo bkntc__('Embed Code')?> (<?php echo bkntc__('Add your HTML code here')?>)</label>
                    <textarea class="form-control" name="livechat_embed_code" id="livechat_embed_code" rows="8" style="font-family: monospace;"><?php echo esc_textarea(get_help_setting('livechat_embed_code', '')); ?></textarea>
                </div>

                <button type="button" id="saveLivechatPage" class="btn btn-primary">
                    <i class="fas fa-save mr-2" style="color: white;"></i><?php echo bkntc__('Save Changes')?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Custom Page Icon Picker Modal -->
<div class="modal fade" id="customPageIconPickerModal" tabindex="-1" aria-labelledby="customPageIconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customPageIconPickerModalLabel"><?php echo bkntc__('Select Icon')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="customPageIconSearch" placeholder="<?php echo bkntc__('Search icons...')?>">
                </div>
                <div class="row row-cols-2 row-cols-md-4 g-3" id="customPageIconGrid">
                    <!-- Icons will be populated here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the custom page icon picker JavaScript -->
<script src="<?php echo BookneticAddon\ContactUsP\ContactUsPAddon::loadAsset('assets/backend/js/custom-page-icon-picker.js'); ?>"></script>
