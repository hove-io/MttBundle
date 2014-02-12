<?php

namespace CanalTP\MethBundle\Entity;

use CanalTP\MethBundle\Entity\AbstractEntity;

/**
 * StopPoint
 */
class StopPoint extends AbstractEntity
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
     * @var datetime
     */
    protected $pdfGenerationDate;
    
    /**
     * @var datetime
     */
    protected $lastModified;

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

    /**
     * Get pdfGenerationDate
     *
     * @return string
     */
    public function getPdfGenerationDate()
    {
        return ($this->pdfGenerationDate);
    }

    /**
     * Set pdfGenerationDate
     *
     * @param  string $pdfGenerationDate
     * @return StopPoint
     */
    public function setPdfGenerationDate($pdfGenerationDate)
    {
        $this->pdfGenerationDate = $pdfGenerationDate;

        return $this;
    }
    
    /**
     * Get lastModified
     *
     * @return string
     */
    public function getLastModified()
    {
        return ($this->lastModified);
    }

    /**
     * Set lastModified
     *
     * @param  string $lastModified
     * @return StopPoint
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }
}
