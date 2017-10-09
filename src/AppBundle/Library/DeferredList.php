<?php
// src/AppBundle/Library/NewsQueue.php

namespace AppBundle\Library;

use AppBundle\Library\Queue;

class DeferredList
{
	private $MAX_CAPACITY = 25; 
    private $list;
	
	public function __construct(array $array = [])
	{
		// check for $array capacity
		$this->list = $array;
	}
	
	public function copy()
	{
		return $this;
	}
	
	public function allocate($capacity, array $array = [])
	{
		// check for $array capacity
		$this->list = $array;
		$this->MAX_CAPACITY = $capacity;
		
		return $this;
	}
	
	public function capacity()
	{
		return count($this->list);
	}
	
	public function exist($value)
	{
		foreach ($this->list as $key => $el) {
			if ($el === $value) {
				return $key;
			}
		}
		
        return -1;
	}
	
	public function isFull()
	{
		return ($this->capacity() == $this->MAX_CAPACITY);
	}
	
	public function isEmpty()
	{
		return empty($this->list);
	}
	
    public function add($value)
    {
		if (($this->exist($value) > -1) || $this->isFull()) {
			return false;
		}
		
        $this->list[] = $value;
		
		return true;
    }
	
	public function remove($value)
	{
		$length = $this->capacity();
		$key = -1;
		
		for ($i = 0; $i < $length; $i++) {
			if ($this->list[$i] === $value) {
				$key = $i;
				break;
			}
		}
		
		if ($key < 0) {
			return false;
		}
		
		unset($this->list[$key]);
		return true;
	}
	
	public function removeByKey($key)
	{
		if ($key >= $this->capacity() || $key < 0) {
			return false;
		}
		
		unset($this->list[$key]);
		return true;
	}
	
	public function clear()
	{
		$this->list = [];
	}
	
	public function toArray()
	{
		return $this->list;
	}
}
?>