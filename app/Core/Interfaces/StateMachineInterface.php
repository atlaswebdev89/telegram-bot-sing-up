<?php

namespace App\Core\Interfaces;

interface StateMachineInterface
{
	/**
	 * Формирование полного дерева состояний
	 */
	public function loadTreeState(array|object $arrayData);
	/**
	 * Загрузка отдельной последовательности в дерево состояний
	 */
	public function addLogicality(array $data);

	public function createStructInStorage();
	public function setState(string $state);
	public function nextState();
	public function prevState();
	public function getCurrentState();
	public function getCurrentTransition(string $state);
	public function setDefault();
	public function backStartTransition();
}
