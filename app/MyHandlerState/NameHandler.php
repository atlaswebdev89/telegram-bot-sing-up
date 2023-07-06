<?php

namespace App\MyHandlerState;

use App\Core\Handlers\StatesHandler;

class NameHandler extends StatesHandler
{
	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "State=" . $this->machine->getCurrentState(),
		]);
		// $this->machine->nextState();
	}
}
