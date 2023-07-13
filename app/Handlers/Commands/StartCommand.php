<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class StartCommand extends CommandsHandler
{
	protected string $name = 'start';
	protected string $description = 'Start Command to get you started';

	/**
	 * Клавиатура для админа
	 */
	public function getKeyboards()
	{
		return json_encode([
			'keyboard' => [
				[
					[
						'text' => '📆 Календарь',
					],
					[
						'text' => '💰 Сумма',
					],

				],
				[
					[
						'text' => "📞 Контакты",
					],
					[
						'text' => "⏰ Режим работы",
					],
				]
			],
			'one_time_keyboard' => FALSE,
			'resize_keyboard' => TRUE,
		]);
	}
	/**
	 * Клавиатура для пользователей
	 */
	public function getKeyboardUser()
	{
		return json_encode([
			'keyboard' => [
				[
					[
						'text' => '📆 Выбрать дату',
					],
					[
						'text' => '📋 Инфо',
					],

				],
				[
					[
						'text' => "⏰ Режим работы",
					],
					[
						'text' => "📞 Контакты",
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
				"📆 Календарь",
				"📌 Список клиентов",

			],
			"1" => [
				"💰 На сегодня",
				"💰 На завтра",
			]
		]);
	}

	public function textUser()
	{
		return "Привет. Я телеграмм бот Bastion Travel\n"
			. "Благодаря мне ты можешь указать  сумму денег и выбрать дату из доступных, "
			. "когда тебе будет удобно приехать к моему начальнику и получить cash";
	}
	public function textAdmin()
	{
		return "Привет. Я телеграмм бот <b>Bastion Travel</b>\n"
			. "<b>Приветствую тебя мой повелитель</b>\n"
			. "Вы администратор бота и можете делать все что пожелаете нужным";
	}
	public function execute()
	{
		$result = $this->machine->setDefault();
		$keybords = ($this->isAdmin()) ? $this->getKeybodardAdmin() : $this->getKeyboardUser();
		$text = ($this->isAdmin()) ? $this->textAdmin() : $this->textUser();

		$response = $this->api->sendTextWithButton($this->chat_id(), [
			'text' => $text,
			'button' => $keybords,
			"parse_mode" => "html",
		]);
	}
}
