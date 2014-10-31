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
    private $locked = false;

    /**
     * @var Object
     */
    private $perimeter;

    /**
     * @var Array
     */
    private $lineConfigs;

    /**
     * @var Array
     */
    private $areasPdf;

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

    public function getPerimeter()
    {
        return $this->perimeter;
    }

    /**
     * Set Object
     *
     * @return Season
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;

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

    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set locked
     *
     * @return Season
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return ($this);
    }

    /**
     * Get areasPdf
     *
     * @return array
     */
    public function getAreasPdf()
    {
        return $this->areasPdf;
    }

    /**
     * Set areasPdf
     *
     * @return Season
     */
    public function setAreasPdf($areasPdf)
    {
        $this->areasPdf = $areasPdf;

        return ($this);
    }

    //
    public function isLocked()
    {
        return $this->getLocked();
    }
}
