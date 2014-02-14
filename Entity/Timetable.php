<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CanalTP\MethBundle\Entity\AbstractEntity;

/**
 * Timetable
 */
class Timetable extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $externalRouteId;
    
    /**
     * @var Object
     */
    private $blocks;
    
    /**
     * @var string - non persistent
     */
    private $title;
    
    /**
     * @var Object - non persistent
     */
    private $line;

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
     * Set externalRouteId
     *
     * @param string $externalRouteId
     * @return Timetable
     */
    public function setExternalRouteId($externalRouteId)
    {
        $this->externalRouteId = $externalRouteId;
    
        return $this;
    }

    /**
     * Get externalRouteId
     *
     * @return string 
     */
    public function getExternalRouteId()
    {
        return $this->externalRouteId;
    }

    /**
     * Set blocks
     *
     * @param  array $blocks
     * @return Line
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Get blocks
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
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

    /**
     * Set title
     *
     * @param  string $networkId
     * @return Line
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
    
     /**
     * Set line
     *
     * @param Object $line
     * @return Route
     */
    public function setLine($line)
    {
        $this->line = $line;
    
        return $this;
    }

    /**
     * Get line
     *
     * @return Object  
     */
    public function getLine()
    {
        return $this->line;
    }
}
