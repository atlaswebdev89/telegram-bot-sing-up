<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;

class WorkModeCommand extends CommandsHandler
{
	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "Пн.-Пт.\nC 8:00 до 20:00",
		]);
	}
}
