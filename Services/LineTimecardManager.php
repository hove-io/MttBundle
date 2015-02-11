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
            'minHour' => 6,
            'maxHour' => 21
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
                    );

                    if (count($stopPointSelected) !== 1) {
                        throw new \Exception('Duplicate or non-existent stopPoint id');
                    }

                    $tResult[$route->id][$stopPoint->stopPointId]['line'][$lineTpl] = array(
                        'name' => $stopPointSelected[0]->name
                    );


                    $blocks = $lineTimecard->getBlocks();
                    $stopSchedules = array();
                    foreach($blocks as $block) {
                        if ($block->getTypeId() == 'calendar' && !is_null($block->getContent())) {
                            $stopSchedules = $this->navitiaManager->getCalendarStopSchedulesByRoute(
                                $lineTimecard->getPerimeter()->getExternalCoverageId(),
                                $route->id,
                                $stopPoint->stopPointId,
                                $block->getContent()
                            );
                            $tResult[$route->id][$stopPoint->stopPointId]['calendars'][$block->getContent()] = $stopSchedules;
                        }
                    }


                    // Get calendars for this stoppoint
                    /*$stopPointCalendars = $this->navitiaManager->getStopPointCalendarsData(
                        $lineTimecard->getPerimeter()->getExternalCoverageId(),
                        $route->id,
                        $stopPoint->stopPointId
                    );*/

                    // Get Calendar for stopppoint by calendar
                    //$tmp1->stop_schedules->date_times[index]->date_time (string : 055200)
                    //$tmp1->stop_schedules->date_times[index]->additional_information (array)
                    //$tmp1->stop_schedules->date_times[index]->links (array)
                    /*$StopSchedulesByCalendar = $stopPointsFq = $this->navitiaManager->getCalendarStopSchedulesByRoute(
                        $lineTimecard->getPerimeter()->getExternalCoverageId(),
                        $route->id,
                        $stopPoint->stopPointId,
                        'Y2FsZW5kYXI6MQ'
                    );*/


                }


            } // eo if
        }


        return $tResult;
    }
}