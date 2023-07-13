<?php

namespace App\Core\Handlers;

class StatesHandler extends BasicHandler
{
	public function iDontGetIt()
	{
		$this->api->sendMessage($this->chat_id(), [
			'text' => 'Не понимаю тебя'
		]);
	}
}
