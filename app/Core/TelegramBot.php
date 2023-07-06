<?php

namespace App\Core;

use App\Core\Classes\EnvLoader\EnvLoader;
use App\Core\Classes\Bootstrap\BootLoader;
use App\Core\Handlers\BasicHandler;

use App\Core\Exceptions\NotExtendsBasicHandler;
use App\Core\Exceptions\NoClassHandlerException;
use App\Core\Exceptions\NoConnectException;

use App\Core\Exceptions\StateMachineExceptions;
use Exception;
use \App\Core\Traits\GetMessageBot;

class TelegramBot
{
	/**
	 * Это трейт с основными функциями для работы с message полученного из чата бота 
	 */
	use GetMessageBot;

	public $events = [];
	public $states = [];

	public $machine;
	public $storage;

	/**
	 * Loggers
	 */
	public $stdLog;
	public $fileLog;
	public $remoteLog;
	public $telegramNotify;
	public $sentryLogger;

	protected $chat_id;
	protected $di;
	protected $queriesTelegramApi;

	# load enviroments
	public function __construct($path)
	{
		$this->di = $this->loader($path);
		$this->queriesTelegramApi = $this->di['query'];

		// Создание логгера monolog
		// File logger 
		$this->fileLog = $this->di['monolog']->getFileLogger($path, 'telegram-log');
		// Logger LogTail
		$this->remoteLog = $this->di['monolog']->getBetterStackLogger('telegram-bot');
		// Logger Telegram
		$this->telegramNotify = $this->di['monolog']->getTelegramHandler('telegram-bot');
		// Logger Sentry
		$this->sentryLogger = $this->di['monolog']->getSentryHandler('telegram-bot');
	}

	# set variables (dotenv) Это ПОКА не используем
	protected function loadEnviroments($path)
	{
		EnvLoader::envload($path);
	}

	//Сервис-контейнер (pimple)
	protected function loader($path)
	{
		return BootLoader::registerFactory($path);
	}

	// Получение хранилище
	public function storage(string $storage)
	{
		try {
			$this->storage = $this->di[$storage];
		} catch (\Pimple\Exception\UnknownIdentifierException $e) {
			$this->sentryLogger->warning('Identifier is not defined', ['exception' => $e]);
		}
	}

	// Главная функция запускается когда есть запросы к боту
	public function run($message)
	{
		$this->message = $message;
		/**
		 * Запись в лог запросов
		 */
		$this->infoLogger();

		// Запускается при наличии handlers для определенного состояния и инициализированной машины состояний
		// А также проверяется чтоб это не было зарегистрированной командой
		if ($this->machine) {
			/**
			 * Прописываем свойство message у state machine
			 */
			$this->machine->message = $this->message;

			if (!array_key_exists($this->getQuery(), $this->events)) {

				// Получить текущее состояние чата
				$stateCurrent = $this->machine->getState($this->chat_id());
				// Найти обработчик и запустить его
				if (isset($this->states[$stateCurrent]) && !empty($this->states[$stateCurrent])) {
					$handler = $this->createCommand($this->states[$stateCurrent]);
					/**
					 * @todo добавить проверку сущестрования указаного метода
					 */
					$handler->execute();
					return TRUE;
				}
			}
		}

		$query = $this->getQuery();
		if (isset($this->events[$query])) {
			$handler = $this->createCommand($this->events[$query]);
			/**
			 * @todo добавить проверку сущестрования указаного метода
			 */
			/**
			 * Проверяем включена ли для команды опция admin и являеться ли текущий пользователь админом
			 */
			if ($this->di['admins'] && $this->events[$query]::$admin) {
				if (in_array($this->chat_id(), $this->di['admins'])) {
					$handler->execute();
					return TRUE;
				}
			} else {
				$handler->execute();
				return TRUE;
			}
		}

		/**
		 * Если нет обработчиков и не вызвана команда 
		 */

		// Отправим что не понимаем полученную команду
		$this->queriesTelegramApi->sendMessage($this->chat_id(), [
			'text' => 'Не понимаю тебя Not found commands'
		]);
		// Запишем в лог если нет заданного обработчика
		$this->fileLog->warning('Not found handler request', [
			'chat_id' => $this->chat_id(),
			'username' => $this->username(),
			'request' => $this->getQuery(),
		]);
	}
	// Функция позволяет запускать команды из других обработчиков
	public function handlerRun($command)
	{
		// Модифицирует обьект message 
		$this->changeQuery($command);

		if (isset($this->events[$command])) {
			$handler = $this->createCommand($this->events[$command]);
			/**
			 * @todo добавить проверку сущестрования указаного метода
			 */
			$handler->execute();
		}
	}

