<?php

namespace BookneticAddon\WebhookWorkflow;

use BookneticApp\Config;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\WorkflowHelper;
use BookneticSaaS\Providers\Core\Route as SaasRoute;
use BookneticAddon\WebhookWorkflow\Backend\Ajax;

function bkntc__ ( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, WebhookWorkflowAddon::getAddonTextDomain() );
}

class WebhookWorkflowAddon extends AddonLoader
{

    public function init ()
    {
        Capabilities::registerTenantCapability( 'webhook_workflow', bkntc__( 'Webhook workflow action' ), 'workflow' );

        if ( ! Capabilities::tenantCan( 'webhook_workflow' ) )
            return;

        Capabilities::registerLimit( 'webhook_allowed_max_number', bkntc__( 'Allowed maximum webhook trigger' ) );

        Config::getWorkflowDriversManager()->register( new WebhookWorkflowDriver() );
    }

    public function initBackend ()
    {
        if ( ! Capabilities::tenantCan( 'webhook_workflow' ) )
            return;

        $ajaxController = new Ajax( Config::getWorkflowEventsManager() );

        Route::post( 'webhook_workflow', $ajaxController, [ 'workflow_action_edit_view', 'workflow_action_save_data', 'workflow_action_send_test_data' ] );

        add_filter('bkntc_tenant_limits' , function ($limitsArr ,$currentPlanInf){
            $limits = json_decode($currentPlanInf->permissions,true)['limits'];

            if( ! array_key_exists('webhook_allowed_max_number' , $limits )) return $limitsArr;

            $count = WorkflowHelper::getUsage( (new WebhookWorkflowDriver())->getDriver() );
            $limitsArr['webhook'] = ['title'=>bkntc__('Webhook') , 'current_usage'=>$count , 'max_usage'=>$limits['webhook_allowed_max_number'] ];

            return $limitsArr;
        },10,2);
    }

    public function initSaaS ()
    {
        \BookneticSaaS\Config::getWorkflowDriversManager()->register( new WebhookWorkflowDriver() );
    }

    public function initSaaSBackend ()
    {
        SaaSRoute::post( 'webhook_workflow', new Ajax( \BookneticSaaS\Config::getWorkflowEventsManager() ), [ 'workflow_action_edit_view', 'workflow_action_save_data', 'workflow_action_send_test_data' ] );
    }
}