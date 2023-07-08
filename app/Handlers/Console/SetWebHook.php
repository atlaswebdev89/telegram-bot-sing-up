<?php

namespace App\Handlers\Console;

use App\Core\Handlers\ConsoleBasicHandler;

class SetWebHook extends ConsoleBasicHandler
{
	public function execute()
	{

		echo ($this->api->sdk("getMe")->getFirstName()) . PHP_EOL;

		if ($webhook = getenv("TELEGRAM_WEB_HOOK")) {
			$response = $this->api->setWebHook($webhook);
			if ($response) {
				print_r($response);
			}
		} else {
			echo "Not found webhooks";
		}
	}
}
