<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;

class ContactCommand extends CommandsHandler
{

	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "Мой контакт только для экстренной связи\nПо мелочам не беспокоить\n@Andrik_Bastion",
		]);
	}
}
