<?php

namespace App;

error_reporting(E_ALL);
// Вывод ошибок
ini_set('display_errors', 1);
// Включение лога ошибок и указания файла для записи.
// Обязательно проверять права доступа на файл лога!!!
ini_set('log_errors', 'On');
ini_set('error_log', 'logs/php_errors.log');

require_once 'vendor/autoload.php';

use App\Core\TelegramBot;

use App\MyCommands\StartCommand;
use App\MyCommands\InitCommand;

use App\MyHandlerState\OneHandler;
use App\MyHandlerState\WorkHandler;
use App\MyHandlerState\AgeHandler;
use App\MyHandlerState\NameHandler;

// Tracker errors
\Sentry\init(['dsn' => getenv('SENTRY_DSN')]);

try {
	// Основная функция для создания бота telegram
	$telegram = new TelegramBot(__DIR__);

	// $telegram->storage('mysql');

	// Одна команда
	$telegram->addCommand('/start', StartCommand::class);
	// Две команды
	$telegram->addCommands([
		'/finish' => FinishCommand::class,
		'/end' => EndCommand::class,
		'/init' => InitCommand::class,
	]);


	$statesTree = [
		'root' => 'START',
		'subsequence' => [
			'users' => [
				'name',
				'age',
				'work',
			],
			'rewievs' => [
				'name',
				'state2',
				'state3',
			]
		],
	];

	// Укажите тип хранилища для сохранения состояния (пока только mysql и redis, Следующее будет сохранение в файл)
	// Вызываю отдельно чтоб можно было указать иную базу т.е. сам бот может не использовать сторадж
	$telegram->createStateMachine('mysql', $statesTree);
	//$telegram->machine->loadTreeState($statesTree);
	$telegram->machine->addLogicality(['a' => [1, 2, 3]]);

	$telegram->addHandlerState('users.work', WorkHandler::class);
	$telegram->addHandlerStates([
		'users.age' => AgeHandler::class,
		'users.name' => NameHandler::class
	]);
	// Запускаем бота в режиме Polling
	// $telegram->startPolling();
	$telegram->start();
} catch (\Throwable $e) {
	// Запись в файл лога
	$telegram->fileLog->error('Error bot', ['exceptions' => $e]);
	// Трекер Sentry
	\Sentry\captureException($e);
}
