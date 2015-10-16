<?php

namespace CanalTP\MttBundle\Entity;

/**
 * StopTimetable
 */
class StopTimetable extends AbstractEntity
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
     * @var Object
     */
    private $stopPoints;

    /**
     * @var Object
     */
    private $network;

    /**
     * @var string - non persistent
     */
    private $title;

    /**
     * @var string - non persistent
     */
    private $directionCity;

    /**
     * @var Object - non persistent
     */
    private $line_config;

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
     * @param  string    $externalRouteId
     * @return StopTimetable
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
     * Set stopPoints
     *
     * @param  array     $blocks
     * @return StopTimetable
     */
    public function setStopPoints($stopPoints)
    {
        $this->stopPoints = $stopPoints;

        return $this;
    }

    /**
     * Get stopPoints
     *
     * @return array
     */
    public function getStopPoints()
    {
        return $this->stopPoints;
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
     * @param  string $title
     * @return Line
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
    public function getDirectionCity()
    {
        return $this->directionCity;
    }

    /**
     * Set direction city
     *
     * @param  string $directionCity
     * @return Line
     */
    public function setDirectionCity($directionCity)
    {
        $this->directionCity = $directionCity;

        return $this;
    }

     /**
     * Set perimeter
     *
     * @param Object $line
     * @return StopTimetable
     */
    public function setPerimeter($network)
    {
        $this->network = $network;

        return $this;
    }

    /**
     * Get network
     *
     * @return Object
     */
    public function getNetwork()
    {
        return $this->network;
    }

     /**
     * Set lineConfig
     *
     * @param LineConfig $lineConfig
     * @return StopTimetable
     */
    public function setLineConfig($lineConfig)
    {
        $this->line_config = $lineConfig;

        return $this;
    }

    /**
     * Get lineConfig
     *
     * @return Object
     */
    public function getLineConfig()
    {
        return $this->line_config;
    }

    public function isLocked()
    {
        return $this->getLineConfig()->isLocked();
    }
}
