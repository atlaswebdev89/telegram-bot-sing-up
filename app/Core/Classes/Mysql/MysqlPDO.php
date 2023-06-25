<?php

namespace App\Core\Classes\Mysql;

class MysqlPDO
{
	private static $connect;
	private static $_instance;

	protected function __construct()
	{
		if (strcasecmp($_ENV['MYSQL'], 'true')) {
			throw new \PDOException("Нет подключения к БД");
		}
		$this->connectDB();
	}

	protected function connectDB()
	{
		try {
			if (self::$connect instanceof \PDO) {
				return self::$connect;
			}

			self::$connect = new \PDO("mysql:host=" . $_ENV['MYSQL_SERVER'] . ";port=" . $_ENV['MYSQL_PORT'] . ";dbname=" . $_ENV['MYSQL_DB'] . ";charset=utf8mb4", $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
			self::$connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			self::$connect->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			return self::$connect;
		} catch (\Exception $e) {
			/**
			 * @todo сделать обработку ошибки при отсуствии подклбчения к БД записью в ЛОг через монолог Но чтоб скрипт работал 
			 */
			echo "Not connect " . $e->getMessage() . PHP_EOL;
		}
	}

	static public function getInstance()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getConnect()
	{
		return self::$connect;
	}
}
