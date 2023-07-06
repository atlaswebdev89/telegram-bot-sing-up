<?php

namespace App\MyCommands;

use App\Core\Handlers\CommandsHandler;

class BackCommand extends CommandsHandler
{

	public function execute()
	{
		// Меняем статус
		$this->machine->setDefault();
		// И если есть обработчик для этого статуса запускаем его
		$this->telegram->handlerRun('/start');
	}
}
