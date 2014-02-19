<?php

namespace CanalTP\MethBundle\Entity;

/**
 * DistributionList
 */
class DistributionList
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $includedStops;

    /**
     * @var array
     */
    private $excludedStops;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var Object
     */
    private $timetable;

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
     * Set includedStops
     *
     * @param  array            $includedStops
     * @return DistributionList
     */
    public function setIncludedStops($includedStops)
    {
        $this->includedStops = $includedStops;

        return $this;
    }

    /**
     * Get includedStops
     *
     * @return array
     */
    public function getIncludedStops()
    {
        return $this->includedStops;
    }

    /**
     * Set excludedStops
     *
     * @param  array            $excludedStops
     * @return DistributionList
     */
    public function setExcludedStops($excludedStops)
    {
        $this->excludedStops = $excludedStops;

        return $this;
    }

    /**
     * Get excludedStops
     *
     * @return array
     */
    public function getExcludedStops()
    {
        return $this->excludedStops;
    }

    /**
     * Set timetable
     *
     * @param integer $timetable
     *
     * @return Block
     */
    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;

        return $this;
    }

    /**
     * Get timetable
     *
     * @return string
     */
    public function getTimetable()
    {
        return $this->timetable;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
