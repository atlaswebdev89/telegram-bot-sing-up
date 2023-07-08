<?php

namespace App\Handlers\States;

use App\Core\Handlers\StatesHandler;

class AgeHandler extends StatesHandler
{
	public function execute()
	{

		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "State=" . $this->machine->getCurrentState(),
		]);
		// $this->machine->nextState();
		// $this->machine->backStartTransition();
	}
}
