<?php

namespace App\MyCommands;

use App\Core\Handlers\CommandsHandler;

class StartCommand extends CommandsHandler
{
	protected string $name = 'start';
	protected string $description = 'Start Command to get you started';

	public function execute()
	{
		echo "Hello   " . __CLASS__ . ' ' . __METHOD__ . PHP_EOL;

		$result = $this->machine->setDefault();

		echo $this->machine->getCurrentState();
	}
}
