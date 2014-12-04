<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Area
 */
class Area extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var Object
     */
    private $perimeter;

    /**
     * @var array
     */
    private $stopPoints;

    /**
     * @var Object
     */
    private $areasPdf;

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
     * @return Area
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
     * Set network
     *
     * @param  Object $network
     * @return Area
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;

        return $this;
    }

    /**
     * Get network
     *
     * @return Object
     */
    public function getPerimeter()
    {
        return $this->perimeter;
    }

    public function setStopPoints($stopPoints)
    {
        $this->stopPoints = $stopPoints;

        return $this;
    }

    /**
     * Get stopPoints
     *
     * @return Object
     */
    public function getStopPoints()
    {
        return $this->stopPoints;
    }

    public function hasStopPoints()
    {
        return (count($this->stopPoints) > 0);
    }

    public function getStopPointsOrderByExternalRouteIdAndExternalLineId()
    {
        $result = array();

        foreach ($this->stopPoints as $stopPointJson) {
            $stopPoint = json_decode($stopPointJson);
            $result[$stopPoint->routeId][$stopPoint->lineId][] = $stopPoint->stopPointId;
        }

        return $result;
    }

    /**
     * Set areasPdf
     *
     * @param  Object $areasPdf
     * @return Area
     */
    public function setAreasPdf($areasPdf)
    {
        $this->areasPdf = $areasPdf;

        return $this;
    }

    /**
     * Get areasPdf
     *
     * @return Object
     */
    public function getAreasPdf()
    {
        return $this->areasPdf;
    }

    public function getNbStopPoints()
    {
        return count($this->stopPoints);
    }
}
