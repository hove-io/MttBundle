<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LineTimecard as LineTimecard;
use CanalTP\MttBundle\Entity\LineConfig as LineConfig;
use CanalTP\SamEcoreApplicationManagerBundle\Exception;


/**
 * Class LineTimecardManager
 * @package CanalTP\MttBundle\Services
 */
class LineTimecardManager
{
    private $om = null;

    /** @var \CanalTP\MttBundle\Services\PerimeterManager */
    private $perimeterManager = null;

    /** @var \CanalTP\MttBundle\Entity\LineTimecard */
    private $lineTimecard = null;

    /** @var \CanalTP\MttBundle\Services\Navitia */
    private $navitiaManager = null;

    /** @var \CanalTP\MttBundle\Services\TimecardManager */
    private $timecardManager =  null;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param $perimeterManager
     * @param $navitiaManager
     * @param $timecardManager
     */
    public function __construct(ObjectManager $om, $perimeterManager, $navitiaManager, $timecardManager)
    {
        $this->om = $om;
        $this->perimeterManager = $perimeterManager;
        $this->navitiaManager = $navitiaManager;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LineTimecard');
        $this->timecardManager = $timecardManager;

    }

    /**
     * Create LineTimecard if not exist.
     *
     * @param $lineId
     * @param $perimeter
     * @param $lineConfig
     * @return LineTimecard
     */
    public function createLineTimecardIfNotExist($lineId, $perimeter, LineConfig $lineConfig) {

        $lineTimecard = $this->om->getRepository('CanalTPMttBundle:LineTimecard')->findOneBy(
            array(
                'line_id' => $lineId,
                'perimeter' => $perimeter
            )
        );

        if (empty($lineTimecard)) {
            $lineTimecard = new LineTimecard();
            $lineTimecard->setLineId($lineId)->setPerimeter($perimeter)->setLineConfig($lineConfig);
            $this->om->persist($lineTimecard);
            $this->om->flush($lineTimecard);
        }

        return $lineTimecard;
    }

    /**
     * Get LineTimecard by line and network id
     *
     * @param $lineId
     * @param $perimeter
     * @return LineTimecard
     */
    public function getLineTimecard($lineId, $perimeter)
    {

        $this->lineTimecard = $this->om->getRepository('CanalTPMttBundle:LineTimecard')->findOneBy(
            array(
                'line_id' => $lineId,
                'perimeter' => $perimeter
            )
        );

        $this->initBlocks();

        return $this->lineTimecard;
    }

    /**
     * Get LineTimecard by id
     *
     * @param $objectId
     * @param null $externalCoverageId
     * @return null|object
     */
    public function getById($objectId, $externalCoverageId = null)
    {
        $this->lineTimecard = $this->repository->find($objectId);

        $this->initBlocks();

        return $this->lineTimecard;

    }

    /**
     * Get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        $lineTimecardBlocks = $this->repository->findBlocksByLineTimecardIdOnly($this->lineTimecard->getId());

        if (count($lineTimecardBlocks) > 0) {
            $blocks = array();

            foreach ($lineTimecardBlocks as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            if (count($blocks) > 0) {
                $this->lineTimecard->setBlocks($blocks);
            }
        }
    }

    /**
     * Return all calendars
     * @param LineTimecard $lineTimecard
     * @return array
     */
    public function getAllCalendars(LineTimecard $lineTimecard, $options = null )
    {
        $params = array(
            'maxColForHours' => 24,
            'minHour' => 060000,
            'maxHour' => 210000
        );

        //$options = array_merge($params, $options);

        // Get Routes
        $routes = $this->navitiaManager->getLineRoutes(
            $lineTimecard->getPerimeter()->getExternalCoverageId(),
            $lineTimecard->getPerimeter()->getExternalNetworkId(),
            $lineTimecard->getLineId()
        );

        // Get Timecards
        $timecards = $this->timecardManager->findTimecardListByCompositeKey(
            $lineTimecard->getLineId(),
            $lineTimecard->getLineConfig()->getSeason()->getId(),
            $lineTimecard->getPerimeter()->getExternalNetworkId()
        );

        // Get all calendars for line
        $calendars = $this->navitiaManager->getAllCalendarsForLine(
            $lineTimecard->getPerimeter()->getExternalCoverageId(),
            $lineTimecard->getLineId()
        );

        // Get  blocks of lineTimecard
        $blocks = $lineTimecard->getBlocks();

        foreach ($routes as $route) {
            $direction = $route->direction->name;
            $stopPoints = array();

            foreach($timecards as $timecard) {
                if ($timecard->getRouteId() == $route->id) {
                    $stopPoints = $timecard->getStopPoints();
                    break;
                }
            }

            if (count($stopPoints) > 0) {

                $lineTpl = 0;

                $stopPointsByRoute = $this->navitiaManager->getStopPointsByRoute(
                    $lineTimecard->getPerimeter()->getExternalCoverageId(),
                    $lineTimecard->getPerimeter()->getExternalNetworkId(),
                    $route->id
                );


                foreach($stopPoints as $stopPoint) {
                    $stopPoint = json_decode($stopPoint);

                    // Search stopPoint object by id into $stopPointsByRoute->stop_points
                    $stopPointSelected = array_values(
                        array_filter(
                            $stopPointsByRoute->stop_points,
                            function($object) use ($stopPoint) {
                                return ($object->id == $stopPoint->stopPointId);
                            }
                        )
                    )[0];

                    if (is_null($stopPointSelected)) {
                        throw new \Exception('Duplicate or non-existent stopPoint id');
                    }

                    // Get all calendars for each stoppoint referenced in lineTimecard's blocks
                    foreach($blocks as $block) {
                        if ($block->getTypeId() == 'calendar' && !is_null($block->getContent())) {
                            $stopSchedules = $this->navitiaManager->getCalendarStopSchedulesByRoute(
                                $lineTimecard->getPerimeter()->getExternalCoverageId(),
                                $route->id,
                                $stopPointSelected->id,
                                $block->getContent()
                            );

                            $tResult[$route->id][$stopPoint->stopPointId]['calendars'][$block->getContent()] = null;
                            $ptResult = &$tResult[$route->id][$stopPoint->stopPointId]['calendars'][$block->getContent()];
                            $lineTpl = 0;
                            $currentCol = 1;
                            foreach($stopSchedules->stop_schedules->date_times as $key => $date) {
                                if($currentCol <= $params['maxColForHours']) {
                                    if ((int)$date->date_time >= $params['minHour'] && (int)$date->date_time <= $params['maxHour']) {
                                        $ptResult['line'][$lineTpl]['schedule'][] = $date;
                                        $currentCol++;
                                    }
                                } else {
                                    $ptResult['line'][$lineTpl]['name'] = $stopPointSelected->name;
                                    $lineTpl++;
                                    $currentCol = 1;
                                }
                            }

                            if ($currentCol != 1) {
                                $ptResult['line'][$lineTpl]['name'] = $stopPointSelected->name;
                            }

                            unset($ptResult);
                         }
                    }

                    // Get calendars for this stoppoint
                    /*$stopPointCalendars = $this->navitiaManager->getStopPointCalendarsData(
                        $lineTimecard->getPerimeter()->getExternalCoverageId(),
                        $route->id,
                        $stopPoint->stopPointId
                    );*/

                }


            } // eo if
        }


        return $tResult;
    }
}