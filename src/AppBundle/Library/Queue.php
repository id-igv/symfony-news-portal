<?php
// src/AppBundle/Library/NewsQueue.php

namespace AppBundle\Library;

class Queue
{
    protected $MAX_CAPACITY = 10;
    
    /**
     * Queue structure:
     *      [0] <-- HEAD (first added element)
     *      [1] <-- till the END - TAIL
     *       .
     *       .
     *       .
     *      [n-1]
     *      [n] <-- END (last added element)
     */
    protected $queue;
    
    public function __construct($array = []) {
        $this->queue = $array;
    }
    
    /**
     * @param integer $capacity
     * 
     * @return void
     */
    public function allocate($capacity) {
        $this->queue = [];
        $this->MAX_CAPACITY = $capacity;
    }
    
    /**
     * @param void
     * 
     * @return integer
     */
    public function capacity() {
        return count($this->queue);
    }
    
    /**
     * @param void
     * 
     * @return void
     */
    public function clear() {
        $this->queue = [];
    }
    
    /**
     * @param void
     * 
     * @return Queue
     */
    public function copy() {
        return $this;
    }
    
    /**
     * @param void
     * 
     * @return boolean
     */
    public function isEmpty() {
        return empty($this->queue);
    }
    
    /**
     * @param void
     * 
     * @return boolean
     */
    public function isFull() {
        return ($this->capacity() == $this->MAX_CAPACITY);
    }
    
    /**
     * @param void
     * 
     * @return mixed
     */
    public function peek() {
        return $this->queue[0];
    }
    
    /**
     * Remove HEAD
     * 
     * @param void
     * 
     * @return mixed
     */
    public function pop() {
        return array_shift($this->queue);
    }
    
    /** 
     * Add element to the end of queue
     * 
     * @param [ mixed $...values ] 
     * 
     * @return void
     */
    public function push($value) {
        if (!$this->isFull()) {
            $this->queue[] = $value;
        }
    }
    
    /** 
     * @param mixed $value
     * 
     * @return boolean
     */
    public function exist($value) {
		foreach ($this->queue as $el) {
			if ($el === $value) {
				return true;
			}
		}
		
        return false;
    }
    
    /**
     * @param void
     * 
     * @return array
     */
    public function toArray() {
		return $this->queue;
		
		// too much eq below
        // return $this->isEmpty() ? null : $this->queue;
    }
}
?>