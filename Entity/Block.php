<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
    private $type;

    /**
     * @var string
     */
    private $domId;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var string
     */
    private $externalLineId;

    /**
     * @var string
     */
    private $externalRouteId;

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
    private $lineTimetable;

    /**
     * @var Object
     */
    private $frequencies;

    public function __construct()
    {
        $this->frequencies = new ArrayCollection();
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param  string $type
     * @return Block
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * Set rank
     *
     * @param  integer $rank
     * @return Block
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Incrementing the block's rank.
     *
     * @param integer $nb
     */
    public function incRank($nb = 1)
    {
        if ($nb >= 1) {
            $this->rank += $nb;
        } else {
            $this->rank++;
        }
    }

    /**
     * Decrementing the block's rank.
     *
     * @param integer $nb
     */
    public function decRank($nb = 1)
    {
        if ($nb >= 1 && ($this->rank - $nb) > 0) {
            $this->rank -= $nb;
        }
    }

    /**
     * Set externalLineId
     *
     * @param  string $externalLineId
     * @return Block
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
     * Set externalRouteId
     *
     * @param  string $externalRouteId
     * @return Block
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
     * Get lineTimetable
     *
     * @return LineTimetable
     */
    public function getLineTimetable()
    {
        return $this->lineTimetable;
    }

    /**
     * Set lineTimetable
     *
     * @param LineTimetable $lineTimetable
     *
     * @return Block
     */
    public function setLineTimetable(LineTimetable $lineTimetable)
    {
        $this->lineTimetable = $lineTimetable;

        return $this;
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
     * Set timetable
     *
     * @param Timetable
     */
    public function setTimetable(Timetable $timetable)
    {
        if ($timetable instanceof LineTimetable) {
            $this->lineTimetable = $timetable;
        } elseif ($timetable instanceof StopTimetable) {
            $this->stopTimetable = $timetable;
        } else {
            throw new \Exception('Timetable object is not StopTimetable nor LineTimetable instance');
        }

        return $this;
    }

    /**
     * Get timetable
     *
     * @return LineTimetable or StopTimetable
     */
    public function getTimetable()
    {
        if ($this->stopTimetable !== null) {
            return $this->stopTimetable;
        }

        if ($this->lineTimetable !== null) {
            return $this->lineTimetable;
        }

        return null;
    }

    /**
     * Set frequencies
     *
     * @param Collection $frequencies
     * @return Block
     */
    public function setFrequencies(Collection $frequencies)
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
     * @return Collection
     */
    public function getFrequencies()
    {
        return $this->frequencies;
    }

    /**
     * Adding a frequency
     *
     * @param Frequency $frequency
     * @return Block
     */
    public function addFrequency(Frequency $frequency)
    {
        $frequency->setBlock($this);
        $this->frequencies->add($frequency);

        return $this;
    }

    /**
     * Check if it is Img block
     *
     * @return boolean
     */
    public function isImg()
    {
        return ($this->getType() == BlockRepository::IMG_TYPE);
    }

    /**
     * Check if it is Text block
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this->getType() == BlockRepository::TEXT_TYPE);
    }

    /**
     * Check if it is Calendar block
     *
     * @return boolean
     */
    public function isCalendar()
    {
        return ($this->getType() == BlockRepository::CALENDAR_TYPE);
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
