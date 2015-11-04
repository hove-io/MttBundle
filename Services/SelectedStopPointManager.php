<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\SerializerInterface;
use CanalTP\MttBundle\Entity\LineTimetable;
use CanalTP\MttBundle\Entity\SelectedStopPoint;

/**
 * Class SelectedStopPointManager.
 */
class SelectedStopPointManager
{
    private $om = null;
    private $repository = null;
    private $serializer = null;
    private $navitia = null;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, Navitia $navitia, SerializerInterface $serializer)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:SelectedStopPoint');
        $this->serializer = $serializer;
        $this->navitia = $navitia;
    }

    /**
     * Preparing a list with available stop points and their selection state.
     *
     * @param mixed      $navitiaStopPoints
     * @param Collection $selectedStopPoints
     */
    private function tagAvailableStopPoints($navitiaStopPoints, Collection $selectedStopPoints)
    {
        $stopPointsIds = $selectedStopPoints->map(
            function ($entity) {
                return $entity->getExternalStopPointId();
            }
        )->toArray();

        $stopPoints = array();
        foreach ($navitiaStopPoints as $stopPoint) {
            $stopPoint->selected = in_array($stopPoint->id, $stopPointsIds);
            $stopPoints[$stopPoint->id] = $stopPoint;
        }

        return $stopPoints;
    }

    /**
     * Preparing available stop points.
     *
     * @param string        $externalCoverageId
     * @param string        $externalRouteId
     * @param LineTimetable $lineTimetable
     * @param mixed         $routes
     */
    public function prepareStopsSelection($externalCoverageId, $externalRouteId, LineTimetable $lineTimetable, $routes)
    {
        $selectedStopPoints = $lineTimetable->getSelectedStopPointsByRoute($externalRouteId);

        $navitiaStopPoints = $this->navitia->getRouteData(
            $externalRouteId,
            $externalCoverageId
        )->stop_points;

        // Reversing selection if there are 2 routes and only one with selected stop points
        $reversed = false;
        if ($selectedStopPoints->isEmpty() && count($routes) == 2) {
            $filteredRoutes = array_filter($routes,
                function ($route) use ($externalRouteId) {
                    return $route->id != $externalRouteId;
                }
            );
            $revExternalRouteId = array_pop($filteredRoutes)->id;

            $reverseStopPoints = $lineTimetable->getSelectedStopPointsByRoute($revExternalRouteId);

            if (!$reverseStopPoints->isEmpty()) {
                $revNavitiaStopPoints = $this->navitia->getRouteData(
                    $revExternalRouteId,
                    $externalCoverageId
                )->stop_points;

                $reverseStopSelection = array_reverse(
                    array_filter(
                        $this->tagAvailableStopPoints($revNavitiaStopPoints, $reverseStopPoints),
                        function ($object) {
                            return $object->selected;
                        }
                    )
                );

                $rank = 1;
                $stopAreaIds = array();
                foreach ($reverseStopSelection as $reverseStopPoint) {
                    $stopAreaId = $reverseStopPoint->stop_area->id;

                    if (in_array($stopAreaId, $stopAreaIds)) {
                        continue;
                    }

                    $stopAreaIds[] = $stopAreaId;

                    $candidateStops = array_filter(
                        $navitiaStopPoints,
                        function ($stopPoint) use ($stopAreaId) {
                            return $stopPoint->stop_area->id == $stopAreaId;
                        }
                    );

                    foreach ($candidateStops as $candidateStop) {
                        $selectedStopPoint = new SelectedStopPoint();
                        $selectedStopPoint->setRank($rank);
                        $selectedStopPoint->setExternalRouteId($externalRouteId);
                        $selectedStopPoint->setExternalStopPointId($candidateStop->id);
                        $selectedStopPoint->setLineTimetable($lineTimetable);

                        $lineTimetable->addSelectedStopPoint($selectedStopPoint);
                        ++$rank;
                    }
                }

                $selectedStopPoints = $lineTimetable->getSelectedStopPointsByRoute($externalRouteId);
                $reversed = true;
            }
        }

        return array($this->tagAvailableStopPoints($navitiaStopPoints, $selectedStopPoints), $reversed);
    }

    /**
     * Updating selected stop points related to a LineTimetable entity.
     *
     * @param mixed         $data
     * @param LineTimetable $lineTimetable
     */
    public function updateStopPointSelection($data, LineTimetable $lineTimetable)
    {
        $data = json_decode($data, true);

        if (empty($data['stopPoints'])) {
            $lineTimetable->clearSelectedStopPoints($data['externalRouteId']);
            $this->om->persist($lineTimetable);
            $this->om->flush();
        } else {
            foreach ($data['stopPoints'] as $index => $stopPoint) {
                $data['stopPoints'][$index] = $this->serializer->deserialize(json_encode($stopPoint), 'CanalTP\MttBundle\Entity\SelectedStopPoint', 'json');
            }

            $newStopPointsIds = array_map(
                function ($entity) {
                    return $entity->getExternalStopPointId();
                },
                $data['stopPoints']
            );

            $selectedStopPoints = $lineTimetable->getSelectedStopPointsByRoute($data['externalRouteId']);

            // Updating existing SelectedStopPoints
            foreach ($selectedStopPoints as $selectedStopPoint) {
                $stopRank = array_search($selectedStopPoint->getExternalStopPointId(), $newStopPointsIds);
                if ($stopRank === false) {
                    $this->om->remove($selectedStopPoint);
                } elseif ($selectedStopPoint->getRank() != $stopRank + 1) {
                    $selectedStopPoint->setRank($stopRank + 1);
                    $this->om->persist($selectedStopPoint);
                }
            }

            // Creating new SelectedStopPoints
            $currentStopPointsIds = $selectedStopPoints->map(
                function ($entity) {
                    return $entity->getExternalStopPointId();
                }
            )->toArray();

            foreach ($data['stopPoints'] as $newStopPoint) {
                if (!in_array($newStopPoint->getExternalStopPointId(), $currentStopPointsIds)) {
                    $newStopPoint->setLineTimetable($lineTimetable);
                    $this->om->persist($newStopPoint);
                }
            }

            $this->om->flush();
        }
    }
}
