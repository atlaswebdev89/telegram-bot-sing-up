<?php

namespace App\Handlers\Console;

use App\Core\Handlers\ConsoleBasicHandler;

class InfoBot extends ConsoleBasicHandler
{
	public function execute()
	{
		echo "I am DONE" . PHP_EOL;
		print_r($this->api->sdk("getMe"));
		echo ($this->api->sdk("getMe")->getFirstName()) . PHP_EOL;
	}
}
