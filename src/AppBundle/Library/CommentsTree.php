<?php
// src/AppBundle/Library/CommentsTree.php

namespace AppBundle\Library;

use AppBundle\Library\TreeNode;

class CommentsTree
{
	/**
	 *	@type TreeNode
	 */
	public $root;
	
	public function __construct()
	{
		$this->root = new TreeNode;
	}
	
	public function isEmpty()
	{
		return empty($this->root->answers);
	}
	
	public function display(){}
	
	/**
	 *	@param integer
	 *
	 *	@return TreeNode
	 */
	public function find($key, $node = null)
	{
		if (is_null($node)) {
			if ($key == 0) {
				return $this->root;
			}
			
			if (empty($this->root->answers)) {
				return null;
			}
			
			$node = $this->root;
		}
		
		foreach ($node->answers as $answer) {
			if ($answer->commentId == $key) {
				return $answer;
			}
			else {
				$res = $this->find($key, $answer);
				if (!is_null($res)) {
					return $res;
				}
			}
		}
		
		return null;
	}
	
	/**
	 *	@return array
	 */
	public function findAll($offset = 0, $limit = null)
	{
		if (empty($this->root->answers) || $offset < 0) {
			return [];
		}
		
		$nodes = [];
		
		// if @var limit hasn't been set, walk through whole tree
		$ansCount = count($this->root->answers);
		if (is_null($limit) || $limit > $ansCount) {
			$limit = $ansCount;
		}
		
		for ($i = $offset; $i < $limit; $i++) {
			$nodes[] = $this->root->answers[$i]->commentId;
			$this->root->answers[$i]->walk($nodes);
		}
		
		return $nodes;
	}
		
	/**
	 *	@param integer
	 *
	 *	@return boolean
	 */
	public function addToRoot($value)
	{
		$this->root->addAnswer($value);
	}
	
	/**
	 *	@param integer
	 *
	 *	@return boolean
	 */
	public function add($key, $value)
	{
		$node = $this->find($key);
		if (is_null($node)) {
			return false;
		}
		
		$node->addAnswer($value);
		return true;
	}
	
	/**
	 *	@param integer
	 *
	 *	@return boolean
	 */
	public function delete($key, $parentId)
	{
		$node = $this->find($parentId);
		if (is_null($node)) {
			return false;
		}
		
		return $node->deleteAnswer($key);
		
	}
	
	public function toArray()
	{
		/*
			[
				[
					'id' => value,
					'answers' => [
									'id' => value,
									'answers' => [...]
								]
				],
				[
					'id' => value,
					'answers' => [...]
				],
				 .
				 .
				 .
				[...]
			]
		*/
		return $this->root->toArray();
	}

	/**
	 *
	 */
	public function decode( \stdClass $treeObject)
	{
		$this->root->decode($treeObject->{'root'});
	}
}