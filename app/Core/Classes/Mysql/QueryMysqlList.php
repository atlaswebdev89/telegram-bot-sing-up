<?php

namespace App\Core\Classes\Mysql;

use App\Core\Traits\GetMessageBot;

class QueryMysqlList implements \App\Core\Interfaces\QueryStorageInterface
{
	use GetMessageBot;

	protected $mysql;
	protected $container;
	protected $table;

	public function __construct($container)
	{
		$this->container = $container;
		if ($this->container['mysql-server'] === "FALSE") {
			throw new \Exception("Not connections for mysql. Check .env file");
		}

		$this->mysql = $this->container['mysql-server'];
		$this->table = $_ENV['MYSQL_TABLE_STATE'];
	}

	public function query($sql, $type, array $data = NULL)
	{
		switch ($type) {
			case 'arraydata':
				$row =  $this->mysql->prepare($sql);
				$row->execute($data);
				return $row->fetchAll();
				break;
			case 'count':
				$row =  $this->mysql->prepare($sql);
				$row->execute($data);
				return $row->rowCount();
				break;
			case 'insert':
				$row = $this->mysql->prepare($sql);
				$row->execute($data);
				return $this->mysql->lastInsertId();
				break;
		}
	}

	// Установка состояния для чата 
	public function setState(string $chat_id, string $state)
	{
		$type = 'count';
		$sql = "INSERT INTO " . $this->table . " (chat_id, state) VALUES (:chat_id, :state) ON DUPLICATE KEY UPDATE state=:state";
		$data_array = array(
			'chat_id'      => $chat_id,
			'state'    => $state
		);
		$result =  $this->query($sql, $type, $data_array);
		return $result;
	}

	// Получение текущего состояния
	public function getCurrentState(string $chat_id)
	{
		$type = 'arraydata';
		$sql = "SELECT state FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'  => $chat_id
		);
		$result =  $this->query($sql, $type, $data_array);

		if ($result) {
			return ($result[0]['state']);
		}
		return FALSE;
	}

	public function createSchema()
	{
		//Проверим создана ли таблица
	}


	//Функция сохранения данных в хранилище
	public function saveData($chat_id, $username, $field, $value)
	{
		$type = 'arraydata';
		$sql = "SELECT questions FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'  => $chat_id
		);
		$result =  $this->query($sql, $type, $data_array);
		if ($result && $result[0]['questions']) {
			$data = json_decode($result[0]['questions'], TRUE);
			$data[$field] = ($value);
		} else {
			$data[$field] = ($value);
		}
		$type = 'count';
		$sql = "UPDATE  `" . $this->table . "` SET `questions` = :questions  WHERE `chat_id` = :chat_id";

		$data_array = array(
			'questions'  => json_encode($data, JSON_UNESCAPED_UNICODE),
			'chat_id'  => $chat_id
		);
		$result =  $this->query($sql, $type, $data_array);
		return $result;
	}
	// Функция получения всех данных определенной сущности
	public function getAllData($user_id, $username)
	{
		$type = 'arraydata';
		$sql = "SELECT * FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'  => $user_id
		);
		$result =  $this->query($sql, $type, $data_array);

		if ($result && $result[0]['questions']) {
			$data = json_decode($result[0]['questions'], TRUE);
			unset($result[0]['questions']);
			$result_finally = array_merge($result[0], $data);
			return $result_finally;
		}
		return FALSE;
	}

	//	Функция изменения статуса
	public function setStatus($user_id, $username, $field, $new_status)
	{
		$type = 'count';
		$sql = "UPDATE  `" . $this->table . "` SET `state` = :state  WHERE `chat_id` = :chat_id";

		$data_array = array(
			'state'  => $new_status,
			'chat_id'  => $user_id
		);
		$result =  $this->query($sql, $type, $data_array);
		return $result;
	}

	// Функция получения статуса текущего состояния системы
	public function getStatus($user_id, $username)
	{
		$type = 'arraydata';
		$sql = "SELECT state FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'  => $user_id
		);
		$result =  $this->query($sql, $type, $data_array);
		if ($result) {
			return ($result[0]['state']);
		}
		return FALSE;
	}

	// Получить значение поля
	public function getValue($user_id, $username, $fields)
	{
		$type = 'arraydata';
		$sql = "SELECT state FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'  => $user_id
		);
		$result =  $this->query($sql, $type, $data_array);
		if ($result) {
			return ($result[0][$fields]);
		}
		return FALSE;
	}

	// Обнулить сессию пользователя (удалить)
	public function clearSession($user_id, $username)
	{
		$type = 'count';
		$sql = "DELETE FROM `" . $this->table . "` WHERE `chat_id` = :chat_id";
		$data_array = array(
			'chat_id'      => $user_id
		);
		$result =  $this->query($sql, $type, $data_array);
		return $result;
	}

	// Начать сессию пользователя. Старая сессия удаляется
	public function setUserStart($user_id, $firstName, $username)
	{
		// Удаляем текущую сессию
		$this->clearSession($user_id,  $username);

		$type = 'count';
		$sql = "INSERT INTO " . $this->table . " (chat_id, username, first_name) VALUES (:chat_id, :username, :first_name)";
		$data_array = array(
			'chat_id'      => $user_id,
			'username'    => $username,
			'first_name' => $firstName,
		);
		$result =  $this->query($sql, $type, $data_array);
		return $result;
	}
}
