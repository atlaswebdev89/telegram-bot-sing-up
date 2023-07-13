<?php

namespace App\Handlers\Commands;

use App\Core\Handlers\CommandsHandler;


class PriceCurrentDayCommand extends CommandsHandler
{
	public $table_users = "telegram_price_users";
	public $table_date = "telegram_table_free_slot_date_time";

	public function execute()
	{
		$response = $this->api->sendMessage($this->chat_id(), [
			'text' => $this->getPriceNextDay(),
		]);
	}

	public function getPriceNextDay()
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
			$text = "На сегодня никто не записался";
		}
		return $text;
	}
}
