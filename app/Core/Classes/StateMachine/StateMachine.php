<?php

namespace App\Core\Classes\StateMachine;

use App\Core\Interfaces\StateMachineInterface;

class StateMachine implements StateMachineInterface
{
	const ROOT = 'root';
	const DEF_ROOT = 'start';
	const STATES = 'subsequence';

	const ERROR_MESSAGE = [
		'addLogicality' => [
			'Не верный формат массива',
			'Не массив'
		],
		'prevState' => [
			'Not fount state'
		],
	];

	public $stateTree;
	public $di;

	public $currentTrans;
	public $currentState;

	public $storage;
	public $chat_id;

	public function __construct($contaner)
	{
		$this->di = $contaner;
		$this->setRootTree();
	}

	public function addStorage(string $storage)
	{
		$this->storage = $this->di[$storage];
		if ($this->storage) $this->storage->createSchema();
	}

	public function createStructInStorage()
	{
	}

	protected function setRootTree($dataArray = null)
	{
		(!$dataArray) ?
			$this->stateTree[self::ROOT] = self::DEF_ROOT :
			$this->stateTree[self::ROOT] = $this->getRootTree($dataArray);
	}

	protected function getRootTree(array $data = []): string
	{
		return (array_key_exists(self::ROOT, $data)) ? $data[self::ROOT] : self::DEF_ROOT;
	}
	/**
	 * 
	 */
	public function loadTreeState(array|object $dataArray)
	{
		$this->setRootTree($dataArray);
		if (array_key_exists(self::STATES, $dataArray)) {
			foreach ($dataArray[self::STATES] as $sub => $states) {
				$this->stateTree[self::STATES][$sub] = $states;
			}
		}
	}
	/**
	 * 
	 */
	public function addLogicality(array $logicality)
	{
		if (is_array($logicality)) {
			$firstIndex = array_key_first($logicality);
			if (gettype($firstIndex) === 'string') {
				$this->stateTree[self::STATES][$firstIndex] = $logicality[$firstIndex];
			} else {
				throw new \Exception($this->getErrorMessage(__METHOD__, 0));
			}
		} else {
			throw new \Exception($this->getErrorMessage(__METHOD__, 1));
		}
	}

	protected function getNameMethod(string $name): string
	{
		$name = explode("::", $name);
		return end($name);
	}

	protected function getErrorMessage(string $method, $key): string
	{
		$nameMethod = $this->getNameMethod($method);
		return self::ERROR_MESSAGE[$nameMethod][$key];
	}

	// Установка состояния для чата
	public function setState(string $state)
	{
		return $this->storage->setState($this->chat_id, $state);
	}

	// Установка начального состояния 
	public function setDefault()
	{
		$state = ($this->stateTree) ? $this->getRootTree($this->stateTree) : $this->getRootTree();
		$this->setState($state);
	}
	// Получение текущего состояния для чата 
	public function getCurrentState()
	{
		return $this->storage->getCurrentState($this->chat_id);
	}

	public function getState($chat_id)
	{
		return $this->storage->getCurrentState($chat_id);
	}

	public function getSingleState(string $state)
	{
		$stateArr = explode('.', $state);
		return (count($stateArr) > 1) ? $stateArr[count($stateArr) - 1] : $state;
	}

	public function getCurrentTransition(string $state): string
	{
		$data = explode('.', $state);
		return (count($data) > 1) ? ($data[array_key_last($data) - 1]) : FALSE;
	}

	public function nextState()
	{
		$state = $this->getCurrentState();
		$trans = $this->getCurrentTransition($state);

		if ($trans) {
			$listStates = $this->stateTree[self::STATES][$trans];
			$currentIndex = array_search($this->getSingleState($state), $listStates);

			if ($currentIndex < (count($listStates) - 1)) {
				$nextState = $listStates[$currentIndex + 1];
				$this->setState($trans . '.' . $nextState);
			} elseif ($currentIndex >= (count($listStates) - 1)) {
				$this->setDefault();
			}
		}
	}

	public function prevState()
	{
		$state = $this->getCurrentState();
		$trans = $this->getCurrentTransition($state);

		if ($trans) {
			if (!array_key_exists($trans, $this->stateTree[self::STATES])) {
				throw new \Exception($this->getErrorMessage(__METHOD__, 0));
			}

			$listStates = $this->stateTree[self::STATES][$trans];
			$currentIndex = array_search($this->getSingleState($state),  $listStates);
			if ($currentIndex > (array_key_first($listStates))) {
				$prevState = $listStates[$currentIndex - 1];
				$this->setState($trans . '.' . $prevState);
			} elseif ($currentIndex <= (array_key_first($listStates))) {
				$this->setDefault();
			}
		}
	}

	public function backStartTransition()
	{
		$state = $this->getCurrentState();
		$trans = $this->getCurrentTransition($state);

		if ($trans) {
			$listState = $this->stateTree[self::STATES][$trans];
			$this->setState($trans . '.' . $listState[array_key_first($listState)]);
		}
	}
}
