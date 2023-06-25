<?php

namespace App\MyHandlers;

use App\Core\Handlers\StatesHandler;

class OneHandler extends StatesHandler
{
	public function execute()
	{
		echo "One handler  " . __METHOD__ . ' ' . __CLASS__ . PHP_EOL;
	}
}
