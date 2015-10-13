<?php

namespace CanalTP\MttBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\Common\Collections\Collection;

/**
 * LineConfig
 */
class LineConfig extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $externalLineId;

    /**
     * @var LayoutConfig
     */
    private $layoutConfig;

    /**
     * @var Season
     */
    private $season;

    /**
     * @var Collection
     */
    private $timetables;

    /**
     * @var Collection
     */
    private $lineTimetables;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->timetables = new ArrayCollection();
        $this->lineTimetables = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @return Object
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set externalLineId
     *
     * @param string $externalLineId
     * @return LineConfig
     */
    public function setExternalLineId($externalLineId)
    {
        $this->externalLineId = $externalLineId;

        return $this;
    }

    /**
     * Get externalLineId
     *
     * @return string
     */
    public function getExternalLineId()
    {
        return $this->externalLineId;
    }

    /**
     * Set LayoutConfig
     *
     * @param LayoutConfig $layoutConfig
     * @return LineConfig
     */
    public function setLayoutConfig(LayoutConfig $layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;

        return $this;
    }

    /**
     * Get getLayoutConfig
     *
     * @return LayoutConfig
     */
    public function getLayoutConfig()
    {
        return $this->layoutConfig;
    }

    /**
     * Set blocks
     *
     * @param array $blocks
     * @return LineConfig
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
     * @param  array $stopPoints
     * @return Line
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
     * Get season
     *
     * @return object
     */
    public function getSeason()
    {
        return ($this->season);
    }

    /**
     * Set season
     *
     * @param  string $season
     * @return Season
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Set timetables
     *
     * @param  array      $timetables
     * @return LineConfig
     */
    public function setTimetables($timetables)
    {
        $this->timetables = $timetables;

        return $this;
    }

    /**
     * Get timetables
     *
     * @return array
     */
    public function getTimetables()
    {
        return $this->timetables;
    }

    public function isLocked()
    {
        return $this->getSeason() != null && $this->getSeason()->isLocked();
    }
}
