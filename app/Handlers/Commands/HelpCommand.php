<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class HelpCommand extends CommandsHandler
{
	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "<b>Вот что я умею</b>\n\n"
				. "/start - Начало работы\n"
				. "/reset - Удалить все данные и начать с начала\n"
				. "/help - Помощь\n"
				. "/author - Разработчик бота\n",
			"parse_mode" => "html",
		]);
	}
}
