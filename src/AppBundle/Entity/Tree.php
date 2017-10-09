<?php

namespace AppBundle\Entity;

use AppBundle\Library\CommentsTree;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tree
 *
 * @ORM\Table(name="trees")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TreeRepository")
 */
class Tree
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
     * @ORM\Column(name="structure", type="text")
     */
    private $structure;
	
	
	public function __construct()
	{
		$this->structure = json_encode(new CommentsTree);
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
     * Set structure
     *
     * @param string $structure
     * @return Tree
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure
     *
     * @return string 
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
