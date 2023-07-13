<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class ListUsersCommand extends CommandsHandler
{
	public $table_users = "telegram_table_states";

	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => $this->getAllUsers(),
		]);
	}

	public function getAllUsers()
	{
		/**
		 * Получаем список всех users
		 */
		$sql = "SELECT `chat_id` FROM  " . $this->table_users . "";
		$result = $this->storage->query($sql, 'arraydata');

		if ($result) {
			$count = 0;
			foreach ($result as $key => $user) {
				if (!in_array($user['chat_id'], $this->di['admins'])) {
					$count++;
				}
			}
			$text = ($count) ? "Количество подключенных пользователей\n" . $count . "\n" : "Нет пользователей\n";
		} else {
			$text = "Нет пользователей\n";
		}
		return $text;
	}
}
