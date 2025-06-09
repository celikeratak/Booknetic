<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\TelegramWorkflow\TelegramWorkflowAddon;
use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\TelegramWorkflow\bkntc__;

?>


<script src="<?php echo Helper::assets('plugins/summernote/summernote-lite.min.js')?>"></script>
<link rel="stylesheet" href="<?php echo Helper::assets('plugins/summernote/summernote-lite.min.css')?>" type="text/css">
<script src="<?php echo Helper::assets('js/summernote.js')?>"></script>
<link rel="stylesheet" href="<?php echo Helper::assets('css/summernote.css')?>" type="text/css">

<script>
    var workflow_telegram_action_all_shortcodes = <?php echo json_encode($parameters['all_shortcodes']) ?>;

    var workflow_telegram_action_all_shortcodes_obj = {};
    workflow_telegram_action_all_shortcodes.forEach((value,index)=>{
        workflow_telegram_action_all_shortcodes_obj[value.code] = value.name;
    });

</script>
<script type="text/javascript" src="<?php echo TelegramWorkflowAddon::loadAsset('assets/backend/js/workflow_action_edit.js')?>"></script>

<div class="fs-modal-title">
	<div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
	<div class="title-text"><?php echo bkntc__('Edit action')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form id="editWorkflowActionForm">

			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="input_to"><?php echo bkntc__( 'To' ); ?></label>
                    <select id="input_to" class="form-control" multiple="multiple">
                        <?php foreach ( $parameters[ 'last_chats' ] as $chatId => $chat ): ?>
                            <option value="<?php echo htmlspecialchars( $chatId ); ?>" <?php echo $chat[ 'selected' ] ? 'selected' : ''; ?>><?php echo htmlspecialchars( $chat[ 'name' ] ); ?></option>
                        <?php endforeach; ?>
                    </select>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="input_body"><?php echo bkntc__( 'Message' ); ?></label>
					<textarea class="form-control required" id="input_body"><?php echo empty( $parameters[ 'data' ][ 'body' ] ) ? '' : htmlspecialchars($parameters['data']['body']); ?></textarea>
				</div>
			</div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="input_documents"><?php echo bkntc__('Documents')?></label>
                    <select id="input_documents" class="form-control" multiple="multiple">
                        <?php foreach ( $parameters[ 'documents_shortcodes' ] as $key => $shortcode ): ?>
                            <option value="<?php echo htmlspecialchars( $key ); ?>" <?php echo $shortcode['selected'] ? 'selected' : '';?> ><?php echo htmlspecialchars( $shortcode['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

		</form>
	</div>
</div>

<div class="fs-modal-footer">

    <div class="footer_left_action">
        <input type="checkbox" id="input_is_active" <?php echo $parameters['action_info']->is_active ? 'checked' : '' ?>>
        <label for="input_is_active" class="font-size-14 text-secondary"><?php echo bkntc__('Enabled')?></label>
    </div>

	<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="saveAndTestWorkflowActionBtn"><?php echo bkntc__( 'SAVE & TEST' ) ?></button>
    <button type="button" class="btn btn-lg btn-primary" id="saveWorkflowActionBtn"><?php echo bkntc__('SAVE')?></button>
</div>
