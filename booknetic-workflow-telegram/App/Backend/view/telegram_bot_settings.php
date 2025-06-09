<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\TelegramWorkflow\TelegramWorkflowAddon;
use function BookneticAddon\TelegramWorkflow\bkntc__;

?>
<div id="booknetic_settings_area">
	<script type="application/javascript" src="<?php echo TelegramWorkflowAddon::loadAsset('assets/backend/js/settings.js')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Telegram Bot settings')?>
		</div>
		<div class="ms-content">

			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="input_telegram_bot_token"><?php echo bkntc__('Telegram Bot token:'); ?></label>
					<input class="form-control" id="input_telegram_bot_token" value="<?php echo htmlspecialchars( Helper::getOption( 'telegram_bot_token', '' ) ); ?>">
				</div>
			</div>

		</div>
	</div>
</div>