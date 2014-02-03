<?php

namespace CanalTP\MethBundle\Entity;

/**
 * StopPoint
 */
class StopPoint
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $navitiaId;

    /**
     * @var Object
     */
    private $line;

    /**
     * @var Object
     */
    private $blocks;

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
     * Set navitiaId
     *
     * @param  string $navitiaId
     * @return Line
     */
    public function setNavitiaId($navitiaId)
    {
        $this->navitiaId = $navitiaId;

        return $this;
    }

    /**
     * Get getNavitiaId
     *
     * @return string
     */
    public function getNavitiaId()
    {
        return $this->navitiaId;
    }

    /**
     * Set line
     *
     * @param integer $line
     *
     * @return StopPoint
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Get line
     *
     * @return string
     */
    public function getLine()
    {
        return $this->line;
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
     * @param  string    $title
     * @return StopPoint
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
