<?php

namespace App\Core\Handlers;

use App\Core\Classes\Stubs\StubMachine;
use App\Core\Classes\Stubs\StubStorage;

abstract class BasicHandler
{

	use \App\Core\Traits\GetMessageBot;

	public $di;
	public $telegram;
	public $machine;
	public $storage;
	public $logger;


	public function __construct(object $telegram, $di, object $message)
	{
		$this->di = $di;
		$this->telegram = $telegram;
		$this->message = $message;
		$this->logger = $telegram->fileLog;
		// Получаем тип хранилица
		$this->getStorage();
		// Получаем обьект state machine 
		$this->getStateMachine();
	}

	protected function getStateMachine()
	{
		if (isset($this->telegram->machine)) {
			$this->machine = $this->telegram->machine;
			$this->machine->chat_id = $this->chat_id();
		} else {
			$this->machine = new StubMachine();
		}
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
