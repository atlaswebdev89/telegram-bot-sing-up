<?php

namespace App\MyCommands;

use App\Core\Handlers\CommandsHandler;

class CalendarCommand extends CommandsHandler
{
	/**
	 * Только для админов команда
	 * Для обычных пользователей не работает
	 */
	public static $admin = TRUE;

	public function execute()
	{
		$date = getdate();
		$calendar = $this->calendar->viewCal($date['mon'], $date['year']);
		$response = $this->api->sendTextWithButton($this->chat_id(), $calendar);

		if ($response->ok) {
			$result = $this->machine->setState('calendar.data');
		}
	}
}
