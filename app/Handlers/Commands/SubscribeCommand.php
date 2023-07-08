<?php

namespace App\Handlers\Commands;

use Telegram\Bot\Commands\Command;

class SubscribeCommand extends Command
{
	protected string $name = 'subscribe';
	protected string $description = 'Start Command to get you started';

	public function handle()
	{
		$this->replyWithMessage([
			'text' => 'subscribe',
		]);
	}
}
