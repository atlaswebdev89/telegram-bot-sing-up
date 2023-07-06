<?php

namespace App\Core\Classes\EnvLoader;

use Dotenv\Dotenv;

class EnvLoader
{

	static public function envload($path)
	{
		$dotenv = Dotenv::createUnsafeImmutable($path);
		$dotenv->load();
	}
}
