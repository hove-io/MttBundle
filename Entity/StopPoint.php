<?php

namespace CanalTP\MttBundle\Entity;

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
    private $externalId;

    /**
     * @var Object
     */
    private $timetable;

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
     * @var string
     */
    private $external_code;

    /**
     * @var datetime
     */
    protected $pdfGenerationDate;

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
     * @param  string $externalId
     * @return Line
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get external Id
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set timetable
     *
     * @param  string $timetable
     * @return StopPoint
     */
    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;

        return $this;
    }

    /**
     * Get timetable
     *
     * @return Object
     */
    public function getTimetable()
    {
        return $this->timetable;
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
     * Get external_code
     *
     * @return string
     */
    public function getExternalCode()
    {
        return $this->external_code;
    }

    /**
     * Set external_code
     *
     * @param  string    $external_code
     * @return StopPoint
     */
    public function setExternalCode($external_code)
    {
        $this->external_code = $external_code;

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
     * @param  string    $pdfGenerationDate
     * @return StopPoint
     */
    public function setPdfGenerationDate($pdfGenerationDate)
    {
        $this->pdfGenerationDate = $pdfGenerationDate;

        return $this;
    }
    
    public function __clone()
    {
        $this->pdfGenerationDate = null;
    }
}
