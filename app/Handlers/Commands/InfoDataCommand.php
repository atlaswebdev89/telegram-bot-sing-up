<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;

class InfoDataCommand extends CommandsHandler
{
	public function execute()
	{
		$this->responseInfo();
	}
}
