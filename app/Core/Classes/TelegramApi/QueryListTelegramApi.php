<?php

namespace App\Core\Classes\TelegramApi;

class QueryListTelegramApi
{

	protected $container;
	protected $updateID;
	protected $httpClient;
	protected $sdkApi;

	public function __construct($container)
	{
		$this->container = $container;
		$this->sdkApi = $this->container['sdk-telegram-bot'];
		$this->httpClient = $container['http-client'];
	}

	public function getUpdate()
	{
		$response = $this->httpClient->queryBuilder(
			'getUpdates',
			[
				'offset' => $this->updateID + 1
			]
		);
		if (!empty($response->result)) {
			$this->updateID = $response->result[count($response->result) - 1]->update_id;
		}
		return $response->result;
	}

	public function sendMessage($chat_id, array $data)
	{
		if (isset($data['parse_mode']) && !empty($data['parse_mode'])) {
			$response = $this->httpClient->queryBuilder('sendMessage', [
				'text' => $data['text'],
				'chat_id' => $chat_id,
				"parse_mode" => (isset($data['parse_mode'])) ? $data['parse_mode'] : 'html',
			]);
		} else {
			$response = $this->httpClient->queryBuilder('sendMessage', [
				'text' => $data['text'],
				'chat_id' => $chat_id,
			]);
		}
		return $response;
	}

	//	Функция текста с кнопками
	public function sendTextWithButton($chat_id, array $arr)
	{
		$response = $this->httpClient->queryBuilder('sendMessage', [
			'text' => $arr['text'],
			'chat_id' => $chat_id,
			'reply_markup' => $arr['button'],
			"parse_mode" => (isset($data['parse_mode'])) ? $data['parse_mode'] : 'html',
		]);
		return $response;
	}

	/**
	 * Функция обратного вызова на нажатие кпонки на inline клавиатуре
	 */
	public function answerCallbackQuery($cbq, array $arr)
	{
		$response = $this->httpClient->queryBuilder('answerCallbackQuery', [
			'callback_query_id' => $cbq,
			'text' => (isset($arr['text'])) ? $arr['text'] : '',
			'show_alert' => (isset($arr['alert'])) ? $arr['alert'] : false,
		]);
		return $response;
	}

	public function updateMessage($chat_id, array $arr)
	{
		$response = $this->httpClient->queryBuilder('editMessageText', [
			'text' => $arr['text'],
			'chat_id' => $chat_id,
			'message_id' => $arr['message_id'],
			'reply_markup' => $arr['button'],
			"parse_mode" => (isset($data['parse_mode'])) ? $data['parse_mode'] : 'html',
		]);
		return $response;
	}
	public function deleteMessage($chat_id, array $arr)
	{
		$response = $this->httpClient->queryBuilder('deleteMessage', [
			'text' => $arr['text'],
			'message_id' => $arr['message_id'],
			'chat_id' => $chat_id,
		]);
		return $response;
	}

	public function sdk($query)
	{
		return $this->sdkApi->$query();
	}

	public function setWebHook($webhook)
	{
		$response = $this->httpClient->queryBuilder('setWebhook', [
			'url' => $webhook,
		]);
		return $response;
	}
}
