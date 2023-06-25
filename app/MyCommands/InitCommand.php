<?php

namespace App\MyCommands;

use App\Core\Handlers\CommandsHandler;

class InitCommand extends CommandsHandler
{

	public function execute()
	{
		echo "Hello   " . __CLASS__ . ' ' . __METHOD__ . PHP_EOL;
		$result = $this->machine->setState('users.name');
		echo $this->machine->getCurrentState() . PHP_EOL;
		$this->logger->error('debug test');
	}
}
