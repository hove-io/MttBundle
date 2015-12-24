<?php

namespace CanalTP\MttBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\Common\Collections\Collection;
use \Doctrine\Common\Collections\Criteria;

/**
 * Class LineTimetable
 */
class LineTimetable extends Timetable
{
    /**
     * @var integer
     */
    private $id;

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
        $this->blocks = new ArrayCollection();
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
        if (!$externalRouteId) {
            $this->selectedStopPoints->clear();
        } else {
            foreach ($this->getSelectedStopPointsByRoute($externalRouteId) as $selectedStopPoint) {
                $this->selectedStopPoints->removeElement($selectedStopPoint);
            }
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

    /**
     * Has templateOfType
     *
     * @param $type
     * @return boolean
     */
    public function hasTemplateOfType($type)
    {
        $template = $this->lineConfig->getLayoutConfig()->getLayout()->getTemplate($type);

        return ($template !== null);
    }
}
