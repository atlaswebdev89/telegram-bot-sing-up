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

use App\Handlers\Console\InfoBot;
use App\Handlers\Console\GetWebHook;
use App\Handlers\Console\SetWebHook;

use App\Handlers\Commands\BackCommand;
use App\Handlers\Commands\HelpCommand;
use App\Handlers\Commands\ResetCommand;
use App\Handlers\Commands\StartCommand;
use App\Handlers\Commands\AuthorCommand;

use App\Handlers\Commands\ContactCommand;

use App\Handlers\Commands\CalendarCommand;
use App\Handlers\Commands\InfoDataCommand;
use App\Handlers\Commands\PriceCurrentDay;
use App\Handlers\Commands\WorkModeCommand;
use App\Handlers\Cron\NotifyAdminCountSum;
use App\Handlers\States\EnterPriceHandler;
use App\Handlers\States\SelectDataHandler;
use App\Handlers\Commands\ListUsersCommand;
use App\Handlers\States\DataSelectCalendar;
use App\Handlers\States\TimeSelectCalendar;
use App\Handlers\Commands\SelectDataCommand;
use App\Handlers\Commands\PriceNextDayCommand;
use App\Handlers\Commands\PriceCurrentDayCommand;


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

	// Несколько команд команды
	$telegram->addCommands([
		'/calendar' => CalendarCommand::class,
		'/end' => EndCommand::class,
		"/contacts" => ContactCommand::class,
		"/workmode" => WorkModeCommand::class,
		'/selectDate' => SelectDataCommand::class,
		"/info" => InfoDataCommand::class,
		"/priceNextDay" => PriceNextDayCommand::class,
		"/priceCurrentDay" => PriceCurrentDayCommand::class,
		"/listUsers" => ListUsersCommand::class,
		"/help" => HelpCommand::class,
		"/reset" => ResetCommand::class,
		"/author" => AuthorCommand::class,
	]);

	$telegram->aliasCommands([
		'/contacts' => ["📞 Контакты"],
		'/calendar' => ['📆 Календарь'],
		'/info' => ['📋 Инфо'],
		'/selectDate' => ["📆 Выбрать дату"],
		'/workmode' => ["⏰ Режим работы"],
		"/priceNextDay" => ["💰 На завтра"],
		"/priceCurrentDay" => ["💰 На сегодня"],
		"/listUsers" => ["📌 Список клиентов"],
	]);

	// Обработчики для console команд В том числе и cron
	$telegram->addCommandsConsole([
		'notifyAdminCountSum' => NotifyAdminCountSum::class,
		'infoBot' => InfoBot::class,
		'setwebhook' => SetWebHook::class,
		'getwebhook' => GetWebHook::class,
	]);

	$statesTree = [
		'root' => 'START',
		'subsequence' => [
			'calendar' => [
				'data',
				'time',
			],
			'selectDate' => [
				'data',
				'price',
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
		'calendar.data' => DataSelectCalendar::class,
		'calendar.time' => TimeSelectCalendar::class,
		'selectDate.data' => SelectDataHandler::class,
		'selectDate.price' => EnterPriceHandler::class,
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
