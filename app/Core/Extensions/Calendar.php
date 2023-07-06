<?php

namespace App\Core\Extensions;

class Calendar
{
	public $di;
	public $hello = "Hello Calendar";
	public $defaultIcon = "☑";
	public $storage;
	public $table = "`telegram_table_free_slot_date_time`";
	public $checkIcon = "✅";
	public $checnIconDays = "🚖";

	public function __construct($di)
	{
		$this->di = $di;
	}

	public function addStorage($storage)
	{
		$this->storage = $storage;
	}

	// Функция проверки есль ли выбранные временные слоты на выбранный день

	protected function getSelectedTimeSlot($currentDate)
	{
		// Делаем запрос в БД Есть ли уcтановленные слоты на выбранную дату
		$sql = "SELECT `slots` FROM  " . $this->table . " WHERE date =:date";
		$data_array = array(
			'date'      => $currentDate,
		);
		$result = $this->storage->query($sql, 'arraydata', $data_array);
		$arraySelectedSlots = ($result) ? json_decode($result[0]['slots'], true) : NULL;

		return $arraySelectedSlots;
	}
	// Функция проверки есть ли дни с установленными слотами
	protected function getSelectedDays($mount, $year)
	{
		// Делаем запрос в БД Есть ли уcтановленные слоты на выбранную дату
		$sql = "SELECT * FROM  " . $this->table . " WHERE date LIKE :date";
		$data_array = array(
			'date'      => "%" . $mount . "-" . $year,
		);
		$result = $this->storage->query($sql, 'arraydata', $data_array);

		// Получим нлмера дней в которых уже есть выбранные слоты
		if ($result) {
			$arraySelectedDayNumber = [];
			foreach ($result as $day) {
				$tempArr = explode("-", $day['date']);
				$arraySelectedDayNumber[] = (json_decode($day['slots'], true)) ? $tempArr[0] : NULL;
			}
			return $arraySelectedDayNumber;
		}
	}


	/**
	 * Изменения состояния кнопки со слотами времени
	 * 
	 */
	public function changeButtonSlotTime($keyboads, $timeslot)
	{
		if ($keyboads) {
			foreach ($keyboads as  $slot) {
				foreach ($slot as  $s) {
					foreach ($s as  $t) {
						if ($t->text == $timeslot) {
							if ($pos = strpos($timeslot, $this->checkIcon)) {
								$t->text =  trim(substr($t->text, 0, $pos));
								$t->callback_data = trim(substr($t->callback_data, 0, strpos($t->callback_data, $this->checkIcon)));
							} else {
								$t->text = $timeslot . " " . $this->checkIcon;
								$t->callback_data = "slot_" . $timeslot . " " . $this->checkIcon;
							}
						}
					}
				}
			}
		}
		return $keyboads;
	}

	public function getNumDayOfWeek($date)
	{
		// получим день недели
		$day = $date->format("w");
		// вернем на 1 меньше [0 - вск]
		return ($day == 0) ? 6 : $day - 1;
	}

	public function getDays($month, $year)
	{
		// создаем дату на начало месяца
		$date = new \DateTime($year . "-" . $month . "-01");

		// массив дней
		$days = [];
		// начало массива
		$line = 0;

		$nameDays = [
			'Пн',
			'Вт',
			'Ср',
			'Чт',
			'Пн',
			'Сб',
			'Вс',
		];
		// Заполняем строку с названиеми дней недели 
		foreach ($nameDays as $name) {
			$days[$line][] = $name;
		}
		$line++;
		// заполним начало если нужно пустыми значениями
		for ($i = 0; $i < $this->getNumDayOfWeek($date); $i++) {
			$days[$line][] = "-";
		}

		// перебираем дни пока месяц совпадает с переданным
		while ($date->format("m") == $month) {
			// добавляем в строку дни
			$days[$line][] = $date->format("d");
			// вс, последний день - перевод строки
			if ($this->getNumDayOfWeek($date) % 7 == 6) {
				// добавляем новую строку
				$line += 1;
			}
			// переходим на следующий день
			$date->modify('+1 day');
		}

		// дозаполняем последнюю строку пустыми значениями
		if ($this->getNumDayOfWeek($date) != 0) {
			for ($i = $this->getNumDayOfWeek($date); $i < 7; $i++) {
				$days[$line][] = "-";
			}
		}
		// вернем массив дней
		return $days;
	}

