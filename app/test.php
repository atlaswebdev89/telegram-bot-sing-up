<?php

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Logtail\Monolog\LogtailHandler;

$logger = new Logger("example-app");
$logger->pushHandler(new LogtailHandler("9DYFe79avBbKYJW1ArwZjYDx"));
$logger->error("Something bad happened.");
$logger->info("Log message with structured logging.", [
	"item" => "Orange Soda",
	"price" => 100,
]);
