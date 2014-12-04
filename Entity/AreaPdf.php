<?php

namespace CanalTP\MttBundle\Entity;

/**
 * AreaPdf
 */
class AreaPdf extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Object
     */
    private $area;

    /**
     * @var Object
     */
    private $season;

    /**
     * @var \DateTime
     */
    private $generatedAt;

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
     * Set area
     *
     * @param  Object  $area
     * @return AreaPdf
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return Object
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set season
     *
     * @param  Object  $season
     * @return AreaPdf
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return Object
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set generatedAt
     *
     * @param  \DateTime $generatedAt
     * @return AreaPdf
     */
    public function setGeneratedAt($generatedAt)
    {
        $this->generatedAt = $generatedAt;

        return $this;
    }

    /**
     * Get generatedAt
     *
     * @return \DateTime
     */
    public function getGeneratedAt()
    {
        return $this->generatedAt;
    }

    /**
     * Get nbStopPoint in area
     *
     * @return integer
     */
    public function getNbStopPointAggregated()
    {
        return ($this->getArea()->getNbStopPoints);
    }

    public function getPath()
    {
        $path = 'area/';
        $path .= $this->getArea()->getId() . '/';
        $path .= 'seasons/';
        $path .= $this->getSeason()->getId() . '/';
        $path .= 'Secteur.pdf';

        return $path;
    }
}
