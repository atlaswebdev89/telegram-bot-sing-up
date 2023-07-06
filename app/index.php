<?php

namespace App;

error_reporting(E_ALL);
// Вывод ошибок
ini_set('display_errors', 0);
// Включение лога ошибок и указания файла для записи.
// Обязательно проверять права доступа на файл лога!!!
ini_set('log_errors', 'On');
ini_set('error_log', 'logs/php-errors.log');

require_once 'vendor/autoload.php';

use App\Core\TelegramBot;
use App\MyCommands\BackCommand;

use App\MyCommands\StartCommand;
use App\MyCommands\ContactCommand;
use App\MyHandlerState\AgeHandler;
use App\MyHandlerState\OneHandler;
use App\MyCommands\CalendarCommand;
use App\MyHandlerState\NameHandler;
use App\MyHandlerState\WorkHandler;
use App\MyHandlerState\DataSelectCalendar;
use App\MyHandlerState\TimeSelectCalendar;

try {

	// Загружаем переменные окружения
	\App\Core\Classes\EnvLoader\EnvLoader::envload(__DIR__);

	// Tracker errors
	\Sentry\init(['dsn' => getenv('SENTRY_DSN')]);

	// Основная функция для создания бота telegram
	$telegram = new TelegramBot(__DIR__);

	// Добавить хранилище (в будущем будет redis, mysql, file)
	$telegram->storage('mysql');

	// Одна команда
	$telegram->addCommand('/start', StartCommand::class);

	// Две команды
	$telegram->addCommands([
		'/finish' => FinishCommand::class,
		'/calendar' => CalendarCommand::class,
		'/end' => EndCommand::class,
		"\xE2\x98\x9D Контакты" => ContactCommand::class,
		'Назад' => BackCommand::class,
	]);

	$telegram->aliasCommands(['/calendar' => ['Календарь']]);

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
			],
			'calendar' => [
				'data',
				'time',
			]

		],
	];

	// Укажите тип хранилища для сохранения состояния (пока только mysql и redis, Следующее будет сохранение в файл)
	// Вызываю отдельно чтоб можно было указать иную базу т.е. сам бот может не использовать сторадж
	$telegram->createStateMachine('mysql', $statesTree);
	//$telegram->machine->loadTreeState($statesTree);
	$telegram->machine->addLogicality(['a' => [1, 2, 3]]);

	// $telegram->addHandlerState('users.work', WorkHandler::class);
	$telegram->addHandlerStates([
		'users.age' => AgeHandler::class,
		'users.name' => NameHandler::class,
		'calendar.data' => DataSelectCalendar::class,
		'calendar.time' => TimeSelectCalendar::class,
	]);
	// Запускаем бота в режиме Polling
	// $telegram->startPolling();
	$telegram->start();
} catch (\Throwable $e) {
	// Запись в файл лога
	error_log('Error:exceptions - ' . $e);
	// Трекер Sentry
	\Sentry\captureException($e);
}
