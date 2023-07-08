<?php

namespace App\Core\Interfaces;

interface QueryStorageInterface
{
	//Функция сохранения данных в хранилище
	public function saveData($user_id, $username, $field, $value);

	// Функция получения всех данных определенной сущности
	public function getAllData($user_id, $username);

	//	Функция изменения статуса
	public function setStatus($user_id, $username, $field, $new_status);

	// Функция получения статуса текущего состояния системы
	public function getStatus($user_id, $username);

	// Получить значение поля
	public function getValue($user_id, $username, $fields);

	// Обнулить сессию пользователя (удалить)
	public function clearSession($username, $user_id);

	// Начать сессию пользователя. Старая сессия удаляется
	public function setUserStart($user_id, $firstName, $username);







	// Установка состояния для чата 
	public function setState(string $chat_id, string $state, string $username);

	// Получение текущего состояния
	public function getCurrentState(string $chat_id);

	//Создать стуктуру для хранения состояния
	public function createSchema();
}
