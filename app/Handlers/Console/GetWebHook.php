<?php

namespace App\Handlers\Console;

use App\Core\Handlers\ConsoleBasicHandler;

class GetWebHook extends ConsoleBasicHandler
{
	public function execute()
	{
		$response = json_decode($this->api->sdk("getWebhookInfo"));
		print_r($response);
	}
}
