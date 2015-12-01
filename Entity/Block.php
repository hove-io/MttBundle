<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Block
 */
class Block extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $typeId;

    /**
     * @var string
     */
    private $domId;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Object
     */
    private $stopTimetable;

    /**
     * @var Object
     */
    private $frequencies;

    public function __construct()
    {
        $this->frequencies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get typeId
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set typeId
     *
     * @param  string $typeId
     * @return Block
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Set domId
     *
     * @param  string $domId
     * @return Block
     */
    public function setDomId($domId)
    {
        $this->domId = $domId;

        return $this;
    }

    /**
     * Get domId
     *
     * @return string
     */
    public function getDomId()
    {
        return $this->domId;
    }

    /**
     * Set content
     *
     * @param  string $content
     * @return Block
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return Block
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set stopTimetable
     *
     * @param integer $stopTimetable
     *
     * @return Block
     */
    public function setStopTimetable($stopTimetable)
    {
        $this->stopTimetable = $stopTimetable;

        return $this;
    }

    /**
     * Get stopTimetable
     *
     * @return string
     */
    public function getStopTimetable()
    {
        return $this->stopTimetable;
    }

    /**
     * Set frequencies
     *
     * @param array $frequencies
     *
     * @return Block
     */
    public function setFrequencies($frequencies)
    {
        $this->frequencies = $frequencies;
        foreach ($this->frequencies as $frequency) {
            $frequency->setBlock($this);
        }

        return $this;
    }

    /**
     * Get frequencies
     *
     * @return array
     */
    public function getFrequencies()
    {
        return $this->frequencies;
    }

    /**
     * Check if it is Img block
     *
     * @return boolean
     */
    public function isImg()
    {
        return ($this->getTypeId() == BlockRepository::IMG_TYPE);
    }

    /**
     * Check if it is Text block
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this->getTypeId() == BlockRepository::TEXT_TYPE);
    }

    /**
     * Check if it is Calendar block
     *
     * @return boolean
     */
    public function isCalendar()
    {
        return ($this->getTypeId() == BlockRepository::CALENDAR_TYPE);
    }

    /**
     * Getting the Timetable
     */
    public function getTimetable()
    {
        return $this->stopTimetable;
    }

    /**
     * Checking the timetable is locked or not
     */
    public function isLocked()
    {
        $timetable = $this->getTimetable();

        return (!empty($timetable) && ($timetable->isLocked()));
    }
}
