<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CanalTP\MethBundle\Entity\AbstractEntity;

/**
 * Route
 */
class Route extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $externalId;
    
    /**
     * @var Object
     */
    private $blocks;

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
     * Set externalId
     *
     * @param string $externalId
     * @return Route
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    
        return $this;
    }

    /**
     * Get externalId
     *
     * @return string 
     */
    public function getExternalId()
    {
        return $this->externalId;
    }
}
