<?php

namespace App\Core\Handlers;

use App\Core\Classes\Stubs\StubMachine;
use App\Core\Classes\Stubs\StubStorage;

use App\Core\Classes\Calendar\Calendar;

abstract class BasicHandler
{

	use \App\Core\Traits\GetMessageBot;

	public $di;
	public $telegram;
	public $machine;
	public $storage;
	public $logger;
	public $api;

	####################
	public $calendar;
	########################################

	public static $admin = FALSE;

	public function __construct(object $telegram, $di, object $message)
	{
		$this->di = $di;
		$this->telegram = $telegram;
		$this->message = $message;

		$this->logger = $telegram->fileLog;
		// Получаем тип хранилица
		$this->getStorage();
		// Получаем обьект state machine 
		$this->getStateMachine();
		// Обьект запросов telegram Api
		$this->api = $this->di['query'];

		// Определяем календарь 
		$this->calendar = $di['calendar'];
		($this->calendar) ? $this->calendar->addStorage($this->storage) : NULL;

		###############################
	}

	protected function getStateMachine()
	{
		if (isset($this->telegram->machine)) {
			$this->machine = $this->telegram->machine;
			$this->machine->chat_id = $this->chat_id();
		} else {
			$this->machine = new StubMachine();
		}
	}

	protected function getStorage()
	{
		if ($this->telegram->storage) {
			$this->storage = $this->telegram->storage;
		} else {
			$this->storage = new StubStorage();
		}
	}

	/**
	 * Функция проверки админ или обычный пользователь
	 */
	public function isAdmin()
	{
		return (in_array($this->chat_id(), $this->di['admins'])) ? TRUE : FALSE;
	}

	public function getBasicArrayButtons()
	{
		return [
			'keyboard' => [],
			'one_time_keyboard' => FALSE,
			'resize_keyboard' => TRUE,
		];
	}

	// Функция формирования массива кнопок с учетом админской части
	public function getButtonsKeybord(array $data)
	{
		if (is_array($data) && !empty($data)) {
			$buttons = $this->getBasicArrayButtons();
			foreach ($data as $key => $array) {
				$result = [];
				foreach ($array as $k => $button) {

					if (isset($this->telegram->events[$button]) && $this->telegram->events[$button]::$admin) {
						if (in_array($this->chat_id(), $this->di['admins'])) {
							$result[] = ['text' => $button];
						}
					} else {
						$result[] = ['text' => $button];
					}
				}
				if (!empty($result)) $buttons['keyboard'][] = $result;
			}
			return json_encode($buttons);
		}
	}


	/** 
	 * Метод для получения данных о сумме и дате выдаче
	 */
	public function responseInfo()
	{
		$table_price = "telegram_price_users";
		/**
		 * Делаем запрос на получение информации о записи пользователя
		 */
		$sqls = "SELECT date, slots, price FROM " . $table_price . " 
			LEFT JOIN `telegram_table_states` ON `telegram_table_states`.`id` = `telegram_price_users`.`id_user`
			LEFT JOIN `telegram_table_free_slot_date_time` ON `telegram_price_users`.`id_date`=`telegram_table_free_slot_date_time`.`id`
			WHERE `telegram_table_states`.`chat_id` = :chat_id";
		$data_array = array(
			'chat_id'      => $this->chat_id(),
		);
		$result = $this->storage->query($sqls, 'arraydata', $data_array);

		if ($result) {
			$result = $result[0];
			$text = "<b>Информация</b>\n\n";
			$text .= "<b>Дата выдачи</b>\n";
			$text .= $result['date'] . "\n\n";
			$slots = json_decode($result['slots']);
			if ($slots) {
				$text .= "<b>Время выдачи</b>\n";
				foreach ($slots as $slot) {
					$text .= $slot . "\n";
				}
			}
			$text .= "<b>\nСумма</b>:\n";
			$text .= $result['price'] . " рублей\n";
		} else {
			$text = "Вы пока не записаны!\nНеобходимо нажать кнопку \nВыбрать дату\n или ввести\n/selectDate";
		}

		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => $text,
			"parse_mode" => "html",
		]);
	}
}
