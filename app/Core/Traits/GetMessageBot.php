<?php

namespace App\Core\Traits;

trait GetMessageBot
{

	/**
	 * Пoлучение строки запроса 
	 * или текста инлайн кнопки
	 */
	protected $message;

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
}
