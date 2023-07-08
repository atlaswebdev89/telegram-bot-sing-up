<?php

namespace App\Core\Extensions;

use \App\Core\Traits\GetMessageBot;

class Security
{
	use GetMessageBot;

	public $di;
	public $api;
	public $secret;
	public $storage;

	public function __construct($container)
	{
		$this->di = $container;
		$this->api = $this->di['query'];
		$this->secret = (getenv("TELEGRAM_SECRET")) ?? NULL;
	}

	public function addStorage($storage)
	{
		$this->storage = ($storage) ? $this->di[$storage] : NULL;
	}
	public function checkUserInStorage(object $message)
	{
		$this->message = $message;

		if ($this->secret && $this->storage) {
			if ($this->storage->existsUser($this->chat_id())) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	}

	public function checkUserPassword($message)
	{
		$this->message = $message;

		if ($this->secret && $this->storage) {
			if ($this->secret == $this->getQuery()) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	}
}
