<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Line
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
     * @var string
     */
    private $layoutConfig;

    /**
     * @var string
     */
    private $twigPath;

    /**
     * @var Array
     */
    private $blocks;

    /**
     * @var Array
     */
    private $stopPoints;

    /**
     * @var Object
     */
    private $season;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $timetables;

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
     * @param  string $externalLineId
     * @return Line
     */
    public function setExternalLineId($externalLineId)
    {
        $this->externalLineId = $externalLineId;

        return $this;
    }

    public function getExternalLineId()
    {
        return $this->externalLineId;
    }

    /**
     * Set LayoutConfig
     *
     * @param  string $layoutConfig
     * @return Line
     */
    public function setLayoutConfig($layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;

        return $this;
    }

    /**
     * Get getLayoutConfig
     *
     * @return string
     */
    public function getLayoutConfig()
    {
        return $this->layoutConfig;
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
     * Get twigPath
     *
     * @return string
     */
    public function getTwigPath()
    {
        return ($this->twigPath);
    }

    /**
     * Set twigPath
     *
     * @param  string $twigPath
     * @return Line
     */
    public function setTwigPath($twigPath)
    {
        $this->twigPath = $twigPath;

        return $this;
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
