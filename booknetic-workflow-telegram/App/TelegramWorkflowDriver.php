<?php

namespace BookneticAddon\TelegramWorkflow;

use BookneticApp\Models\WorkflowLog;
use BookneticApp\Providers\Common\WorkflowDriver;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Curl;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\WorkflowHelper;
use BookneticSaaS\Config as SaasConfig;

class TelegramWorkflowDriver extends WorkflowDriver
{
    protected $driver = 'telegram';

    private string $botToken;

    public function __construct()
    {
        $this->setName( bkntc__( 'Send Telegram message' ) );
        $this->setEditAction( 'telegram_bot_workflow', 'workflow_action_edit_view' );
    }

    public function handle( $eventData, $actionSettings, $shortCodeService )
    {
        $this->initBotToken( $actionSettings[ 'when' ] );

        if ( empty( $this->botToken ) ) {
            return;
        }

        $actionData = json_decode( $actionSettings[ 'data' ], true );

        if ( empty( $actionData ) ) {
            return;
        }

        $sendTo = $shortCodeService->replace( $actionData[ 'to' ], $eventData );
        $body = $shortCodeService->replace( $actionData[ 'body' ], $eventData );
        $documents = isset( $actionData[ 'documents' ] ) ? explode( ",", $shortCodeService->replace( $actionData[ 'documents' ], $eventData ) ) : [];

        if ( ! empty( $sendTo ) ) {
            $sendToArr = explode( ',', $sendTo );

            foreach ( $sendToArr as $sendTo ) {
                $body = htmlspecialchars_decode( $body );
                $body = str_replace( [ '<p>', '</p>', '&nbsp;' ], [ '', '<br>', ' ' ], $body );
                $body = preg_replace( '/<br\s?\/?>/i', "\n", $body );
                $body = strip_tags( $body, '<b><u><i><a>' );

                $this->send( trim( $sendTo ), $body, $documents, $actionSettings );
            }
        }
    }

    /**
     * This function is a workaround for the case when a saas event is fired from a tenant's side.
     * See tenant_deleted for further details.
     */
    private function initBotToken( $event )
    {
        if ( Helper::isSaaSVersion() && in_array( $event, SaasConfig::getWorkflowEventsManager()->getList() ) )
        {
            $this->botToken = Helper::getOption( 'telegram_bot_token', '', false );
            return;
        }

        $this->botToken = Helper::getOption( 'telegram_bot_token', '' );
    }

    public function send( $sendTo, $body, $documents, $actionSettings )
    {
        if ( empty( $sendTo ) ) {
            return false;
        }

        $logCount = WorkflowHelper::getUsage( $this->getDriver() );
        if ( Capabilities::getLimit( 'telegram_bot_allowed_max_messages' ) <= $logCount && Capabilities::getLimit( 'telegram_bot_allowed_max_messages' ) > -1 ) {
            return false;
        }

        $data = [
            'parse_mode' => 'HTML',
            'chat_id' => $sendTo,
            'text' => $body,
        ];

        if ( mb_strlen( $body ) >= 1024 * 10 ) {
            $body = mb_substr( $body, 0, 1024 * 10 );
        }

        for ( $i = 0; $i < ceil( mb_strlen( $body ) / 1024 ); $i++ ) {
            $data[ 'text' ] = mb_substr( $body, $i * 1024, 1024 );

            Curl::getURL( 'https://api.telegram.org/bot' . $this->botToken . '/sendMessage?' . http_build_query( $data ) );
        }

        foreach ( $documents as $document ) {
            if ( empty( $document ) || ! file_exists( $document ) || ! is_readable( $document ) )
                continue;

            $mime_type = mime_content_type( $document );
            if ( ! in_array( $mime_type, [ 'image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'application/zip', 'application/vnd.rar', 'video/mp4', 'audio/mpeg', 'audio/wav', 'application/rtf', 'application/vnd.ms-excel', 'application/msword', 'text/csv' ] ) )
                continue;

            $ch = curl_init( 'https://api.telegram.org/bot' . $this->botToken . '/sendDocument' );
            $data = [
                'chat_id' => $sendTo,
                'document' => new \CURLFile( $document, $mime_type, basename( $document ) )
            ];
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_exec( $ch );
        }

        WorkflowLog::insert( [
            'workflow_id' => $actionSettings[ 'workflow_id' ],
            'when' => $actionSettings->when,
            'driver' => $this->getDriver(),
            'date_time' => Date::dateTimeSQL(),
            'data' => json_encode( [
                'to' => $sendTo,
                'body' => $body,
                'documents' => $documents
            ] ),
        ] );

        return true;
    }
}
