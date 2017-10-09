<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscriber
 *
 * @ORM\Table(name="subscribers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscriberRepository")
 */
class Subscriber
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="category_list", type="string", length=255)
     */
    private $categoryList;
	
	/**
     * @var string
     *
     * @ORM\Column(name="undo_key", type="string", length=255)
     */
    private $undoKey;


    public function __construct()
	{
		$this->undoKey = md5(uniqid());
	}
	
	/**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Subscriber
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return srting 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set categoryList
     *
     * @param string $categoryList
     * @return Subscriber
     */
    public function setCategoryList($categoryList)
    {
        $this->categoryList = $categoryList;

        return $this;
    }

    /**
     * Get categoryList
     *
     * @return string 
     */
    public function getCategoryList()
    {
        return $this->categoryList;
    }

    /**
     * Set undoKey
     *
     * @param string $undoKey
     * @return Subscriber
     */
    public function setUndoKey($undoKey)
    {
        $this->undoKey = $undoKey;

        return $this;
    }

    /**
     * Get undoKey
     *
     * @return string 
     */
    public function getUndoKey()
    {
        return $this->undoKey;
    }
}
