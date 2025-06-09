<?php

namespace BookneticAddon\Customerpanel;


use BookneticApp\Providers\Helpers\Helper;

class Listener
{

    public static function replaceShortCode( string $text, $data )
    {
        $shortCodeReplacers = [
            'customer_panel_url' => CustomerPanelHelper::customerPanelURL(),
            'customer_panel_restriction_time' => Helper::getOption( 'time_restriction_to_make_changes_on_appointments', '5' )
        ];

        foreach ( $shortCodeReplacers as $shortcode => $replacer )
        {
            $text = str_replace( '{' . $shortcode . '}', $replacer, $text );
        }

        return $text;
    }

    public static function saasSharePageFooter()
    {
        if( Helper::getOption('customer_panel_enable', 'off', false) == 'on' ):?>
		<div class="form-row">
			<div class="form-group col-md-12">
				<label for="input_customer_cabinet_url"><?php echo bkntc__('Customer panel URL')?>:</label>
				<input type="text" id="input_customer_cabinet_url" readonly class="form-control" value="<?php echo CustomerPanelHelper::customerPanelURL()?>">
			</div>
		</div>
		<?php endif;
    }

    public static function initGutenbergBlocks()
    {
        if( !function_exists('register_block_type') )
            return;

        wp_register_script(
            'booknetic-cp-blocks',
            plugins_url( 'assets/backend/js/gutenberg-block.js', dirname(__DIR__) . '/init.php' ),
            [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ]
        );

        register_block_type( 'booknetic/customerpanel' , ['editor_script' => 'booknetic-cp-blocks'] );
    }

}