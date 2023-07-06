<?php

namespace App\Core\Classes\Stubs;

class StubStorage
{

	public function __call($name, $arguments)
	{
		error_log("Not init state storage. Method $name not exists");
	}
}
