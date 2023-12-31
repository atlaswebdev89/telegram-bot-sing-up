<?php

namespace App\Core\Traits;

trait GetMessageBot
{
	/**
	 * Пoлучение строки запроса 
	 * или текста инлайн кнопки
	 */
	public $message;

	public function getQuery()
	{
		switch (true) {
			case (isset($this->message->callback_query->data)):
				$query = $this->message->callback_query->data;
				break;
			case (isset($this->message->message->text)):
				$query = $this->message->message->text;
				break;
			default:
				$query = FALSE;
				break;
		}
		return $query;
	}

	public function changeQuery($query)
	{
		if (isset($this->message->message->text))
			$this->message->message->text = $query;
	}

	/**
	 * Получение chat_id
	 */
	public function chat_id()
	{
		switch (true) {
			case (isset($this->message->message->chat->id)):
				$chat_id = $this->message->message->chat->id;
				break;
			case (isset($this->message->callback_query->message->chat->id)):
				$chat_id = $this->message->callback_query->message->chat->id;
				break;
			default:
				$chat_id = FALSE;
				break;
		}
		return $chat_id;
	}

	public function username()
	{
		switch (true) {
			case (isset($this->message->message->chat->username)):
				$username = $this->message->message->chat->username;
				break;
			case (isset($this->message->callback_query->message->chat->username)):
				$username = $this->message->callback_query->message->chat->username;
				break;
			default:
				$username = FALSE;
				break;
		}
		return $username;
	}

	public function firstname()
	{
		switch (true) {
			case (isset($this->message->message->chat->first_name)):
				$firstname = $this->message->message->chat->first_name;
				break;
			case (isset($this->message->message->chat->title)):
				$firstname =
					"Chat - " . $this->message->message->chat->title;
				break;
			default:
				$firstname =
					"nousername";
				break;
		}
		return $firstname;
	}

	public function messageId()
	{
		switch (true) {
			case (isset($this->message->callback_query->message->message_id)):
				$messageId = $this->message->callback_query->message->message_id;
				break;
			case (isset($this->message->message->message_id)):
				$messageId = $this->message->message->message_id;
				break;
			default:
				$messageId = FALSE;
				break;
		}
		return $messageId;
	}

	public function callbackQueryId()
	{
		if (isset($this->message->callback_query->id)) {
			return $this->message->callback_query->id;
		}
	}

	// Получить клавиатуру после нажатия inline кнопки
	public function getInlineKeyboards()
	{
		if (isset($this->message->callback_query->message->reply_markup)) {
			return $this->message->callback_query->message->reply_markup;
		}
	}

	// Получить значение текста в сообщении при нажатии на инлайн кнопку
	public function getTextMessageInline()
	{
		if (isset($this->message->callback_query->message->text)) {
			return $this->message->callback_query->message->text;
		}
	}

	// Развернуть массив со значеними выбраных кнопок на клаиватуре 
	protected function makeListInlineButtons()
	{
		$keyboards = $this->getInlineKeyboards();
		if ($keyboards) {
			$result = [];
			foreach ($keyboards as $slots) {
				foreach ($slots as $slot) {
					foreach ($slot as $item) {
						if ($item->text) $result[] = $item->text;
					}
				}
			}
			return $result;
		}
	}

	/**
	 * Получение статуса сообщения my_chat_member
	 * Это сообщение получает телеграмм бот при удалении и блокировке бота и при 
	 * Возвращении к боту нажатием кнопки Restart bot
	 */
	protected function getStatusMember()
	{
		if (isset($this->message->my_chat_member->new_chat_member->status)) {
			return $this->message->my_chat_member->new_chat_member->status;
		}
	}
}
