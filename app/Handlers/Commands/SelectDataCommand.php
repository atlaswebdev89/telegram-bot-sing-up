<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;

class SelectDataCommand extends CommandsHandler
{

	public function execute()
	{
		$date = getdate();
		$calendar = $this->calendar->viewCal($date['mon'], $date['year']);
		$response = $this->api->sendTextWithButton($this->chat_id(), $calendar);
		if ($response->ok) {
			$result = $this->machine->setState('selectDate.data');
		}
	}
}
