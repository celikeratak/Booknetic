<?php

namespace BookneticAddon\WebhookWorkflow\Backend;

use BookneticApp\Providers\DB\Collection;
use BookneticAddon\WebhookWorkflow\WebhookWorkflowDriver;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Common\ShortCodeService;
use BookneticApp\Providers\Core\Controller;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\WebhookWorkflow\bkntc__;

class Ajax extends Controller
{

    /**
     * @var WorkflowEventsManager
     */
    private $workflowEventsManager;

    public function __construct ( $workflowEventsManager )
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

        $data = json_decode( $workflowActionInfo->data, true );

        $availableParams = $this->workflowEventsManager->get( Workflow::get( $workflowActionInfo->workflow_id )[ 'when' ] )
                                                       ->getAvailableParams();

        $allShortcodes = $this->workflowEventsManager->getShortcodeService()->getShortCodesList( $availableParams );

        if ( empty( $data[ 'headers' ] ) )
        {
            $data[ 'headers' ] = [];
        }

        if ( empty( $data[ 'body' ] ) )
        {
            $data[ 'body' ] = [];
        }

        return $this->modalView( __DIR__ . '/view/workflow_action_edit.php', [
            'action_info'    => $workflowActionInfo,
            'data'           => $data,
            'all_shortcodes' => $allShortcodes,
        ], [ 'workflow_action_id' => $id ] );
    }

    public function workflow_action_save_data ()
    {
        $id            = Helper::_post( 'id', 0, 'int' );
        $isActive      = Helper::_post( 'is_active', 1, 'int' );
        $requestMethod = Helper::_post( 'request_method', '', 'string', [ 'GET', 'POST', 'PUT', 'DELETE' ] );
        $url           = Helper::_post( 'url', '', 'string' );
        $headers       = Helper::_post( 'headers', [], 'array' );
        $contentType   = Helper::_post( 'content_type', '', 'string', [ 'FORM_DATA', 'JSON' ] );

        if ( ! WorkflowAction::get( $id ) )
        {
            return $this->response( false );
        }

        $url = trim( strip_tags( htmlspecialchars_decode( $url ) ) );

        if ( empty( $requestMethod ) || empty( $url ) )
        {
            return $this->response( false, bkntc__( 'Webhook requires request method and url!' ) );
        }

        $sqlHeaders = [];

        foreach ( $headers as $k => $v )
        {
            $k = trim( $k );
            $v = trim( $v );

            if ( ! empty( $k ) && ! empty( $v ) )
            {
                $sqlHeaders[ $k ] = $v;
            }
        }

        $body = '';

        if ( $requestMethod === 'POST' || $requestMethod === 'PUT' )
        {
            if ( $contentType === 'FORM_DATA' )
            {
                $formData = Helper::_post( 'body', [], 'array' );

                $body = [];

                foreach ( $formData as $k => $v )
                {
                    $k = trim( $k );
                    $v = trim( $v );

                    if ( ! empty( $k ) && ! empty( $v ) )
                    {
                        $body[ $k ] = $v;
                    }
                }
            }
            else if ( $contentType === 'JSON' )
            {
                $body = Helper::_post( 'body', '', 'string' );
            }
        }

        $newData = [
            'request_method' => $requestMethod,
            'url'            => $url,
            'headers'        => $sqlHeaders,
            'content_type'   => $contentType,
            'body'           => $body,
        ];

        WorkflowAction::where( 'id', $id )->update( [ 'data' => json_encode( $newData ), 'is_active' => $isActive ] );

        return $this->response( true );
    }

	public function workflow_action_send_test_data ()
	{
		$url = Helper::_post('url', '', 'string');
		$actionId = Helper::_post('id', 0, 'int');

		if( !empty( $url ) && $actionId > 0 )
		{
			$actionInf = WorkflowAction::get( $actionId );
			$settings = json_decode( $actionInf->data, true );
			$settings['url'] = $url;
            $actionInf->data = json_encode($settings);
            $actionInf->when = 'send_test';
			$driver = new WebhookWorkflowDriver();
			$driver->handle(new Collection(), $actionInf, new ShortCodeService());
		}

		return $this->response( true );
	}


}
