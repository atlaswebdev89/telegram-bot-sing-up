<?php

namespace App\Handlers\Cron;

use App\Core\Exceptions\NotSummExceptions;
use App\Core\Handlers\ConsoleBasicHandler;

class NotifyAdminCountSum extends ConsoleBasicHandler
{

	public $chat_id = "496315328";
	public function execute()
	{
		try {
			$text = $this->getSumm();
			$response = $this->api->sendMessage($this->chat_id, [
				'text' => $text,
			]);
			if ($response) {
				$this->telegram->consoleLog->info("Request done succesfull", ['handler' => "NotifyAdmin"]);
			}
		} catch (NotSummExceptions $e) {
			$this->telegram->remoteLog->warning('Cron task: summ Not Found', [
				'date' => date('d-m-Y H:i:s'),
			]);
		}
	}

	public function getSumm()
	{
		$currentDay = date('d-m-Y');

		$sql = "SELECT SUM(price) as prices FROM `telegram_price_users` 
		JOIN `telegram_table_free_slot_date_time` ON `telegram_price_users`.`id_date` = `telegram_table_free_slot_date_time`.`id` 
		WHERE `telegram_table_free_slot_date_time`.`date`=:date";
		$data_array = array(
			'date'      => $currentDay,
		);

		$result = $this->storage->query($sql, 'arraydata', $data_array);
		$sum = ($result[0]) ? $result[0]['prices'] : NULL;
		if ($sum) {
			$text = "Сумма на сегодня " . $currentDay . "\n";
			$text .= $sum . " рублей\n";
		} else {
			throw new NotSummExceptions("Not found");
		}
		return $text;
	}
}
