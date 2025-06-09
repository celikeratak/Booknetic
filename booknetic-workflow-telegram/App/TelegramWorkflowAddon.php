<?php

namespace BookneticAddon\TelegramWorkflow;

use BookneticAddon\TelegramWorkflow\Backend\Ajax;
use BookneticApp\Config;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\WorkflowHelper;
use BookneticApp\Providers\UI\SettingsMenuUI;
use BookneticSaaS\Providers\Core\Route as SaaSRoute;

function bkntc__( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, TelegramWorkflowAddon::getAddonTextDomain() );
}

class TelegramWorkflowAddon extends AddonLoader
{

    public function init()
    {
        Capabilities::registerTenantCapability( 'telegram_bot', bkntc__( 'Telegram Bot integration' ), 'workflow' );

        if ( ! Capabilities::tenantCan( 'telegram_bot' ) ) {
            return;
        }

        Capabilities::register( 'telegram_bot_settings', bkntc__( 'Telegram Bot settings' ), 'settings' );
        Capabilities::registerLimit( 'telegram_bot_allowed_max_messages', bkntc__( 'Allowed maximum Telegram messages' ) );

        Config::getWorkflowDriversManager()->register( new TelegramWorkflowDriver() );
    }

    public function initBackend()
    {
        if ( ! Capabilities::tenantCan( 'telegram_bot' ) ) {
            return;
        }

        $ajaxController = new Ajax( Config::getWorkflowEventsManager() );

        Route::post( 'telegram_bot_workflow', $ajaxController, [ 'workflow_action_edit_view', 'workflow_action_save_data', 'workflow_action_send_test_data' ] );

        if ( Capabilities::userCan( 'telegram_bot_settings' ) ) {
            Route::post( 'telegram_bot', $ajaxController, [ 'settings_view', 'save_settings' ] );

            SettingsMenuUI::get( 'integrations' )
                ->subItem( 'settings_view', 'telegram_bot' )
                ->setTitle( bkntc__( 'Telegram Bot' ) )
                ->setPriority( 15 );
        }

        add_filter( 'bkntc_tenant_limits', function ( $limitArr, $currentPlanInf ) {
            $limits = json_decode( $currentPlanInf->permissions, true )[ 'limits' ];

            if ( ! array_key_exists( 'telegram_bot_allowed_max_messages', $limits ) ) return $limitArr;

            $count = WorkflowHelper::getUsage( ( new TelegramWorkflowDriver() )->getDriver() );
            $limitArr[ 'telegram' ] = [ 'title' => bkntc__( 'Telegram' ), 'current_usage' => $count, 'max_usage' => $limits[ 'telegram_bot_allowed_max_messages' ] ];

            return $limitArr;
        }, 10, 2 );
    }

    public function initSaaS()
    {
        \BookneticSaaS\Config::getWorkflowDriversManager()->register( new TelegramWorkflowDriver() );
    }

    public function initSaaSBackend()
    {
        $ajaxController = new Ajax( \BookneticSaaS\Config::getWorkflowEventsManager() );
        SaaSRoute::post( 'telegram_bot_workflow', $ajaxController, [ 'workflow_action_edit_view', 'workflow_action_save_data', 'workflow_action_send_test_data' ] );
        SaaSRoute::post( 'telegram_bot', $ajaxController, [ 'settings_view', 'save_settings' ] );

        \BookneticSaaS\Providers\UI\SettingsMenuUI::get( 'integrations' )
            ->subItem( 'settings_view', 'telegram_bot' )
            ->setTitle( bkntc__( 'Telegram Bot' ) )
            ->setPriority( 15 );
    }
}