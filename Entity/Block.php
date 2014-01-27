<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Block
 */
class Block
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $typeId;

    /**
     * @var string
     */
    private $domId;

    /**
     * @var string
     */
    private $container;

    /**
     * @var string
     */
    private $title;


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
     * Get typeId
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * Set typeId
     *
     * @param string $typeId
     * @return Block
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    
        return $this;
    }

    /**
     * Set domId
     *
     * @param string $domId
     * @return Block
     */
    public function setDomId($domId)
    {
        $this->domId = $domId;
    
        return $this;
    }

    /**
     * Get domId
     *
     * @return string 
     */
    public function getDomId()
    {
        return $this->domId;
    }

    /**
     * Set container
     *
     * @param string $container
     * @return Block
     */
    public function setContainer($container)
    {
        $this->container = $container;
    
        return $this;
    }

    /**
     * Get container
     *
     * @return string 
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Block
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
}