	public function viewCal($month, $year,  $cbq_id = null, $message_id = null)
	{
		// получаем массив дней месяца
		$dayLines = $this->getDays($month, $year);
		// определим переданную дату
		$current = new \DateTime($year . "-" . $month . "-01");
		// определим параметры переданного месяца
		$current_info = $current->format("m-Y");


		// определим кнопки
		$buttons = [];
		// первый ряд кнопок это навигация календаря
		$buttons[] = [
			[
				"text" => "<<<",
				"callback_data" => "month_" . date("m_Y", strtotime('-1 month', $current->getTimestamp()))
			],
			[
				"text" => $current_info,
				"callback_data" => "monthInfo_" . $current_info
			],
			[
				"text" => ">>>",
				"callback_data" => "month_" . date("m_Y", strtotime('+1 month', $current->getTimestamp()))
			]
		];
		$selectDays = $this->getSelectedDays($month, $year);
		// выводим дни месяца
		foreach ($dayLines as $line => $days) {
			// переберем линию
			foreach ($days as $day) {
				// добавим кнопку в линию
				$buttons[$line + 1][] = [
					// выведем день
					"text" => ($selectDays && in_array($day, $selectDays))
						? $day . " " . $this->checnIconDays
						: $day,
					// поределим параметры
					"callback_data" => ($day > 0 && is_numeric($day))
						// если это день
						? "day_" . $day . "-" . $current_info
						// другое значение
						: "inline"
				];
			}
		}
		// готовим данные
		$data = [
			"text" => "<b>Календарь:</b>\n\n" . $current->format("F Y"),
			"parse_mode" => "html",
			"button" => json_encode(['inline_keyboard' => $buttons])
		];
		if (!is_null($message_id)) {
			$data["message_id"] = $message_id;
		}
		return $data;
	}

	public function viewSlotTime($message_id, $currentDate)
	{
		$slots = [
			'08:00-09:00',
			'09:00-10:00',
			'10:00-11:00',
			'11:00-12:00',
			'13:00-14:00',
			'15:00-16:00',
			'17:00-18:00',
			'19:00-20:00',
		];

		// определим кнопки
		$buttons = [];
		// первый ряд кнопок это навигация календаря
		// выводим дни месяца
		$index = 0;
		foreach ($slots as $line => $slot) {
			// добавим кнопку в линию
			if ($line != 0 && $line % 2 == 0) {
				$index = $index + 1;
			}

			if ($this->getSelectedTimeSlot($currentDate) && in_array($slot, $this->getSelectedTimeSlot($currentDate))) {
				$slot = ($this->checkIcon) ? $slot . " " . $this->checkIcon : $slot . " +1";
			}

			$buttons[$index][] = [
				// выведем день
				"text" => $slot,
				// поределим параметры
				"callback_data" => "slot_" . $slot,
			];
		}

		$buttons[] = [
			[
				// выведем день
				"text" => "Готово",
				// поределим параметры
				"callback_data" => "done",
			],
			[
				// выведем день
				"text" => "Назад",
				// поределим параметры
				"callback_data" => "back",
			],

		];
		// готовим данные
		$data = [
			"text" => "Слоты времени:\n" . $currentDate,
			"parse_mode" => "html",
			"button" => json_encode(['inline_keyboard' => $buttons])
		];
		if (!is_null($message_id)) {
			$data["message_id"] = $message_id;
		}
		return $data;
	}
}
