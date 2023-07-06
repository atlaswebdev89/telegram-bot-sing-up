<?php

namespace App\Core\Classes\StateMachine;

use App\Core\Interfaces\StackInterface;
use App\Core\Traits\GetMessageBot;


/**
 * @todo Передалеть exceptions
 */
class Stack implements StackInterface
{
	protected $stack;
	protected $limit;

	public function __construct($di, $limit = 10)
	{
		$this->stack = array();
		$this->limit = $limit;
	}

	public function load($data)
	{
		if ($data) {
			$this->stack = $data;
		}
	}

	public function getAllStack()
	{
		return $this->stack;
	}

	public function push($item)
	{
		if (count($this->stack) < $this->limit) {
			array_unshift($this->stack, $item);
		} else {
			throw new \RunTimeException('Stack is full!');
		}
	}

	public function pop()
	{
		if ($this->isEmpty()) {
			throw new \RunTimeException('Stack is empty!');
		} else {
			return array_shift($this->stack);
		}
	}

	public function top()
	{
		// return current($this->stack);
		return $this->stack[0];
	}

	public function isEmpty()
	{
		return empty($this->stack);
	}
}
