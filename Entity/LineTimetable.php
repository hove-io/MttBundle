<?php

namespace CanalTP\MttBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\Common\Collections\Collection;
use \Doctrine\Common\Collections\Criteria;

/**
 * Class LineTimetable
 */
class LineTimetable extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var LineConfig
     */
    private $lineConfig;

    /**
     * @var Collection selectedStopPoints
     */
    private $selectedStopPoints;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->selectedStopPoints = new ArrayCollection();
    }

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
     * Set lineConfig
     *
     * @param LineConfig $lineConfig
     * @return LineTimetable
     */
    public function setLineConfig(LineConfig $lineConfig = null)
    {
        $this->lineConfig = $lineConfig;

        return $this;
    }

    /**
     * Get lineConfig
     *
     * @return LineConfig
     */
    public function getLineConfig()
    {
        return $this->lineConfig;
    }

    /**
     * Set selectedStopPoints
     *
     * @param Collection $selectedStopPoints
     * @return LineTimetable
     */
    public function setSelectedStopPoints(Collection $selectedStopPoints)
    {
        $this->selectedStopPoints = $selectedStopPoints;

        return $this;
    }

    /**
     * Get selectedStopPoints
     *
     * @return Collection
     */
    public function getSelectedStopPoints()
    {
        return $this->selectedStopPoints;
    }

    /**
     * Add selectedStopPoint
     *
     * @param SelectedStopPoint $selectedStopPoint
     * @return LineTimetable
     */
    public function addSelectedStopPoint(SelectedStopPoint $selectedStopPoint)
    {
        $this->selectedStopPoints->add($selectedStopPoint);

        return $this;
    }

    /**
     * Remove selectedStopPoint
     *
     * @param SelectedStopPoint $selectedStopPoint
     * @return LineTimetable
     */
    public function removeSelectedStopPoint(SelectedStopPoint $selectedStopPoint)
    {
        $this->selectedStopPoints->removeElement($selectedStopPoint);

        return $this;
    }

    /**
     * Clear selectedStopPoints
     *
     * @param $externalRouteId
     * @return LineTimetable
     */
    public function clearSelectedStopPoints($externalRouteId = null)
    {
        if (!$externalRouteId)
            $this->selectedStopPoints->clear();
        else
        {
            foreach ($this->getSelectedStopPointsByRoute($externalRouteId) as $selectedStopPoint)
                $this->selectedStopPoints->removeElement($selectedStopPoint);
        }

        return $this;
    }

    /**
     * Get selectedStopPointsByRoute
     *
     * @param string $externalRouteId
     * @return Collection
     */
    public function getSelectedStopPointsByRoute($externalRouteId)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('externalRouteId', $externalRouteId))
            ->orderBy(array('rank' => Criteria::ASC))
        ;

        return $this->selectedStopPoints->matching($criteria);
    }
}
