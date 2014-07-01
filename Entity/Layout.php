<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Layout
 */
class Layout extends AbstractEntity
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
     * @var string
     */
    private $twig;

    /**
     * @var string
     */
    private $preview;

    /**
     * @var integer
     * default value 1
     */
    private $cssVersion = 1;

    /**
     * @var string
     */
    private $orientation;

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
    private $networks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $lineConfigs;

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
     * @param  string $label
     * @return Layout
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
     * Set twig
     *
     * @param  string $twig
     * @return Layout
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * Get twig
     *
     * @return string
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * Set preview
     *
     * @param  string $preview
     * @return Layout
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set orientation
     *
     * @param  string $orientation
     * @return Layout
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set cssVersion
     *
     * @param  integer $cssVersion
     * @return Layout
     */
    public function setCssVersion($cssVersion)
    {
        $this->cssVersion = $cssVersion;

        return $this;
    }

    /**
     * Get cssVersion
     *
     * @return integer
     */
    public function getCssVersion()
    {
        return $this->cssVersion;
    }

    /**
     * Set calendarStart
     *
     * @param  integer $calendarStart
     * @return Layout
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
     * @param  integer $calendarEnd
     * @return Layout
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
     * @param  integer $notesMode
     * @return Layout
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

    /**
     * Set Networks
     *
     * @return Collections\Collection
     */
    public function getLineConfigs()
    {
        return ($this->lineConfigs);
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
     * Set networks
     *
     * @return Layout
     */
    public function setLineConfigs($lineConfigs)
    {
        $this->lineConfigs = $lineConfigs;

        return ($this);
    }

    public function __toString()
    {
        return $this->label;
    }
}
