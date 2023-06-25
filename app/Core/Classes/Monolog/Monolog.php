<?php

namespace App\Core\Classes\Monolog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\HtmlFormatter;

class Monolog
{
	public function createLogger(string $name)
	{
		return new Logger($name);
	}

	public function getFileLogger($rootDir, $nameLogger = 'telegram')
	{
		if (getenv('TELEGRAN_LOG') == 'true') {
			$fileloger = $this->createLogger($nameLogger);
			if (getenv('TELEGRAM_LOG_DIR')) {
				$dir = $rootDir . '/' . getenv('TELEGRAM_LOG_DIR');
				if (getenv('TELEGRAM_LOG_ACCESS')) {
					$stream_handler1 = new StreamHandler($dir . '/' . getenv('TELEGRAM_LOG_ACCESS'), LOGGER::INFO);
					$fileloger->pushHandler($stream_handler1);
				}
				if (getenv('TELEGRAM_LOG_ERROR')) {
					$stream_handler2 = new StreamHandler($dir . '/' . getenv('TELEGRAM_LOG_ERROR'), LOGGER::WARNING, false);
					$fileloger->pushHandler($stream_handler2);
				}
			}
			return $fileloger;
		}
		return new \App\Core\Classes\Stubs\StubLogger();
	}

	public function getStdoutLogger(string $name = 'stdout-logger')
	{
		$stdoutLogger = $this->createLogger($name);
		$stream_handler = new StreamHandler("php://stdout");
		$stdoutLogger->pushHandler($stream_handler);

		return $stdoutLogger;
	}
}
