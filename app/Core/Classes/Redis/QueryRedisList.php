<?php

namespace App\Core\Classes\Redis;

class QueryRedisList implements \TelegramBot\Interfaces\QueryStorageInterface
{
	protected $redis;
	protected $container;

	public function __construct($container)
	{
		$this->container = $container;
		if ($this->container['redis-server'] === "FALSE") {
			throw new \Exception("Not connections for redis. Check .env file");
		}
		$this->redis = $this->container['redis-server'];
	}

	// add data in hash table
	public function saveData($user_id, $username, $field, $value = "waiting")
	{
		$this->redis->hset($username . ':' . $user_id, $field, $value);
	}

	public function getAllData($user_id, $username)
	{
		return $this->redis->hgetall($username . ':' . $user_id);
	}

	public function setStatus($user_id, $username, $field, $new_status)
	{
		$this->saveData($user_id, $username, $field, $new_status);
	}
	// получить текущий статус
	public function getStatus($user_id, $username)
	{
		return $this->redis->hget($username . ':' . $user_id, 'status');
	}
	// Получить значение поля
	public function getValue($user_id, $username, $fields)
	{
		return $this->redis->hget($username . ':' . $user_id, $fields);
	}

	// Обнулить сессию пользователя (удалить)
	public function clearSession($username, $user_id)
	{
		$this->redis->del($username . ':' . $user_id);
	}

	public function setUserStart($user_id, $firstName, $username)
	{
		// Удаляем текущую сессию
		$this->clearSession($username, $user_id);
		$this->redis->hmset(
			$username . ':' . $user_id,
			[
				"first_name" => $firstName,
				"username" => $username,
			]
		);
	}
}
