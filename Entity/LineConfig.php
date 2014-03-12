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
    private $layout;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $seasons;

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

    /**
     * Get getExternalId
     *
     * @return string
     */
    public function getExternalLineId()
    {
        return $this->externalLineId;
    }

    /**
     * Set layout
     *
     * @param  string $layout
     * @return Line
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get getLayout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
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
}
