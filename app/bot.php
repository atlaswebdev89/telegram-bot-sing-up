<?php

namespace App;


require __DIR__ . '/vendor/autoload.php';

use Telegram\Bot\Api;
use App\MyCommands\FinishCommand;
use App\MyCommands\SubscribeCommand;

$telegram = new Api('5786092131:AAFN2_x1WUc0X5c4SAhME2M2TONOK_FfdAE');

// Установка webhooks
// $response = $telegram->setWebhook(['url' => 'https://example.com/<token>/webhook']);

// Получение обновление через WebHooks
// print_r($updates = $telegram->getWebhookUpdate());

// Удаление webhook
$response = $telegram->removeWebhook();

// Example usage
$response = $telegram->getMe();

echo $botId = $response->getId() . PHP_EOL;
echo $firstName = $response->getFirstName() . PHP_EOL;
echo $username = $response->getUsername() . PHP_EOL;


echo "start" . PHP_EOL;


$telegram->addCommands(
	[
		FinishCommand::class,
		SubscribeCommand::class,
	]
);


$updateID = 0;
while (true) {
	sleep(3);
	echo "Polling" . PHP_EOL;

	$response = $telegram->getUpdates(['offset' => $updateID + 1]);
	if ($response) {
		// Найти последний элемент 
		$last_message = $response[array_key_last($response)];
		// $last_message = $response[count($response) - 1];
		$updateID = $last_message->update_id;
	}

	print_r($response);
	$update = $telegram->commandsHandler();
}
