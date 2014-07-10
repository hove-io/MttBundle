<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\StopPoint;

class StopPointManager
{
    private $stopPoint = null;
    private $navitia = null;
    private $repository = null;
    private $om = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia)
    {
        $this->stopPoint = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->repository = $om->getRepository('CanalTPMttBundle:StopPoint');
        $this->om = $om;
    }

    private function initTitle($externalCoverageId)
    {
        $this->stopPoint->setTitle(
            $this->navitia->getStopPointTitle(
                $externalCoverageId,
                $this->stopPoint->getExternalId()
            )
        );
    }

    private function initStopPointCode($externalCoverageId)
    {
        $externalCode = $this->navitia->getStopPointExternalCode(
            $externalCoverageId,
            $this->stopPoint->getExternalId()
        );
        $this->stopPoint->setExternalCode($externalCode);
    }

    private function initStopPointPois($externalCoverageId)
    {
        $distance = 400;
        $pois = $this->navitia->getStopPointPois(
            $externalCoverageId,
            $this->stopPoint->getExternalId(),
            $distance
        );

        $this->stopPoint->setPoiDistance($distance);
        if (isset($pois->pagination) && $pois->pagination->total_result) {
            $this->stopPoint->setPois($pois->places_nearby);
        }
    }

    // TODO: mutualize with timetable manager?
    private function initBlocks()
    {
        $blocks = array();
        $stopPointBlocks = $this->repository->getBlocks($this->stopPoint, $this->timetable);

        if (!empty($stopPointBlocks)) {
            foreach ($stopPointBlocks as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            $this->stopPoint->setBlocks($blocks);
        }
    }

    /**
     * Return StopPoint with data from navitia
     *
     * @param  String    $externalStopPointId
     * @param  Line      $line                Line Entity
     * @return stopPoint
     */
    public function getStopPoint($externalStopPointId, $timetable, $externalCoverageId)
    {
        $this->stopPoint = $this->repository->findOneBy(array(
            'externalId' => $externalStopPointId,
            'timetable' => $timetable->getId()
        ));
        $this->timetable = $timetable;
        if (!empty($this->stopPoint)) {
            $this->initBlocks();
        } else {
            $this->stopPoint = new StopPoint();

            $this->stopPoint->setExternalId($externalStopPointId);
            $this->stopPoint->setTimetable($timetable);
        }
        $this->initTitle($externalCoverageId);
        $this->initStopPointCode($externalCoverageId);
        $this->initStopPointPois($externalCoverageId);

        return $this->stopPoint;
    }

    public function getPrevNextStopPoints($network, $externalRouteId, $externalStopPointId)
    {
        $result = $this->navitia->getRouteStopPoints(
            $network,
            $externalRouteId,
            $externalStopPointId
        );
        $prevNext = array();
        foreach ($result->route_schedules[0]->table->rows as $index => $stopPointData) {
            if ($stopPointData->stop_point->id == $externalStopPointId) {
                if (isset($result->route_schedules[0]->table->rows[$index-1])) {
                    $prevNext['prev'] = $result->route_schedules[0]->table->rows[$index-1]->stop_point->id;
                }
                if (isset($result->route_schedules[0]->table->rows[$index+1])) {
                    $prevNext['next'] = $result->route_schedules[0]->table->rows[$index+1]->stop_point->id;
                }
            }
        }

        return $prevNext;
    }

    /**
     * Return StopPoints list with Data from navitia
     *
     * @param array $stopPointNavitiaIds Array of Stoppoints NavitiaId
     *
     * @return array
     */
    public function enhanceStopPoints($stopPoints, $timetable)
    {
        $externalStopPointIds = array();
        $stopPointsIndexed = array();
        // extract externalStopPointIds to prepare SQL WHERE IN
        foreach ($stopPoints as $stopPoint_data) {
            $externalStopPointIds[] = $stopPoint_data->stop_point->id;
            // and index by navitia Id to make it easy to find an item inside
            $stopPointsIndexed[$stopPoint_data->stop_point->id] = $stopPoint_data;
        }
        $query = $this->om
            ->createQueryBuilder()
            ->addSelect('stopPoint')
            ->where("stopPoint.externalId IN(:externalStopPointIds)")
            ->from('CanalTPMttBundle:StopPoint', 'stopPoint')
            ->setParameter('externalStopPointIds', array_values($externalStopPointIds))
            ->andWhere("stopPoint.timetable = :timetableId")
            ->setParameter('timetableId', $timetable->getId())
            ->getQuery();
        $db_stop_points = $query->getResult();
        // add pdf generation date and Hash to stop points
        foreach ($db_stop_points as $db_stop_point) {
            if (isset($stopPointsIndexed[$db_stop_point->getExternalId()])) {
                $stopPointsIndexed[$db_stop_point->getExternalId()]->stop_point->pdfGenerationDate = $db_stop_point->getPdfGenerationDate();
                $stopPointsIndexed[$db_stop_point->getExternalId()]->stop_point->pdfHash = $db_stop_point->getPdfHash();
            }
        }

        return $stopPointsIndexed;
    }

    /**
     * Return StopPoint
     *
     * @param  Object $block
     * @param  Object $destTimetable
     * @param  Object $destStopPoint
     * @return line
     */
    public function copy($stopPoint, $destTimetable)
    {
        $stopPointCloned = clone $stopPoint;
        $stopPointCloned->setTimetable($destTimetable);

        $this->om->persist($stopPointCloned);

        return $stopPointCloned;
    }
}
