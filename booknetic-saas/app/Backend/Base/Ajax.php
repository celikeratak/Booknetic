<?php

namespace BookneticSaaS\Backend\Base;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function ping()
	{
		return $this->response( true );
	}

    public function join_beta()
    {
        return ( new \BookneticApp\Backend\Base\Ajax() )->join_beta();
    }
}
