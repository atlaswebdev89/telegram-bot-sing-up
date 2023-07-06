<?php

namespace App\MyHandlerState;

use App\Core\Handlers\StatesHandler;

class TimeSelectCalendar extends StatesHandler
{

	public $checkIcon = "✅";
	public $table = "`telegram_table_free_slot_date_time`";

	public function execute()
	{
		$data = $this->getQuery();
		$cbq_id = $this->callbackQueryId();
		$data = explode('_', $data);

		switch (true) {
				// Если выбран слот времени
			case ($data[0] == 'slot'):
				$keyboadsRequest = ($this->getInlineKeyboards());
				$keyboads = $this->calendar->changeButtonSlotTime($keyboadsRequest, $data[1]);

				$response = $this->api->updateMessage(
					$this->chat_id(),
					[
						'text' => $this->getTextMessageInline(),
						'message_id' => $this->messageId(),
						'button' => json_encode($keyboads),
					]
				);
				break;
				// Если нажата кнопка назад
			case ($data[0] == 'back'):
				$response = $this->getBack();
				if ($response->ok) {
					// меняем состояние машины
					$this->machine->prevState();
				}
				break;
			case ($data[0] == 'done'):
				$date = $this->getDateFromDone();
				$currnetDate = date('d-m-Y');
				/**
				 * Проверяем что текущая дата была меньше даты выставления слотов времени
				 */
				if (strtotime($date) >= strtotime($currnetDate)) {
					$selectedSlots = $this->getSelectedSlotTimes();
					/**
					 * Save in storage
					 */
					$sql = "INSERT INTO " . $this->table . " (date, slots) VALUES (:date, :slots) ON DUPLICATE KEY UPDATE slots=:slots";
					$data_array = array(
						'date'      => $date,
						'slots'    => $selectedSlots
					);
					$result = $this->storage->query($sql, 'count', $data_array);
				}
				$this->api->answerCallbackQuery($cbq_id, ['text' => 'Временные слоты сохранены']);
				// меняем состояние машины
				$this->machine->prevState();
				$response = $this->getBack();
				break;
			default:
				$this->iDontGetIt();
		}
	}

	public function getBack()
	{
		$data = explode(":", $this->getTextMessageInline());
		$date = getdate(strtotime($data[1]));
		$calendar = $this->calendar->viewCal($date['mon'], $date['year'], null, $this->messageId());
		$response = $this->api->updateMessage($this->chat_id(), $calendar);
		return $response;
	}

	// Получаем дату из текса сообшения
	public function getDateFromDone()
	{
		$textMessage = $this->getTextMessageInline();
		$textMessageArr = explode("\n", $textMessage);
		return $textMessageArr[1];
	}

	// Получаем выбраные слоты времени ввиде коллекции (массива) без вложености
	public function getSelectedSlotTimes()
	{
		$keyboads = $this->makeListInlineButtons();
		if ($keyboads) {
			$result = [];
			foreach ($keyboads as $slot) {
				if ($pos = strpos($slot, $this->checkIcon)) {
					$result[] =  trim(substr($slot, 0, $pos));
				}
			}
			return json_encode($result, JSON_UNESCAPED_UNICODE);
		}
	}
}
