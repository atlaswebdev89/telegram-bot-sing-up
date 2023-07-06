<?php

namespace App\Core\Classes\Stubs;

class StubMachine
{
	public function __call($name, $arguments)
	{
		error_log("Not init state machine. Method $name not exists" . PHP_EOL);
	}
}
