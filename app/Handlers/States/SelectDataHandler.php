<?php

namespace App\Handlers\States;

use App\Core\Handlers\StatesHandler;

class SelectDataHandler extends StatesHandler
{

	public $checkIcon = "✅";
	public $table = "telegram_temp_date";
	public $table_date = "telegram_table_free_slot_date_time";

	public function execute()
	{
		$data = $this->getQuery();
		$cbq_id = $this->callbackQueryId();
		$data = explode('_', $data);

		switch (true) {
			case ($data[0] == "month"):
				$calendar = $this->calendar->viewCal($data[1], $data[2], $cbq_id, $this->messageId());
				$response = $this->api->updateMessage($this->chat_id(), $calendar);
				break;
			case ($data[0] == 'day'):
				/**
				 * Проверить чтоб запись была не раньше чем завтра
				 */
				$current = date("d-m-Y");
				$selectDate = $data[1];
				if (strtotime($selectDate) <= strtotime($current)) {
					$this->api->answerCallbackQuery($cbq_id, ['text' => 'Запись осуществляется на следующий день']);
					break;
				}
				// Получить текущие слоты времени и отобразим для понимания
				// Когда можно забрать деньги
				$getTimeSlots = $this->calendar->getTimeSlots($data[1], $this->messageId());
				if ($getTimeSlots) {
					$this->userDateAddStorage($selectDate);
					$this->api->answerCallbackQuery($cbq_id, ['text' => 'Укажите сумму денег']);
					$response = $this->api->updateMessage($this->chat_id(), $getTimeSlots);
					if ($response->ok) {
						// меняем состояние машины
						$this->machine->nextState();
					}
				} else {
					$this->api->answerCallbackQuery($cbq_id, ['text' => "Нет времени на указанную дату Выберете другую"]);
				}
				break;
			default:
				$this->iDontGetIt();
		}
	}

	protected function userDateAddStorage($selectDate)
	{
		/**
		 * Получаем id_date
		 */
		$sql = "SELECT `id` FROM  " . $this->table_date . " WHERE date =:date";
		$data_array = array(
			'date'      => $selectDate,
		);
		$result = $this->storage->query($sql, 'arraydata', $data_array);
		$id_date = ($result[0]) ? $result[0]['id'] : NULL;

		/**
		 * Предварительно записываем во временную таблицу значение выбраной даты для текущего user
		 */
		if ($id_date) {
			$sql = "INSERT INTO " . $this->table . " (id_date, chat_id) VALUES (:id_date, :chat_id) ON DUPLICATE KEY UPDATE id_date=:id_date";
			$data_array = array(
				'id_date'      => $id_date,
				'chat_id'    => $this->chat_id(),
			);
			$result = $this->storage->query($sql, 'count', $data_array);
		}
		if ($result) return TRUE;
	}
}
