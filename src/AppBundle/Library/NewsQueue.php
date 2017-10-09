<?php
// src/AppBundle/Library/NewsQueue.php

namespace AppBundle\Library;

use AppBundle\Library\Queue;

class NewsQueue extends Queue
{
    /**
     * Add element by rule:
     *      1. if queue is full 
     *              then remove HEAD (first element)
     *      2. add new element to the end of queue
     * 
     * @param mixed $value
     * 
     * @return void
     */
    public function add($value)
    {
		if ($this->exist($value)) {
			return false;
		}
		
        if ($this->isFull()) {
            $this->pop();
        }
        $this->push($value);
		
		return true;
    }
}
?>