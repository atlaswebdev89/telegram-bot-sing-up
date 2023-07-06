<?php

namespace App\MyCommands;

use App\Core\Handlers\CommandsHandler;


class StartCommand extends CommandsHandler
{
	protected string $name = 'start';
	protected string $description = 'Start Command to get you started';

	public function getKeyboards()
	{
		return json_encode([
			'keyboard' => [
				[
					[
						'text' => 'Календарь',
					],
					[
						'text' => 'Время доставки',
					],

				],
				[
					[
						'text' => "\xE2\x98\x9D Контакты",
					],
					[
						'text' => "\xF0\x9F\x9A\xAB	Наш сайт",
					],
				]
			],
			'one_time_keyboard' => FALSE,
			'resize_keyboard' => TRUE,
		]);
	}

	/**
	 * Для получения кнопок с учетом роли пользователя (для админа будут все, 
	 * для обычных пользователй часть кнопок не будет доступна)
	 */
	public function getKeybodardAdmin()
	{
		/**
		 * Функция формирования клавиатуры Индекс это ряд Массив-кнопки
		 */
		return $this->getButtonsKeybord([
			"0" => [
				"Календарь",
				"Время доставки",
			],
			"1" => [
				"\xE2\x98\x9D Контакты",
				"\xF0\x9F\x9A\xAB Наш сайт",
			]
		]);
	}

	public function execute()
	{
		$result = $this->machine->setDefault();
		$response = $this->api->sendTextWithButton($this->chat_id(), [
			'text' => 'STATUS MACHINE ' . $this->machine->getCurrentState(),
			'button' => $this->getKeybodardAdmin(),
		]);
	}
}
