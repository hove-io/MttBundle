<?php

namespace CanalTP\MttBundle\Entity;

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
     * @var string
     */
    private $externalRouteId;

    /**
     * @var Object
     */
    private $perimeter;

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
     * Set externalRouteId
     *
     * @param  string    $externalRouteId
     * @return Timetable
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
     * Set network
     *
     * @param integer $network
     *
     * @return DistributionList
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;

        return $this;
    }

    /**
     * Get network
     *
     * @return string
     */
    public function getPerimeter()
    {
        return $this->perimeter;
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
