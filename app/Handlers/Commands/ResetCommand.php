<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class ResetCommand extends CommandsHandler
{
	public $table_state = "telegram_table_states";
	public function execute()
	{
		/**
		 * Удаляем бота
		 */
		$this->deleteUsers();

		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "Вы удалили все данные.\nНеобходимо начать с начала\n/start",
			"parse_mode" => "html",
		]);
	}
	public function deleteUsers()
	{
		$sql = "DELETE FROM " . $this->table_state . " WHERE chat_id=:chat_id";
		$data_array = array(
			'chat_id'      => $this->chat_id(),
		);
		$result = $this->storage->query($sql, 'count', $data_array);
	}
}
