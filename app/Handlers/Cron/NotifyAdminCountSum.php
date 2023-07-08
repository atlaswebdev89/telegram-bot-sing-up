<?php

namespace App\Handlers\Cron;

use App\Core\Handlers\ConsoleBasicHandler;

class NotifyAdminCountSum extends ConsoleBasicHandler
{

	public $chat_id = "496315328";
	public function execute()
	{
		echo "HELLO I am Cron handlers" . PHP_EOL;
		$response = $this->api->sendMessage($this->chat_id, [
			'text' => "Cron work good Hellp",
		]);
	}
}
