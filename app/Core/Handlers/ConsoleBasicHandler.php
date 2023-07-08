<?php

namespace App\Core\Handlers;

use App\Core\Classes\Stubs\StubStorage;

abstract class ConsoleBasicHandler
{
	public $di;
	public $telegram;
	public $storage;
	public $logger;
	public $api;
	public $admin;


	public function __construct(object $telegram, $di)
	{
		$this->di = $di;
		$this->telegram = $telegram;
		// Обьект запросов telegram Api
		$this->api = $this->di['query'];
		$this->logger = $telegram->fileLog;
		// Получаем тип хранилица
		$this->getStorage();
		/**
		 * @proterty array
		 */
		$this->admin = $this->di['admins'];
	}

	protected function getStorage()
	{
		if ($this->telegram->storage) {
			$this->storage = $this->telegram->storage;
		} else {
			$this->storage = new StubStorage();
		}
	}
}
