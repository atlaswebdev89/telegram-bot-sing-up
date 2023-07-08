<?php

namespace App\Handlers\States;

use App\Core\Handlers\StatesHandler;

class DataSelectCalendar extends StatesHandler
{

	public $checkIcon = "✅";
	public $table = "`telegram_table_free_slot_date_time`";

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
				$this->api->answerCallbackQuery($cbq_id, ['text' => 'Выберете слоты времени']);
				// Получить текущие слоты времени 
				//Сами слоты прописаны в файле класса Лучше убрать для возможности редактирования
				$dataSlotsTime = $this->calendar->viewSlotTime($this->messageId(), $data[1]);
				$response = $this->api->updateMessage($this->chat_id(), $dataSlotsTime);
				if ($response->ok) {
					// меняем состояние машины
					$this->machine->nextState();
				}
				break;
			default:
				$this->iDontGetIt();
		}
	}
}
