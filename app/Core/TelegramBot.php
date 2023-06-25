<?php

namespace App\Core;

use App\Core\Classes\EnvLoader\EnvLoader;
use App\Core\Classes\Bootstrap\BootLoader;
use App\Core\Handlers\BasicHandler;

use App\Core\Exceptions\NotExtendsBasicHandler;

use App\Core\Exceptions\StateMachineExceptions;

class TelegramBot
{

	/**
	 * Это трейт с основными функциями для работы с message полученного из чата бота 
	 */
	use \App\Core\Traits\GetMessageBot;


	public $events = [];
	public $states = [];


	public $machine;
	public $storage;

	public $stdLog;
	public $fileLog;

	protected $chat_id;
	protected $di;
	protected $queriesTelegramApi;

	protected $books = [];
	protected $buttons = [];



	# load enviroments
	public function __construct($path)
	{
		$this->loadEnviroments($path);
		$this->di = $this->loader();
		$this->queriesTelegramApi = $this->di['query'];
		// Создание логгеров monolog
		$this->fileLog = $this->di['monolog']->getFileLogger($path, 'telegram-log');
	}

	# set variables (dotenv)
	protected function loadEnviroments($path)
	{
		EnvLoader::envload($path);
	}

	//Сервис-контейнер (pimple)
	protected function loader()
	{
		return BootLoader::registerFactory();
	}

	// Получение хранилище
	public function storage(string $storage)
	{
		$this->storage = $this->di[$storage];
	}

	// Запись в лог запроса Чтоб нечего не потерялось
	protected function infoLogger()
	{
		echo "LOGGET RUN" . PHP_EOL;
		if ($this->fileLog) {
			$this->fileLog->info('Request in telegram', [
				'chat_id' => $this->chat_id(),
				'username' => $this->username(),
				'message' => $this->getQuery(),
			]);
		}
	}

	// Главная функция запускается когда есть запросы к боту
	public function run($message)
	{
		$this->message = $message;
		// Записываем запрос в лог
		$this->infoLogger();
		// Запускается при наличии handlers для определенного состояния и инициализированной машины состояний
		if ($this->machine) {
			// Получить текущее состояние чата
			$stateCurrent = $this->machine->getState($this->chat_id());
			// Найти обработчик и запустить его
			if (isset($this->states[$stateCurrent]) && !empty($this->states[$stateCurrent])) {
				$handler = $this->createCommand($this->states[$stateCurrent]);
				/**
				 * @todo добавить проверку сущестрования указаного метода
				 */
				$handler->execute();
			}
		}

		$query = $this->getQuery();
		if (isset($this->events[$query])) {
			$handler = $this->createCommand($this->events[$query]);
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
				throw new \Exception("STOP telegram bot reason not connection. Fail count connection {$countFail}");
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
			} catch (\Exception $e) {
				$countFail += 1;
				error_log("Not connection... Error Message {$e->getMessage()}");
				echo "Not connection... Try later\n";
				echo "Error Message {$e->getMessage()}\n";
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
		$this->events[$command] = $className;
	}

	public function addCommands(array $commands)
	{
		if (is_array($commands)) {
			foreach ($commands as $key => $class) {
				$this->events[$key] = $class;
			}
		} else {
			throw new \Exception("No array argument");
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
			throw  new \Exception("Not found class " . __METHOD__);
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
}
