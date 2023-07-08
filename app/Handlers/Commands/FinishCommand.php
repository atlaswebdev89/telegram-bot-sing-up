<?php

namespace App\Handlers\Commands;

use Telegram\Bot\Commands\Command;

class FinishCommand extends Command
{
	protected string $name = 'start';
	protected string $description = 'Start Command to get you started';

	public function handle()
	{
		// Получить текущий экземпляр telegram
		echo $this->getTelegram()->getMe();

		echo $this->getName() . PHP_EOL;
		$this->triggerCommand('subscribe');
		$this->replyWithMessage([
			'text' => 'Hey, there! Welcome to our bot!',
		]);
	}
}
