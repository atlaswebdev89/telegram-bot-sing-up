<?php

namespace App\Core\Interfaces;

interface StackInterface
{
	public function push($data);
	public function pop();
	public function top();
	public function isEmpty();
	public function load($data);
	public function getAllStack();
}
