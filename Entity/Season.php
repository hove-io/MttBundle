<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Season
 */
class Season
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
    private $network;

    /**
     * @var Object
     */
    private $seasonToClone;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

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
}
