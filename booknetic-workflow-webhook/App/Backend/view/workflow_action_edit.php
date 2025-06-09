<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\WebhookWorkflow\WebhookWorkflowAddon;
use function BookneticAddon\WebhookWorkflow\bkntc__;

?>

<script>
    let workflow_email_action_all_shortcodes = <?php echo json_encode( $parameters[ 'all_shortcodes' ] ) ?>;

    let workflow_email_action_all_shortcodes_obj = {};

    workflow_email_action_all_shortcodes.forEach( ( value, index ) =>
    {
        workflow_email_action_all_shortcodes_obj[ value.code ] = value.name;
    } );
</script>

<link rel="stylesheet" href="<?php echo WebhookWorkflowAddon::loadAsset( 'assets/css/webhook.css' ) ?>" type="text/css">
<script type="text/javascript" src="<?php echo WebhookWorkflowAddon::loadAsset( 'assets/js/workflow_action_edit.js' ) ?>"></script>


<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo bkntc__( 'Edit action' ) ?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form id="editWorkflowActionForm">

	        <div class="form-row">
		        <div class="form-group col-md-12">
			        <label for="input_url"><?php echo bkntc__( 'URL' ) ?></label>
			        <div class="url_method_group">
				        <div>
					        <select id="input_request_method" class="form-control">
						        <option value="GET" <?php echo empty( $parameters[ 'data' ][ 'request_method' ] ) || $parameters[ 'data' ][ 'request_method' ] === 'GET' ? 'selected' : ''; ?>>GET</option>
						        <option value="POST" <?php echo ! empty( $parameters[ 'data' ][ 'request_method' ] ) && $parameters[ 'data' ][ 'request_method' ] === 'POST' ? 'selected' : ''; ?>>POST</option>
						        <option value="PUT" <?php echo ! empty( $parameters[ 'data' ][ 'request_method' ] ) && $parameters[ 'data' ][ 'request_method' ] === 'PUT' ? 'selected' : ''; ?>>PUT</option>
						        <option value="DELETE" <?php echo ! empty( $parameters[ 'data' ][ 'request_method' ] ) && $parameters[ 'data' ][ 'request_method' ] === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
					        </select>
				        </div>
				        <div>
					        <input type="text" class="form-control required" id="input_url" placeholder="https://" value="<?php echo empty( $parameters[ 'data' ][ 'url' ] ) ? '' : htmlspecialchars( $parameters[ 'data' ][ 'url' ] ); ?>">
				        </div>
			        </div>
		        </div>
	        </div>

            <div class="form-row form-group">
                <label class="col-12" for="input_headers"><?php echo bkntc__( 'Headers' ); ?></label>
                <div class="col-12 d-flex mb-1 webhook-headers" style="display: none !important;" id="headersClone">
                    <input type="text" class="form-control mr-1" placeholder="Content-type">
                    <input type="text" class="form-control mr-1" placeholder="application/text">
                    <button type="button" class="btn btn-outline-danger btn-lg remove-row border-0">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>

                <?php if ( ! empty( $parameters[ 'data' ][ 'headers' ] ) ): ?>
                    <?php foreach ( $parameters[ 'data' ][ 'headers' ] as $k => $v ): ?>
                        <div class="col-12 d-flex mb-1 webhook-headers">
                            <input type="text" class="form-control mr-1" placeholder="Content-type" value="<?php echo htmlspecialchars( $k ); ?>">
                            <input type="text" class="form-control mr-1" placeholder="application/text" value="<?php echo htmlspecialchars( $v ); ?>">
                            <button type="button" class="btn btn-outline-danger btn-lg remove-row border-0">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="col-12">
                    <button type="button" id="addHeaders" class="btn btn-primary btn-sm"><?php echo bkntc__( 'Add new header' ); ?></button>
                </div>
            </div>

            <div id="bodyContainer" class="<?php echo empty( $parameters[ 'data' ][ 'request_method' ] ) || ( $parameters[ 'data' ][ 'request_method' ] !== 'POST' && $parameters[ 'data' ][ 'request_method' ] !== 'PUT' ) ? 'd-none' : ''; ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="input_content_type"><?php echo bkntc__( 'Content type' ) ?></label>

                        <select id="input_content_type" class="form-control">
                            <option value="NONE" <?php echo ! empty( $parameters[ 'data' ][ 'content_type' ] ) && $parameters[ 'data' ][ 'content_type' ] === 'NONE' ? 'selected' : ''; ?>><?php echo bkntc__( 'none' ) ?></option>
                            <option value="FORM_DATA" <?php echo empty( $parameters[ 'data' ][ 'content_type' ] ) || $parameters[ 'data' ][ 'content_type' ] === 'FORM_DATA' ? 'selected' : ''; ?>><?php echo bkntc__( 'Form data' ) ?></option>
                            <option value="JSON" <?php echo ! empty( $parameters[ 'data' ][ 'content_type' ] ) && $parameters[ 'data' ][ 'content_type' ] === 'JSON' ? 'selected' : ''; ?>><?php echo bkntc__( 'JSON' ) ?></option>
                        </select>
                    </div>
                </div>

                <div id="dataContainer" class="form-row form-group <?php echo empty( $parameters[ 'data' ][ 'content_type' ] ) || $parameters[ 'data' ][ 'content_type' ] === 'FORM_DATA' ? '' : 'd-none'; ?>">
                    <label class="col-12" for="input_headers"><?php echo bkntc__( 'Form data' ) ?></label>
                    <div class="col-12 d-flex mb-1 webhook-body" style="display: none !important;" id="bodyClone">
                        <input type="text" class="form-control mr-1 rtl-mr-1">
                        <input type="text" class="form-control mr-1 rtl-mr-1">
                        <button type="button" class="btn btn-outline-danger btn-lg remove-row border-0">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>

                    <?php if ( ! empty( $parameters[ 'data' ][ 'content_type' ] ) && $parameters[ 'data' ][ 'content_type' ] === 'FORM_DATA' ): ?>
                        <?php foreach ( $parameters[ 'data' ][ 'body' ] as $k => $v ): ?>
                            <div class="col-12 d-flex mb-1 webhook-body">
                                <input type="text" class="form-control mr-1 rtl-mr-1" placeholder="appointment_id" value="<?php echo addslashes( $k ); ?>">
                                <input type="text" class="form-control mr-1 rtl-mr-1" placeholder="{appointment_id}" value="<?php echo addslashes( $v ); ?>">
                                <button type="button" class="btn btn-outline-danger btn-lg remove-row border-0">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="col-12">
                        <button type="button" id="addBody" class="btn btn-primary btn-sm"><?php echo bkntc__( 'Add new field' ); ?></button>
                    </div>
                </div>

                <div id="jsonContainer" class="form-row <?php echo ! empty( $parameters[ 'data' ][ 'content_type' ] ) && $parameters[ 'data' ][ 'content_type' ] === 'JSON' ? '' : 'd-none'; ?>">
                    <div class="form-group col-md-12">
                        <label for="jsonBody"><?php echo bkntc__( 'JSON' ) ?></label>
                        <textarea class="form-control" placeholder='{"appointment_id": 1}' id="jsonBody"><?php echo ! empty( $parameters[ 'data' ][ 'content_type' ] ) && $parameters[ 'data' ][ 'content_type' ] === 'JSON' && ! empty( $parameters[ 'data' ][ 'body' ] ) ? $parameters[ 'data' ][ 'body' ] : ''; ?></textarea>
                    </div>
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

    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__( 'CANCEL' ) ?></button>
    <button type="button" class="btn btn-lg btn-primary" id="saveAndTestWorkflowActionBtn"><?php echo bkntc__( 'SAVE & TEST' ) ?></button>
    <button type="button" class="btn btn-lg btn-primary" id="saveWorkflowActionBtn"><?php echo bkntc__( 'SAVE' ) ?></button>
</div>
