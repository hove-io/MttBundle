<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LineTimecard as LineTimecard;
use CanalTP\MttBundle\Entity\LineConfig as LineConfig;
use CanalTP\SamEcoreApplicationManagerBundle\Exception;
use MyProject\Proxies\__CG__\stdClass;


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
     * @var array default options for displaying schedule
     */
    private $params = array(
        'maxColForHours' => 24,
        'minHour' => '060000',
        'maxHour' => '190000'
    );

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
     * @param array $options
     * @return array
     */
    public function getAllBlockCalendars(LineTimecard $lineTimecard, $options = array() )
    {
        $params = array_merge($this->params, $options);

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
            $lineTimecard->getPerimeter()
        );

        // Get  blocks of lineTimecard
        $blocks = $lineTimecard->getBlocks();

        $tResult = array();

        foreach ($routes as $route) {
            $stopPoints = array();

            foreach($timecards as $timecard) {
                if ($timecard->getRouteId() == $route->id) {
                    $stopPoints = $timecard->getStopPoints();
                    break;
                }
            }

            if (count($stopPoints) > 0) {
                foreach($blocks as $block) {
                    if ($block->getTypeId() == 'calendar' && !is_null($block->getContent())) {
                        $stopSchedules = $this->navitiaManager->getCalendarSchedulesByRoute(
                            $lineTimecard->getPerimeter()->getExternalCoverageId(),
                            $route->id,
                            $block->getContent()
                        );

                        $tResult['routes'][$route->id]['calendars'][$block->getContent()] = $stopSchedules;

                        // Create pointer
                        $p_tResult = &$tResult['routes'][$route->id]['calendars'][$block->getContent()];

                        // Get stop points selected
                        $p_tResult->stopPointsSelected = $this->getStopPointSelected(
                            $stopPoints,
                            $p_tResult->stop_schedules
                        );

                        // Load results
                        $p_tResult->lines = $this->getLineSchedules($p_tResult->stopPointsSelected, $params);

                        unset($p_tResult->stopPointsSelected, $p_tResult->stop_schedules, $p_tResult);
                    }
                }
            }
        } // eo foreach $routes

        return $tResult;
    }

    /**
     * @param LineTimecard $lineTimecard
     * @param array $calendarList
     * @param array $options
     */
    public function getAllCalendars(LineTimecard $lineTimecard, $calendarList, $options = array())
    {
        $params = array_merge($this->params, $options);

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
            $lineTimecard->getPerimeter()
        );

        $tResult = array();

        foreach($calendarList as $calendar) {
            foreach($routes as $key => $route) {

                $stopPoints = array();

                foreach($timecards as $timecard) {
                    if ($timecard->getRouteId() == $route->id) {
                        $stopPoints = $timecard->getStopPoints();
                        break;
                    }
                }

                if (count($stopPoints) > 0) {
                    $stopSchedules = $this->navitiaManager->getCalendarSchedulesByRoute(
                        $lineTimecard->getPerimeter()->getExternalCoverageId(),
                        $route->id,
                        $calendar->id
                    );

                    $tResult['routes'][$route->id]['calendars'][$calendar->id] = $stopSchedules;

                    // Create pointer
                    $p_tResult = &$tResult['routes'][$route->id]['calendars'][$calendar->id];
                    $p_tResult->name = $calendar->name;
                    $p_tResult->id = $calendar->id;
                    $p_tResult->active_periods = $calendar->active_periods;
                    $p_tResult->week_pattern = (array) $calendar->week_pattern;
                    $p_tResult->validity_pattern = $calendar->validity_pattern;

                    // Get stop points selected
                    $p_tResult->stopPointsSelected = $this->getStopPointSelected(
                        $stopPoints,
                        $p_tResult->stop_schedules
                    );

                    // Load results
                    $p_tResult->lines = $this->getLineSchedules($p_tResult->stopPointsSelected, $params);

                    unset($p_tResult->stopPointsSelected, $p_tResult->stop_schedules, $p_tResult);
                }
            }
        }
        return $tResult;
    }

    /**
     * @param LineTimecard $lineTimecard
     * @param string $routeId
     * @param array $calendar
     * @param array $options
     */
    public function getCalendar(LineTimecard $lineTimecard, $routeId, $calendar, $options = array())
    {
        $params = array_merge($this->params, $options);

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
            $lineTimecard->getPerimeter()
        );

        $tResult = array();
        $stopPoints = array();
        foreach($timecards as $timecard) {
            if ($timecard->getRouteId() == $routeId) {
                $stopPoints = $timecard->getStopPoints();
                break;
            }
        }

        if (count($stopPoints) > 0) {
            $stopSchedules = $this->navitiaManager->getCalendarSchedulesByRoute(
                $lineTimecard->getPerimeter()->getExternalCoverageId(),
                $routeId,
                $calendar->id
            );

            $tResult[$calendar->id] = $stopSchedules;

            // Create pointer
            $p_tResult = &$tResult[$calendar->id];
            $p_tResult->name = $calendar->name;
            $p_tResult->id = $calendar->id;
            $p_tResult->active_periods = $calendar->active_periods;
            $p_tResult->week_pattern = (array) $calendar->week_pattern;
            $p_tResult->validity_pattern = $calendar->validity_pattern;

            // Get stop points selected
            $p_tResult->stopPointsSelected = $this->getStopPointSelected(
                $stopPoints,
                $p_tResult->stop_schedules
            );

            // Load results
            $p_tResult->lines = $this->getLineSchedules($p_tResult->stopPointsSelected, $params);

            unset($p_tResult->stopPointsSelected, $p_tResult->stop_schedules, $p_tResult);
        }

        return $tResult;
    }

    /**
     * Get stops points selected
     *
     * @param array $stopPoints
     * @param array $listStopPoint
     * @return array
     * @throws \Exception
     */
    private function getStopPointSelected($stopPoints, $listStopPoint)
    {
        $listStopPointsSelected = array();

        foreach($stopPoints as $stopPoint) {
            $stopPoint = json_decode($stopPoint);

            // Search stopPoint object by id into $p_tResult->stop_schedules
            $stopPointSelected = array_values(
                array_filter(
                    $listStopPoint,
                    function ($object) use ($stopPoint) {
                        return ($object->stop_point->id == $stopPoint->stopPointId);
                    }
                )
            )[0];

            if (is_null($stopPointSelected)) {
                throw new \Exception('Duplicate or non-existent stopPoint id');
            }

            $listStopPointsSelected[] = $stopPointSelected;
        }

        return $listStopPointsSelected;
    }

    /**
     * Load an array to display easily a line's schedules
     *
     * @param array stdClass $stopPointSelected
     * @param array $params display options
     * @return array
     */
    private function getLineSchedules($stopPointSelected, $params)
    {
        $line = 0;
        $result = array();
        $params['frequencyNbCol'] = 3;

        $bFrequency = false;
        if (isset($params['frequencies'])) {
            $numberOfFrequencies = count($params['frequencies']);
            $bFrequency = ($numberOfFrequencies > 0) ? true : false;
        }
        
        foreach($stopPointSelected as $stop) {

            $lineTpl = 0;
            $currentCol = 1;
            $schedule = array();
            $allFrequenciesPassed = false;
            if($bFrequency) {
                $nextFrequencyBeginTime = $params['frequencies'][0]->getStartTime()->format('His');
                $nextFrequencyEndTime = $params['frequencies'][0]->getEndTime()->format('His');
                $frequencyIndex = 0;
                $currenltyInsideFrequency = false;
            }

            foreach ($stop->date_times as $detail) {
                if (!empty($detail->date_time)) {
                    $detail->date_time_formated = date('His', strtotime($detail->date_time));
                    if ($currentCol <= $params['maxColForHours']) {
                        if ((int)$detail->date_time_formated >= (int)$params['minHour']
                            && (int)$detail->date_time_formated <= (int)$params['maxHour']
                        ) {

                            if ($bFrequency && !$allFrequenciesPassed) { // Frequency use case
                                if($currenltyInsideFrequency)
                                {
                                    if ( (int)$detail->date_time_formated > $nextFrequencyEndTime) {
                                        $currenltyInsideFrequency = false;
                                        $frequencyIndex++;
                                        if( $frequencyIndex < $numberOfFrequencies) {
                                            $nextFrequencyBeginTime = $params['frequencies'][$frequencyIndex]->getStartTime()->format('His');
                                            $nextFrequencyEndTime = $params['frequencies'][$frequencyIndex]->getEndTime()->format('His');
                                        }
                                        else
                                        {
                                            $allFrequenciesPassed = true;
                                        }
                                    }
                                    else
                                    {
                                        continue; // Ignore all datetime before $nextFrequencyEndTime
                                    }
                                }
                                else
                                {
                                    if ( (int)$detail->date_time_formated >= $nextFrequencyBeginTime) {
                                        $currenltyInsideFrequency = true;
                                        $schedule[] = $params['frequencies'][$frequencyIndex]->getContent();
                                        $schedule[] = $params['frequencies'][$frequencyIndex]->getContent();
                                        $schedule[] = $params['frequencies'][$frequencyIndex]->getContent();
                                        $currentCol += 3;
                                        continue;
                                    }
                                }
                            }

                            // Ajout de l'horaire
                            $schedule[] = $detail->date_time_formated;
                            $currentCol++;
                        }
                    } else {
                        $result[$lineTpl][$line] = array(
                            'name' => $stop->stop_point->name,
                            'schedule' => $schedule
                        );
                        $schedule = array();
                        $lineTpl++;
                        $currentCol = 1;
                    }
                } else {
                    if ($currentCol <= $params['maxColForHours']) {
                        $schedule[] = null;
                        $currentCol++;
                    } else {
                        $result[$lineTpl][$line] = array(
                            'name' => $stop->stop_point->name,
                            'schedule' => $schedule
                        );
                        $schedule = array();
                        $lineTpl++;
                        $currentCol = 1;
                    }
                }
            }
            if ($currentCol != 1) {
                $limit = (int)$params['maxColForHours'] - (int)count($schedule);
                if ( $limit > 0 ) {
                    // fill $schedule for obtained maxColForHours entries
                    $schedule = array_merge($schedule, array_fill(
                            (count($schedule) - 1),
                            $limit,
                            null
                        )
                    );
                }
                $result[$lineTpl][$line] = array(
                    'name' => $stop->stop_point->name,
                    'schedule' => $schedule
                );
            }
            $line++;
        }

        return $result;
    }
}
