<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resets (reset settings)
 *
 * @ORM\Table(name="resets")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResetsRepository")
 */
class Resets
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="reset_key", type="string", length=255, unique=true)
     */
    private $resetKey;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;
	
	/**
     * @var string
     *
     * @ORM\Column(name="created", type="integer")
     */
    private $created;

	public function __construct()
	{
		$this->resetKey = md5(uniqid());
		$this->created = time();
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
     * Set userId
     *
     * @param integer $userId
     * @return Resets
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set resetKey
     *
     * @param string $resetKey
     * @return Resets
     */
    public function setResetKey($resetKey)
    {
        $this->resetKey = $resetKey;

        return $this;
    }

    /**
     * Get resetKey
     *
     * @return string 
     */
    public function getResetKey()
    {
        return $this->resetKey;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Resets
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }
}
