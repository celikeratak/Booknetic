<?php

namespace BookneticAddon\WebhookWorkflow;

use BookneticApp\Providers\Helpers\WorkflowHelper;
use BookneticVendor\GuzzleHttp\Client;
use BookneticApp\Models\WorkflowLog;
use BookneticApp\Providers\Common\WorkflowDriver;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticVendor\GuzzleHttp\Exception\GuzzleException;

class WebhookWorkflowDriver extends WorkflowDriver
{
    protected $driver = 'webhook';

    public function __construct ()
    {
        $this->setName( bkntc__( 'Send HTTP(S) request' ) );
        $this->setEditAction( 'webhook_workflow', 'workflow_action_edit_view' );
    }

    public function handle ( $eventData, $actionSettings, $shortCodeService )
    {
        $actionData = json_decode($actionSettings['data'],true);
        if ( empty( $actionData ) )
        {
            return;
        }

        $requestMethod = $actionData[ 'request_method' ];
        $url           = trim( $shortCodeService->replace( $actionData[ 'url' ], $eventData ) );
        $contentType   = $actionData[ 'content_type' ];

        $headers = $actionData[ 'headers' ];

        if ( ! empty( $headers ) )
        {
            foreach ( $headers as $k => $v )
            {
                $headers[ $k ] = trim( $shortCodeService->replace( $v, $eventData ) );
            }
        }

        $body = $actionData[ 'body' ];

        if ( $contentType === 'FORM_DATA' && ! empty( $body ) )
        {
            foreach ( $body as $k => $v )
            {
                $body[ $k ] = trim( $shortCodeService->replace( $v, $eventData ) );
            }
        }
        else if ( $contentType === 'JSON' )
        {
            $body = $shortCodeService->replace( $body, $eventData );
        }

        $this->send( $requestMethod, $url, $headers, $contentType, $body, $actionSettings );
    }

    public function send ( $requestMethod, $url, $headers, $contentType, $body, $actionSettings )
    {
        if ( empty( $requestMethod ) || empty( $url ) )
            return false;

        $logCount = WorkflowHelper::getUsage( $this->getDriver() );

        if ( Capabilities::getLimit( 'webhook_allowed_max_number' ) <= $logCount && Capabilities::getLimit( 'webhook_allowed_max_number' ) > -1 )
        {
            return false;
        }

        $options = [
            'headers' => [
                'user-agent' => 'Webhook action for Booknetic workflows/' . WebhookWorkflowAddon::getVersion(),
            ],
        ];

        if ( ! empty( $headers ) )
        {
            $options[ 'headers' ] = $headers;
        }

        if ( $requestMethod === 'POST' || $requestMethod === 'PUT' )
        {
            if ( $contentType === 'FORM_DATA' )
            {
                $options[ 'form_params' ] = $body;
            }
            else if ( $contentType === 'JSON' )
            {
                $options[ 'json' ] = json_decode( $body, true );
            }
        }

        try
        {
            $options[ 'verify' ]          = false;
            $options[ 'timeout' ]         = 10;
            $options[ 'connect_timeout' ] = 10;

            $client = new Client();

            $client->request( $requestMethod, $url, $options );

            WorkflowLog::insert([
                'workflow_id'   => $actionSettings['workflow_id'],
                'when'          => $actionSettings->when,
                'driver'    =>  $this->getDriver(),
                'date_time' =>  Date::dateTimeSQL(),
                'data'      =>  json_encode([
                    'request_method'        =>$requestMethod,
                    'url'                   =>$url,
                    'headers'               =>$headers,
                    'content_type'          =>$contentType,
                    'body'                  =>$body,
                ]),
            ]);
        }
        catch ( GuzzleException $e )
        {
        }

        return true;
    }
}
