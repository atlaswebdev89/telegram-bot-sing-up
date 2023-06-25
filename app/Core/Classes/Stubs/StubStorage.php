<?php

namespace App\Core\Classes\Stubs;

class StubStorage
{
	public function __call($name, $arguments)
	{
		echo "Not init state storage. Method $name not exists" . PHP_EOL;
	}
}
