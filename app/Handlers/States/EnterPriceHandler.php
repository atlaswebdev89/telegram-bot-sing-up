<?php

namespace App\Handlers\States;

use App\Core\Handlers\StatesHandler;

class EnterPriceHandler extends StatesHandler
{

	public $checkIcon = "✅";

	public $table_users = "telegram_table_states";
	public $table_price = "telegram_price_users";
	public $table_temp_date = "telegram_temp_date";

	public function execute()
	{
		$request = $this->getQuery();
		$cbq_id = $this->callbackQueryId();
		$data = explode('_', $request);

		switch (true) {
			case ($data[0] == 'back'):
				$response = $this->getBack();
				if ($response->ok) {
					// меняем состояние машины
					$this->machine->prevState();
				}
				break;
			default:
				if (is_numeric($request)) {
					/** 
					 * Получим id user
					 */
					$sql = "SELECT `id` FROM  " . $this->table_users . " WHERE chat_id =:chat_id";
					$data_array = array(
						'chat_id'      => $this->chat_id(),
					);
					$result = $this->storage->query($sql, 'arraydata', $data_array);
					$id_user = ($result[0]) ? $result[0]['id'] : NULL;
					/**
					 * Получаем выбраную дату (только так может потом что лучшее придумаю)
					 */
					$sql = "SELECT `id_date` FROM  " . $this->table_temp_date . " WHERE chat_id =:chat_id";
					$data_array = array(
						'chat_id'      => $this->chat_id(),
					);
					$result = $this->storage->query($sql, 'arraydata', $data_array);
					$id_date = ($result[0]) ? $result[0]['id_date'] : NULL;
					/**
					 * Добавляем сумму денег
					 */
					if ($id_date && $id_user) {
						$sql = "INSERT INTO " . $this->table_price . " (id_date, id_user, price) VALUES (:id_date, :id_user, :price) ON DUPLICATE KEY UPDATE id_date=:id_date, price=:price";
						$data_array = array(
							'id_date'      => $id_date,
							'id_user'    => $id_user,
							'price' => $request,
						);
						$result = $this->storage->query($sql, 'count', $data_array);
					}
					if ($result) {
						// меняем состояние машины
						$this->machine->nextState();
						$this->responseInfo();
					}
				} else {
					$this->iDontGetIt();
				}
		}
	}

	public function getBack()
	{
		$data = explode("\n", $this->getTextMessageInline());
		$date = getdate(strtotime($data[0]));
		$calendar = $this->calendar->viewCal($date['mon'], $date['year'], null, $this->messageId());
		$response = $this->api->updateMessage($this->chat_id(), $calendar);
		return $response;
	}

	public function iDontGetIt()
	{
		$this->api->sendMessage($this->chat_id(), [
			'text' => 'Не верный ввод суммы. Должны быть цифры'
		]);
	}
}
