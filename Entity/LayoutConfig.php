<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LayoutConfig
 */
class LayoutConfig extends AbstractEntity
{
    const NOTES_MODE_AGGREGATED = 0;
    const NOTES_MODE_DISPATCHED = 1;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var integer
     */
    private $calendatStart;

    /**
     * @var integer
     */
    private $calendarEnd;

    /**
     * @var integer
     */
    private $notesMode = self::NOTES_MODE_AGGREGATED;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $lineConfigs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $networks;

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
     * Set label
     *
     * @param string $label
     * @return LayoutConfig
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set calendatStart
     *
     * @param integer $calendatStart
     * @return LayoutConfig
     */
    public function setCalendatStart($calendatStart)
    {
        $this->calendatStart = $calendatStart;

        return $this;
    }

    /**
     * Get calendatStart
     *
     * @return integer
     */
    public function getCalendatStart()
    {
        return $this->calendatStart;
    }

    /**
     * Set calendarEnd
     *
     * @param integer $calendarEnd
     * @return LayoutConfig
     */
    public function setCalendarEnd($calendarEnd)
    {
        $this->calendarEnd = $calendarEnd;

        return $this;
    }

    /**
     * Get calendarEnd
     *
     * @return integer
     */
    public function getCalendarEnd()
    {
        return $this->calendarEnd;
    }

    /**
     * Set notesMode
     *
     * @param integer $notesMode
     * @return LayoutConfig
     */
    public function setNotesMode($notesMode)
    {
        $this->notesMode = $notesMode;

        return $this;
    }

    /**
     * Get notesMode
     *
     * @return integer
     */
    public function getNotesMode()
    {
        return $this->notesMode;
    }

    public function aggregatesNotes()
    {
        return $this->getNotesMode() == self::NOTES_MODE_AGGREGATED;
    }

    public function dispatchesNotes()
    {
        return $this->getNotesMode() == self::NOTES_MODE_DISPATCHED;
    }

    /**
     * Set Networks
     *
     * @return Collections\Collection
     */
    public function getLineConfigs()
    {
        return ($this->lineConfigs);
    }

    /**
     * Set networks
     *
     * @return Layout
     */
    public function setLineConfigs($lineConfigs)
    {
        $this->lineConfigs = $lineConfigs;

        return ($this);
    }

    /**
     * Set Networks
     *
     * @return Collections\Collection
     */
    public function getNetworks()
    {
        return ($this->networks);
    }

    /**
     * Set networks
     *
     * @return Layout
     */
    public function setNetworks($networks)
    {
        $this->networks = $networks;

        return ($this);
    }
}
