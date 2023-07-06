<?php

namespace App\Core\Handlers;

class CommandsHandler extends BasicHandler
{
	public function iDontGetIt()
	{
		$this->api->sendMessage($this->chat_id(), [
			'text' => 'Не понимаю тебя Commands not found'
		]);
	}
}
