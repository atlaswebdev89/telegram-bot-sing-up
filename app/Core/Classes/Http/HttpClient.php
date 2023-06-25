<?php

namespace App\Core\Classes\Http;

use GuzzleHttp\Psr7;

class httpClient
{
	protected $container;
	protected $token;
	protected $api;
	protected $httpClient;

	public function __construct($container)
	{
		$this->container = $container;
		$this->httpClient = $container['http'];
		$this->token = $container['token'];
		$this->api = $container['uri'];
	}

	public function queryBuilder($method, $params = [], $requestType = 'GET')
	{
		//Запрос к Телеграм API
		$url = $this->api . $this->token . "/" . $method;
		//Формируем URL с учетом параметров
		if (!empty($params)) {
			$url .= "?" . http_build_query($params);
		}
		$result = $this->httpClient->request($requestType, $url);
		return json_decode($result->getBody());
	}
}
