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
	public $console = [];

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
	public $consoleLog;

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
		// Logger для Console command
		$this->consoleLog = $this->di['monolog']->getFileLogger($path, 'telegram-console-log');
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
		 * Временная загрушка пна сообщение my_chat_member
		 * Никаких действий не предусмотрено
		 * @todo В будущем разобраться как это использовать my_chat_member
		 */

		if ($this->getStatusMember()) {
			return TRUE;
		}
		/**
		 * Запись в лог запросов
		 */
		$this->infoLogger();

		###########################################################
		/**
		 * Проверку на пароль если он включен Без него не будет доступа
		 * 
		 */
		if (!$this->di['security']->checkUserInStorage($this->message) && !$this->checkAdmin()) {
			switch ($this->getQuery()) {
				case ("/start"):
					$this->sendMsg("Для доступа к боту необходим пароль");
					return FALSE;
					break;
				default:
					if ($this->di['security']->checkUserPassword($this->message)) {
						$this->sendMsg("Доступ разрешен\nДобро пожаловать");
						$this->handlerRun("/start");
						return FALSE;
					} else {
						$this->sendMsg("Пароль не верный\nПопробуйте еще раз");
						return FALSE;
					}
			}
		}
		################################################################

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
				if ($this->checkAdmin()) {
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

	// Функция запуска для cron

	public function consoleRun($handler)
	{
		/**
		 * Запись в лог запросов
		 */
		$this->consoleLog->info("Console command run", ['handler' => $handler]);
		if (isset($this->console[$handler])) {
			$handler = $this->createCommand($this->console[$handler]);
			/**
			 * @todo добавить проверку сущестрования указаного метода
			 */
			/**
			 * Проверяем включена ли для команды опция admin и являеться ли текущий пользователь админом
			 */
			$handler->execute();
			return TRUE;
		}
	}
	// Функция позволяет запускать команды из других обработчиков
	public function handlerRun($command)
	{
		// Модифицирует обьект message 
		$this->changeQuery($command);
		// передать message машине состояний
		if ($this->machine) $this->machine->message = $this->message;

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
		/**
		 * Console commands 
		 */
		if ($_SERVER['argv'] && $_SERVER['argv'][1]) {
			$this->consoleRun($_SERVER['argv'][1]);
			return TRUE;
		};

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
			if ($_SERVER['argv'] && $_SERVER['argv'][1]) {
				$this->consoleRun($_SERVER['argv'][1]);
				return TRUE;
			};

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

	/**
	 * Добавление cron обработчиков
	 */
	public function addCommandsConsole(array $commands)
	{
		if (is_array($commands)) {
			foreach ($commands as $key => $class) {
				$this->console[trim($key)] = $class;
			}
		} else {
			throw new \Exception("No array argument " . __CLASS__ . __METHOD__);
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
			$this->di['security']->addStorage($storage);
		} else {
			throw new StateMachineExceptions('Not set storage for state. In method createStateMachine set type handler(mysql, redis)');
		}

		if ($data) $this->machine->loadTreeState($data);
	}
	#####################################################################

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

	// Отправка сообщений в телеграмм
	protected function sendMsg(string $text)
	{
		$this->queriesTelegramApi->sendMessage($this->chat_id(), [
			'text' => $text
		]);
	}

	/**
	 * Проверка является ли текущий пользователь админом
	 */
	protected function checkAdmin()
	{
		if ($this->di['admins']) {
			if (in_array($this->chat_id(), $this->di["admins"])) {
				return TRUE;
			}
		}
	}
}
