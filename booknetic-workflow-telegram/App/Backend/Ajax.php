<?php

namespace BookneticAddon\TelegramWorkflow\Backend;

use BookneticAddon\TelegramWorkflow\TelegramWorkflowDriver;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Common\ShortCodeService;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Providers\Helpers\Curl;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Controller;
use BookneticApp\Providers\Core\Capabilities;

class Ajax extends Controller
{
    /**
     * @var WorkflowEventsManager
     */
    private $workflowEventsManager;

    public function __construct($workflowEventsManager)
    {
        $this->workflowEventsManager = $workflowEventsManager;
    }

    public function workflow_action_edit_view ()
    {
        $id = Helper::_post( 'id', 0, 'int' );

        $workflowActionInfo = WorkflowAction::get( $id );

        if ( ! $workflowActionInfo )
        {
            return $this->response( false );
        }

        $availableParams = $this->workflowEventsManager->get(Workflow::get($workflowActionInfo->workflow_id)['when'])
            ->getAvailableParams();

        $data           = json_decode( $workflowActionInfo->data ?? '[]', true );
        $lastChatIDs    = self::getLastChatIDs();
        $toValues       = isset($data['to']) ? explode(',',   $data['to']) : [];
        $chats = [];

        $bodyShortcodes = $this->workflowEventsManager->getShortcodeService()->getShortCodesList($availableParams);

        foreach ( $toValues as $chatId )
        {
            if(empty($chatId)) continue;

            $chats[ $chatId ] = [
                'selected' => true
            ];

            if ( array_key_exists( $chatId, $lastChatIDs ) )
            {
                $chats[ $chatId ][ 'name' ] = $lastChatIDs[ $chatId ];
            }
            else
            {
                $chats[ $chatId ][ 'name' ] = $chatId;
            }
        }

        foreach ( $lastChatIDs as $chatId => $name )
        {
            if ( ! in_array( $chatId, $toValues ) )
            {
                $chats[ $chatId ] = [
                    'name'      => $name,
                    'selected'  => false
                ];
            }
        }

        $selectedDocuments = isset($data['documents']) ? explode(',',   $data['documents']) : [];
        $documents = [];
        $fileShortcodes = $this->workflowEventsManager->getShortcodeService()->getShortCodesList($availableParams, ['file']);
        foreach ($fileShortcodes as $shortcode)
        {
            $documents[ '{'. $shortcode['code'] . '}' ] = [
                'name'      => $shortcode['name'],
                'selected'  => false
            ];
        }
        foreach ($selectedDocuments as $document)
        {
            if (empty($document)) continue;

            if (array_key_exists($document, $documents))
            {
                $documents[$document]['selected'] = true;
            } else {
                $documents[$document] = [
                    'selected' => true,
                    'name' => $document
                ];
            }
        }


        return $this->modalView( __DIR__ . '/view/workflow_action_edit.php', [
            'action_info'           => $workflowActionInfo,
            'data'                  => $data,
            'last_chats'            => $chats,
            'all_shortcodes'        => $bodyShortcodes,
            'documents_shortcodes'  => $documents
        ], [
            'workflow_action_id' => $id,
        ] );
    }

    public function workflow_action_save_data ()
    {
        $id         = Helper::_post( 'id', 0, 'int' );
        $to         = Helper::_post( 'to', '', 'string' );
        $body       = Helper::_post( 'body', '', 'string' );
        $documents  = Helper::_post( 'documents', '', 'string' );
        $is_active  = Helper::_post( 'is_active', 1, 'num' );

        $checkWorkflowActionExist = WorkflowAction::get( $id );

        if ( ! $checkWorkflowActionExist )
        {
            return $this->response( false );
        }

        $newData = [
            'to'        => $to,
            'body'      => $body,
            'documents' => $documents
        ];

        WorkflowAction::where( 'id', $id )->update( [
            'data' => json_encode( $newData ),
            'is_active' => $is_active
        ] );

        return $this->response( true );
    }

    public function workflow_action_send_test_data ()
    {
        $to = Helper::_post('to', '', 'string');
        $actionId = Helper::_post('id', 0, 'int');

        if( !empty( $to ) && $actionId > 0 )
        {
            $actionInf = WorkflowAction::get( $actionId );
            $settings = json_decode( $actionInf->data, true );
            $settings['to'] = $to;
            $actionInf->data = json_encode($settings);
            $actionInf->when = 'send_test';
            $driver = new TelegramWorkflowDriver();
            $driver->handle(new Collection(), $actionInf, new ShortCodeService());
        }

        return $this->response( true );
    }

    public function settings_view ()
    {
        Capabilities::must( 'telegram_bot_settings' );

        return $this->modalView( __DIR__ . '/view/telegram_bot_settings.php', [] );
    }

    public function save_settings ()
    {
        Capabilities::must( 'telegram_bot_settings' );

        $telegram_bot_token = Helper::_post( 'telegram_bot_token', '', 'string' );

        Helper::setOption( 'telegram_bot_token', $telegram_bot_token );

        return $this->response( true );
    }

    private static function getLastChatIDs ()
    {
        $telegramBotToken = Helper::getOption( 'telegram_bot_token', '' );

        if ( empty( $telegramBotToken ) )
        {
            return [];
        }

        $activeChats = Curl::getURL( 'https://api.telegram.org/bot' . $telegramBotToken . '/getUpdates?allowed_updates=message' );

        try
        {
            $activeChats = json_decode( $activeChats );

            if ( ! empty( $activeChats->error_code ) )
            {
                throw new \Exception();
            }
        }
        catch ( \Exception $e )
        {
            return [];
        }

        $lastChatIDs = [];

        foreach ( $activeChats->result as $chat )
        {
            if ( ! isset( $chat->message ) )
            {
                continue;
            }

            $chatId = isset( $chat->message->chat->id ) ? $chat->message->chat->id : '';

            if ( isset( $lastChatIDs[ $chatId ] ) )
            {
                continue;
            }

            if ( isset( $chat->message->chat->first_name ) )
            {
                $name = $chat->message->chat->first_name;
            }
            else if ( isset( $chat->message->chat->title ) )
            {
                $name = $chat->message->chat->title;
            }
            else
            {
                $name = '[unnamed]';
            }

            $lastChatIDs[ $chatId ] = $name;
        }

        return $lastChatIDs;
    }
}
