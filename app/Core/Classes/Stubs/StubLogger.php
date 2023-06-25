<?php

namespace App\Core\Classes\Stubs;

class StubLogger
{
	public function __call($name, $arguments)
	{
		error_log("Not init logger monolog. Method $name not exists. Check .env file");
	}
}
