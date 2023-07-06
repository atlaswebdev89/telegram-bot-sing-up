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
}
