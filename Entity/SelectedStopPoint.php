<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Class SelectedStopPoint
 */
class SelectedStopPoint extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string externalRouteId
     */
    private $externalRouteId;

    /**
     * @var string externalStopPointId
     */
    private $externalStopPointId;

    /**
     * @var integer rank
     */
    private $rank;

    /**
     * @var LineTimetable $lineTimetable
     */
    private $lineTimetable;

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
     * Set externalRouteId
     *
     * @param string $externalRouteId
     * @return SelectedStopPoint
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
     * Set externalStopPointId
     *
     * @param string $externalStopPointId
     * @return SelectedStopPoint
     */
    public function setExternalStopPointId($externalStopPointId)
    {
        $this->externalStopPointId = $externalStopPointId;

        return $this;
    }

    /**
     * Get externalStopPointId
     *
     * @return string
     */
    public function getExternalStopPointId()
    {
        return $this->externalStopPointId;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return SelectedStopPoint
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
     * Set lineTimetable
     *
     * @param LineTimetable $lineTimetable
     * @return SelectedStopPoint
     */
    public function setLineTimetable(LineTimetable $lineTimetable)
    {
        $this->lineTimetable = $lineTimetable;

        return $this;
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
}
