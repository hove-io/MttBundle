<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Season
 */
class Season extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Object
     */
    private $startDate;

    /**
     * @var Object
     */
    private $endDate;

    /**
     * @var Object
     */
    private $published = false;

    /**
     * @var Object
     */
    private $network;

    /**
     * @var Array
     */
    private $lineConfigs;

    /**
     * @var Object
     */
    private $seasonToClone;

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
     * @return object
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return ($this);
    }

    /**
     * Get seasons
     *
     * @return string
     */
    public function getSeasonToClone()
    {
        return $this->seasonToClone;
    }

    /**
     * Set seasons
     *
     * @return object
     */
    public function setSeasonToClone($seasonToClone)
    {
        $this->seasonToClone = $seasonToClone;

        return ($this);
    }

    /**
     * Get Object
     *
     * @return startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set startDate
     *
     * @return Season
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return ($this);
    }

    /**
     * Get Object
     *
     * @return endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set Object
     *
     * @return Season
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return ($this);
    }

    /**
     * Get Object
     *
     * @return Network
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Set Object
     *
     * @return Season
     */
    public function setNetwork($network)
    {
        $this->network = $network;

        return ($this);
    }

    /**
     * Set lineConfigs
     *
     * @param  array  $lineConfigs
     * @return Season
     */
    public function setLineConfigs($lineConfigs)
    {
        $this->lineConfigs = $lineConfigs;

        return $this;
    }

    /**
     * Get lineConfigs
     *
     * @return array
     */
    public function getLineConfigs()
    {
        return $this->lineConfigs;
    }

    /**
     * Get published
     *
     * @return Network
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set published
     *
     * @return Season
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return ($this);
    }
}
