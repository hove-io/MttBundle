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
    public function getAllCalendars(LineTimecard $lineTimecard, $options = array() )
    {
        $params = array(
            'maxColForHours' => 24,
            'minHour' => '060000',
            'maxHour' => '190000'
        );

        $params = array_merge($params, $options);

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
                        $p_tResult = &$tResult['routes'][$route->id]['calendars'][$block->getContent()];

                        foreach($stopPoints as $stopPoint) {
                            $stopPoint = json_decode($stopPoint);

                            // Search stopPoint object by id into $p_tResult->stop_schedules
                            $stopPointSelected = array_values(
                                array_filter(
                                    $p_tResult->stop_schedules,
                                    function ($object) use ($stopPoint) {
                                        return ($object->stop_point->id == $stopPoint->stopPointId);
                                    }
                                )
                            )[0];

                            if (is_null($stopPointSelected)) {
                                throw new \Exception('Duplicate or non-existent stopPoint id');
                            }

                            $p_tResult->stopPointsSelected[] = $stopPointSelected;
                        }

                        unset($p_tResult->stop_schedules);

                        // Gestion du format d'affichage

                        $line = 0;
                        $p_tResult->lines[] = array();
                        foreach($p_tResult->stopPointsSelected as $stop) {

                            $lineTpl = 0;
                            $currentCol = 1;
                            $p_tResult->stops[$stop->stop_point->id] = $stop;
                            $schedule = array();

                            foreach ($stop->date_times as $detail) {
                                if (!empty($detail->date_time)) {
                                    $detail->date_time_formated = date('His', strtotime($detail->date_time));
                                    if ($currentCol <= $params['maxColForHours']) {
                                        if ((int)$detail->date_time_formated >= (int)$params['minHour']
                                            && (int)$detail->date_time_formated <= (int)$params['maxHour']
                                        ) {

                                           // $p_tResult->stops[$stop->stop_point->id]->line[$lineTpl]['schedule'][] = $detail->date_time_formated;
                                            $schedule[] = $detail->date_time_formated;

                                            $currentCol++;
                                        }
                                    } else {
                                        //$p_tResult->stops[$stop->stop_point->id]->line[$lineTpl]['name'] = $stop->stop_point->name;
                                        $p_tResult->lines[$lineTpl][$line] = array(
                                            'name' => $stop->stop_point->name,
                                            'schedule' => $schedule
                                        );
                                        $schedule = array();
                                        $lineTpl++;
                                        $currentCol = 1;
                                    }
                                } else {
                                    if ($currentCol <= $params['maxColForHours']) {
                                        //$p_tResult->stops[$stop->stop_point->id]->line[$lineTpl]['schedule'][] = null;
                                        $schedule[] = null;
                                        $currentCol++;
                                    } else {
                                        //$p_tResult->stops[$stop->stop_point->id]->line[$lineTpl]['name'] = $stop->stop_point->name;
                                        $p_tResult->lines[$lineTpl][$line] = array(
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
                                $p_tResult->lines[$lineTpl][$line] = array(
                                    'name' => $stop->stop_point->name,
                                    'schedule' => $schedule
                                );
                            }
                            $line++;
                        }

                        unset($p_tResult->stopPointsSelected, $p_tResult);
                    }
                }

            } // eo if

        } // eo foreach $routes
        return $tResult;
    }
}