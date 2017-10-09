<?php
// src/AppBundle/Library/TreeNode.php

namespace AppBundle\Library;

class TreeNode
{
	/**
	 *	@type integer
	 */
	public $commentId = 0;
	
	/**
	 *	@type TreeNode
	 */
	public $answers = [];
	
	/**
	 *	@params nodes, node
	 *
	 *	@return array nodes
	 */	
	public function walk(Array &$nodes = [])
	{
		if (empty($this->answers)) {
			return;
			// return $nodes;
		}
		
		foreach ($this->answers as $answer) {
			$nodes[] = $answer->commentId;
			/*$nodes = */$answer->walk($nodes);
		}
		// return $nodes;
	}
	
	/**
	 *	Remove node
	 */
	public function delete()
	{
		unset($this);
	}
	
	public function addAnswer($key)
	{
		$node = new TreeNode;
		$node->commentId = $key;
		$this->answers[] = $node;
	}
	
	/**
	 *	Remove from answers
	 */
	public function deleteAnswer($key)
	{
		foreach ($this->answers as $ansIndex => $answer) {
			if ($answer->commentId == $key) {
				unset($this->answers[$ansIndex]);
				return true;
			}
		}
		return false;
	}
	
	public function findAnswer($key)
	{
		if (empty($this->answers)) {
			return null;
		}
		
		foreach ($this->answers as $answer) {
			if ($answer->commentId == $key) {
				return $answer;
			}
		}
		return null;
	}
	
	public function toArray()
	{
		$nodeAsArray = [
			'commentId' => $this->commentId,
			'answers' => []
		];
		
		foreach ($this->answers as $answer) {
			$nodeAsArray['answers'][] = $answer->toArray();
		}
		
		return $nodeAsArray;
	}
	
	public function decode( \stdClass $nodeObject)
	{
		$this->commentId = $nodeObject->{'commentId'};
		$encodedAnswers = $nodeObject->{'answers'};
		
		if (empty($encodedAnswers)) {
			return;
		}
		
		foreach ($encodedAnswers as $answer) {
			$node = new TreeNode;
			$node->decode($answer);
			$this->answers[] = $node;
		}
	}
}