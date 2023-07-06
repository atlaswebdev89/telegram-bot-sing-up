<?php

namespace App\Core\Classes\Monolog;

use Monolog\Level;
use Monolog\Logger;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\ClientBuilder;
use Sentry\Monolog\Handler;

use Monolog\Handler\StreamHandler;

use Logtail\Monolog\LogtailHandler;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;

use Monolog\Formatter\LineFormatter;
use App\Core\Classes\Stubs\StubLogger;
use BGalati\MonologSentryHandler\SentryHandler;
use jacklul\MonologTelegramHandler\TelegramHandler;
use jacklul\MonologTelegramHandler\TelegramFormatter;

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
					$stream_handler1 = new StreamHandler($dir . '/' . getenv('TELEGRAM_LOG_ACCESS'), Level::Info);
					$fileloger->pushHandler($stream_handler1);
				}
				if (getenv('TELEGRAM_LOG_ERROR')) {
					$stream_handler2 = new StreamHandler($dir . '/' . getenv('TELEGRAM_LOG_ERROR'), Level::Warning, false);
					$fileloger->pushHandler($stream_handler2);
				}
			}
			return $fileloger;
		}
		return new StubLogger();
	}

	/**
	 * Не работает в режиме полинга только после завершения скрипта отрабатывает
	 */
	public function getBetterStackLogger(string $name)
	{
		if (getenv('BETTER_STACK_TOKEN')) {
			$logger = $this->createLogger($name);
			$stream = new LogtailHandler(getenv('BETTER_STACK_TOKEN'));
			$logger->pushHandler($stream);
			return $logger;
		}
		return new StubLogger();
	}

	public function getTelegramHandler(string $name)
	{
		if (getenv('TELEGRAM_LOG_TOKEN_BOT') && getenv('TELEGRAM_LOG_CHAT')) {
			$logger = $this->createLogger($name);
			$handler = new TelegramHandler(
				getenv('TELEGRAM_LOG_TOKEN_BOT'),
				getenv('TELEGRAM_LOG_CHAT')
			);
			$handler->setFormatter(new TelegramFormatter());
			$logger->pushHandler($handler);
			return $logger;
		}
		return new StubLogger();
	}

	// Работает только при активации Sentry по входном файле
	public function getSentryHandler(string $name)
	{
		if (getenv('SENTRY_DSN')) {
			$handler = new SentryHandler(SentrySdk::getCurrentHub());
			$handler->setFormatter(new JsonFormatter());
			$logger = $this->createLogger($name);
			$logger->pushHandler($handler);
			return $logger;
		}
		return new StubLogger();
	}

	public function getStdoutLogger(string $name = 'stdout-logger')
	{
		$stdoutLogger = $this->createLogger($name);
		$stream_handler = new StreamHandler("php://stdout");
		$stdoutLogger->pushHandler($stream_handler);

		return $stdoutLogger;
	}
}