	// function polling for get all message from telegram bots
	public function startPolling()
	{
		// count error connection
		$countFail = 0;

		// loop function
		while (true) {
			if ($countFail > 5) {
				throw new NoConnectException("STOP telegram bot reason not connection. Fail count connection {$countFail}");
			}
			try {
				echo "Pollings ...\n";
				sleep(2);
				$data = $this->queriesTelegramApi->getUpdate();
				//Проходим по всем сообщения
				foreach ($data as $message) {
					$this->run($message);
				}
				$countFail = 0;
			} catch (\GuzzleHttp\Exception\ConnectException $e) {
				$countFail += 1;
			}
		}
	}

	// Функция получения необработаных данных (например json ) и
	// сохранения их в массиве
	public function getRawData()
	{
		$body = file_get_contents('php://input');
		$arr = json_decode($body);
		return $arr;
	}

	// Функция для работы в режиме WebHooks
	public function start()
	{
		// Получаем данные
		$message = $this->getRawData();
		if (empty($message)) {
			echo "Telegram bot from brestburger working!!!" . PHP_EOL;
			$this->fileLog->error('Get request in http client. Not found data');
			exit;
		}
		// Запускаем главную функцию
		$this->run($message);
	}

	public function addCommand(string $command, string $className)
	{
		$this->events[trim($command)] = $className;
	}

	public function addCommands(array $commands)
	{
		if (is_array($commands)) {
			foreach ($commands as $key => $class) {
				$this->events[trim($key)] = $class;
			}
		} else {
			throw new \Exception("No array argument");
		}
	}
	// Возможность добавлять псевданимы на команды
	public function aliasCommands(array $data)
	{
		if (is_array($data)) {
			foreach ($data as $key => $data) {
				if (array_key_exists($key, $this->events)) {
					$handler = $this->events[$key];
					foreach ($data as $alias) {
						$this->events[$alias] = $handler;
					}
				}
			}
		}
	}


	public function addHandlerState(string $state, $className): void
	{
		$this->states[$state] = $className;
	}


	public function addHandlerStates(array $states): void
	{
		if (is_array($states)) {
			foreach ($states as $state => $className) {
				$this->states[$state] = $className;
			}
		} else {
			throw new \Exception("No array");
		}
	}

	public function createCommand(string $ClassName)
	{
		/**
		 * @todo добавить проверку существования указаного класса
		 */
		if (class_exists($ClassName)) {
			return  new $ClassName($this, $this->di, $this->message);
		} else {
			throw  new NoClassHandlerException("Not found class " . $ClassName . " " . __METHOD__);
		}
	}

	#####################################################################
	// StateMachine
	public function createStateMachine(string $storage = null, array $data = null)
	{
		if ($storage) {
			$this->machine = $this->di['state-machine'];
			$this->machine->addStorage($storage);
		} else {
			throw new StateMachineExceptions('Not set storage for state. In method createStateMachine set type handler(mysql, redis)');
		}

		if ($data) $this->machine->loadTreeState($data);
	}
	#####################################################################
	/**
	 * Функция проверяет включен режим только админа для комманды
	 */
	public function ButtonIsAdmin(string $command)
	{
		if (is_string($command)) {
			if (isset($this->events[$command]) && $this->events[$command]::$admin) {
			}
		}
	}

	// Запись в лог запроса Чтоб нечего не потерялось
	protected function infoLogger()
	{
		$this->fileLog->info('Request in telegram', [
			'chat_id' => $this->chat_id(),
			'username' => $this->username(),
			'request' => $this->getQuery(),
		]);

		$this->remoteLog->info('Request in telegram', [
			'chat_id' => $this->chat_id(),
			'username' => $this->username(),
			'request' => $this->getQuery(),
		]);
	}
}
