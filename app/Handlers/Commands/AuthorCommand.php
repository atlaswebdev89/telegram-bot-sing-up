<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class AuthorCommand extends CommandsHandler
{
	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => "Меня сделал хороший программист\nJunior developer Atlas&89\n"
				. "<a href='https://www.linkedin.com/in/dzmitry-doroshuk/'>LinkedIn</a>\n"
				. "<a href='https://github.com/atlaswebdev89'>Github</a>",
			"parse_mode" => "html",
		]);
	}
}
