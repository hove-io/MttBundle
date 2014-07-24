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
    private $calendarStart;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $layout;


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
     * Set calendarStart
     *
     * @param integer $calendarStart
     * @return LayoutConfig
     */
    public function setCalendarStart($calendarStart)
    {
        $this->calendarStart = $calendarStart;

        return $this;
    }

    /**
     * Get calendarStart
     *
     * @return integer
     */
    public function getCalendarStart()
    {
        return $this->calendarStart;
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
     * @return LayoutConfig
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
     * Set LayoutConfigs
     *
     * @return Network
     */
    public function addNetwork($network)
    {
        $this->networks[] = $network;

        return ($this);
    }

    /**
     * Set networks
     *
     * @return LayoutConfig
     */
    public function setNetworks($networks)
    {
        $this->networks = $networks;

        return ($this);
    }

    /**
     * Set Layout
     *
     * @return LayoutConfig
     */
    public function getLayout()
    {
        return ($this->layout);
    }

    /**
     * Set layout
     *
     * @return LayoutConfig
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return ($this);
    }

    public function __toString()
    {
        return $this->label;
    }
}
