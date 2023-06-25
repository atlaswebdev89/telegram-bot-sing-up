<?php

namespace App\MyHandlerState;

use App\Core\Handlers\StatesHandler;

class WorkHandler extends StatesHandler
{
	public function execute()
	{
		echo "One handler  " . __METHOD__ . ' ' . __CLASS__ . PHP_EOL;
		echo $this->machine->getCurrentState();
		$this->machine->nextState();
	}
}
