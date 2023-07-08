<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;

class ContactCommand extends CommandsHandler
{

	public  $table = 'telegram_table_states';

	public function getKeyboards()
	{
		return json_encode([
			'keyboard' => [
				[
					[
						'text' => 'Наш сайт',
					],
					[
						'text' => 'Номера телефонов',
					],
					[
						'text' => 'e-mail',
					],
					[
						'text' => 'Назад',
					],
				]
			],
			'one_time_keyboard' => FALSE,
			'resize_keyboard' => TRUE,
		]);
	}

	public function execute()
	{
		$response = $this->api->sendTextWithButton($this->chat_id(), [
			'text' => "Contacts",
			'button' => $this->getKeyboards(),
		]);
		if ($response->ok) {
			$result = $this->machine->setState('users.name');
		}
	}
}
