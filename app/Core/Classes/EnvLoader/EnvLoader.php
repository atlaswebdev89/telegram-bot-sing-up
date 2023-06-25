<?php

namespace App\Core\Classes\EnvLoader;

use Dotenv\Dotenv;

class EnvLoader
{

	static public function envload($path)
	{
		try {
			$dotenv = Dotenv::createUnsafeImmutable($path);
			$dotenv->load();
		} catch (\Exception $e) {
			echo "Not load env!!!";
		}
	}
}
