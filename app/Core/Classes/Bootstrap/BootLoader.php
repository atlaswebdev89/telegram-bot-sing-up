<?php

namespace App\Core\Classes\Bootstrap;

use Pimple\Container as Pimple;
use GuzzleHttp\Client as Client;

use App\Core\Extensions\Calendar;
use App\Core\Classes\Mysql\MysqlPDO;
use App\Core\Classes\Http\HttpClient;

use App\Core\Classes\Monolog\Monolog;
use App\Core\Classes\StateMachine\Stack;
use App\Core\Classes\Mysql\QueryMysqlList;
use App\Core\Classes\Redis\QueryRedisList;

use App\Core\Classes\StateMachine\StateMachine;
use App\Core\Classes\TelegramApi\QueryListTelegramApi;

class BootLoader
{
	static public function registerFactory($rootPath)
	{
		$container = new Pimple();

		$container['rootDir'] = function () use ($rootPath) {
			return $rootPath;
		};
		//GuzzleHttpClient
		$container['http'] = function () {
			return new Client();
		};

		// Class working http request and response 
		$container['http-client'] = function ($container) {
			return new HttpClient($container);
		};

		//QueryList object
		$container['query'] = function ($container) {
			return new QueryListTelegramApi($container);
		};
		// Telegram token 
		$container['token'] = function () {
			return getenv('TELEGRAM_BOT_TOKEN');
		};

		// Telegram uri 
		$container['uri'] = function () {
			return getenv('TELEGRAM_API_URL');
		};

		// Redis server
		$container['redis-server'] = function ($container) {
			if (isset($_ENV['REDIS']) && strcasecmp($_ENV['REDIS'], "TRUE") == 0) {
				return new \Predis\Client(
					[
						"scheme" => "tcp",
						"host" => $_ENV['REDIS_SERVER'],
						"port" => $_ENV['REDIS_PORT'],
					]
				);
			} else {
				return "FALSE";
			}
		};

		// Redis class
		$container['redis'] = function ($container) {
			return new QueryRedisList($container);
		};

		// Mysql server
		$container['mysql-server'] = function ($container) {
			$connect = MysqlPDO::getInstance();
			return $connect->getConnect();
		};

		// Mysql class
		$container['mysql'] = function ($container) {
			return new QueryMysqlList($container);
		};

		$container['state-machine'] = function ($container) {
			return new StateMachine($container);
		};

		$container['monolog'] = function ($container) {
			return new Monolog($container);
		};

		$container['calendar'] = function ($container) {
			return new Calendar($container);
		};
		/**
		 * Это stack пока не используется 
		 * @todo возможно в будущем ипользуем
		 */
		$container['stack'] = function ($container) {
			return new Stack($container);
		};
		/**
		 * Список id для админов
		 */
		$container['admins'] = function ($container) {
			$listAdmin = [];

			$listAdmin[] = getenv("TELEGRAM_ADMIN_CHAT_ID") ? getenv("TELEGRAM_ADMIN_CHAT_ID") : NULL;
			return $listAdmin;
		};

		return $container;
	}
}
