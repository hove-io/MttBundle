<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Network
 */
class Network
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $coverageId;

    /**
     * @var string
     */
    private $nameId;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;


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
     * Set nameId
     *
     * @param string $nameId
     * @return Network
     */
    public function setNameId($nameId)
    {
        $this->nameId = $nameId;
    
        return $this;
    }

    /**
     * Get nameId
     *
     * @return string 
     */
    public function getNameId()
    {
        return $this->nameId;
    }

    /**
     * Set coverageId
     *
     * @param string $coverageId
     * @return Network
     */
    public function setCoverageId($coverageId)
    {
        $this->coverageId = $coverageId;
    
        return $this;
    }

    /**
     * Get coverageId
     *
     * @return string 
     */
    public function getCoverageId()
    {
        return $this->coverageId;
    }
}
