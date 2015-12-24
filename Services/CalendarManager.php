<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use \Doctrine\Common\Collections\Collection;
use Symfony\Component\Translation\TranslatorInterface;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Season;

class CalendarManager
{
    private $navitia = null;
    private $translator = null;
    private $computedNotesId = array();
    private $calendars = array();
    private $colorNotes = array();
    private $additionalInformationsExcluded;

    public function __construct(Navitia $navitia, TranslatorInterface $translator)
    {
        $this->navitia = $navitia;
        $this->translator = $translator;
        $this->additionalInformationsExcluded = array('partial_terminus', 'none');
    }

    /**
     * Converts strings coming from Navitia as T230200 into php DateTime objects
     */
    private function parseDateTimes($datetimes)
    {
        foreach ($datetimes as &$datetime) {
            $datetime->date_time = new \DateTime($datetime->date_time);
        }

        return $datetimes;
    }

    private function sortLinks($linkA, $linkB)
    {
        $result = 0;

        if ($linkA->type != $linkB->type) {
            $result = ($linkA->type == 'notes' && $linkB->type == 'exceptions') ? -1 : 1;
        }

        return ($result);
    }

    private function hasExceptions($links)
    {
        $result = false;

        foreach ($links as $link) {
            if ($link->type == 'exceptions') {
                $result = true;
                break;
            }
        }

        return ($result);
    }

    /**
     * groups datetimes by hours
     */
    private function prepareDateTimes($datetimes)
    {
        $parsedDateTimes = $this->parseDateTimes($datetimes);
        $sortedDateTimes = array();
        foreach ($parsedDateTimes as $parsedDateTime) {
            $hour = date('G', $parsedDateTime->date_time->getTimestamp());
            if (!isset($sortedDateTimes[$hour])) {
                $sortedDateTimes[$hour] = array();
            }
            if ($this->hasExceptions($parsedDateTime->links)) {
                usort($parsedDateTime->links, array($this, "sortLinks"));
            }
            $sortedDateTimes[$hour][] = $parsedDateTime;
        }

        return $sortedDateTimes;
    }

    public function isExceptionInsideSeason($note, $season)
    {
        $dateTime = new \DateTime($note->date);

        return $note->type != 'notes' && $dateTime >= $season->getStartDate() && $dateTime <= $season->getEndDate();
    }

    /**
     * gather notes and ensure these notes are unique. (based on Navitia ID)
     */
    private function computeNotes($lineConfig, $notesToReturn, $newCalendar, $aggregation)
    {
        $layoutConfig = $lineConfig->getLayoutConfig();

        foreach ($newCalendar->notes as $note) {
            if (($note->type == 'notes' || $this->isExceptionInsideSeason($note, $lineConfig->getSeason())) &&
                ($layoutConfig->dispatchesNotes() || !in_array($note->id, $this->computedNotesId))) {
                $note->calendarId = $newCalendar->id;
                $this->computedNotesId[] = $note->id;

                if (!isset($this->colorNotes[$note->id])) {
                    $this->colorNotes[$note->id] = $this->getNewColor($layoutConfig->getNotesColors());
                }

                $note->color = $this->colorNotes[$note->id];
                $notesToReturn[] = $note;
            }
        }

        return $notesToReturn;
    }

    public function getNewColor($colors = array())
    {
        if (!isset($colors[count($this->colorNotes)])) {
            return '#ff794e';
        }

        return $colors[count($this->colorNotes)];
    }

    /**
     * find a calendar or throws an exception if a calendar is not found
     */
    private function findCalendar($calendarId, $calendars)
    {
        if (isset($calendars[$calendarId])) {
            return $calendars[$calendarId];
        } else {
            throw new \Exception(
                $this->translator->trans(
                    'services.calendar_manager.calendar_in_block_not_found',
                    array('%calendarId%' => $calendarId),
                    'exceptions'
                )
            );
        }
    }

    /**
     * Index calendars by Id and cast week_patterns into array for templates
     */
    private function sortCalendars($calendars)
    {
        $calendarsSorted = array();
        foreach ($calendars as $calendar) {
            $calendarsSorted[$calendar->id] = $calendar;
            $calendarsSorted[$calendar->id]->week_pattern = (array) $calendarsSorted[$calendar->id]->week_pattern;
        }

        return $calendarsSorted;
    }

    /**
     * Generate value property of exceptions to display in view
     */
    private function generateExceptionsValues($navitiaExceptions)
    {
        $exceptions = array();

        foreach ($navitiaExceptions as $exception) {

            $date = new \DateTime($exception->date);

            $exception->value = $this->translator->trans(
                'global.exceptions.' . strtolower($exception->type),
                array('%date%' => $date->format('d/m/Y')),
                'messages'
            );
            $exceptions[$exception->id] = $exception;
        }

        return $exceptions;
    }

    /**
     * Add schedules coming from Navitia to calendar object
     */
    private function addSchedulesToCalendar($calendar, $schedules)
    {
        $calendar->schedules = $schedules;
        $calendar->schedules->date_times = $this->prepareDateTimes($calendar->schedules->date_times);

        return $calendar;
    }

    public function getCalendars($externalCoverageId, $stopTimetable, $stopPointInstance)
    {
        return $this->getCalendarsForStopPointAndStopTimetable($externalCoverageId, $stopTimetable, $stopPointInstance);
    }

    /**
     * Generate value propriety of exceptions to display in view
     */
    private function generateAdditionalInformations($additionalInformationsId)
    {
        $additionalInformations = null;

        if (!empty($additionalInformationsId) && !in_array($additionalInformationsId, $this->additionalInformationsExcluded)) {
            $additionalInformations = $this->translator->trans(
                'calendar.schedules.additional_informations.' . $additionalInformationsId,
                array(),
                'messages'
            );
        }

        return $additionalInformations;
    }

    private function parsePeriods($periods)
    {
        foreach ($periods as $period) {
            $period->begin = new \DateTime($period->begin);
            $period->end = new \DateTime($period->end);
        }

        return $periods;
    }

    /**
     * Returns Calendars enhanced with schedules for a stop point and a route
     * Datetimes are parsed and response formatted for template
     * *All* calendars coming from Navitia for this stoppoint are returned
     *
     * @param String $externalCoverageId
     * @param Object $stopTimetable
     * @param Object $stopPointInstance
     *
     * @return object
     */
    public function getCalendarsForStopPoint($externalCoverageId, $externalRouteId, $externalStopPointId)
    {
        $calendarsData = $this->navitia->getStopPointCalendarsData(
            $externalCoverageId,
            $externalRouteId,
            $externalStopPointId
        );
        $calendarsSorted = isset($calendarsData->calendars) ? $this->sortCalendars($calendarsData->calendars) : array();
        foreach ($calendarsSorted as $calendar) {
            $stopSchedulesData = $this->navitia->getCalendarStopSchedulesByRoute(
                $externalCoverageId,
                $externalRouteId,
                $externalStopPointId,
                $calendar->id
            );
            $calendar = $this->addSchedulesToCalendar($calendar, $stopSchedulesData->stop_schedules);
            $calendar->notes = array_merge(
                $stopSchedulesData->notes,
                $this->generateExceptionsValues($stopSchedulesData->exceptions)
            );
            $calendar->schedules->additional_informations = $this->generateAdditionalInformations($calendar->schedules->additional_informations);
            $calendar->active_periods = $this->parsePeriods((isset($calendar->active_periods) ? $calendar->active_periods : array()));
            $calendarsSorted[$calendar->id] = $calendar;
        }

        return $calendarsSorted;
    }

    private function getAnnotationsIfIsIncluded($annotations, $links)
    {
        $result = array();

        foreach ($links as $link) {
            foreach ($annotations as $annotation) {
                if ($link->id == $annotation->id) {
                    $result[$link->id] = $annotation;
                }
            }
        }

        return ($result);
    }

    private function purgeAnnotationsNotUsed($stopSchedulesData, $layoutConfig)
    {
        $result = array();

        if (empty($stopSchedulesData->notes) && empty($stopSchedulesData->exceptions)) {
            return ($result);
        }

        $hourStart = $layoutConfig->getCalendarStart();
        $hourEnd = $layoutConfig->getCalendarEnd();
        $isPM = ($hourEnd <= $hourStart);
        $hourEnd = ($isPM) ? ($hourEnd + 24) : $hourEnd;
        $dateTimes = $stopSchedulesData->stop_schedules->date_times;

        foreach ($dateTimes as $dateTime) {
            foreach ($dateTime as $time) {
                $hourNote = $time->date_time->format('H');
                $hourNote = ($isPM && $hourNote < $hourStart) ? $hourNote + 24 : $hourNote;

                if (!empty($time->links) && $hourNote >= $hourStart && $hourNote <= $hourEnd) {
                    $result = array_merge(
                        $result,
                        $this->getAnnotationsIfIsIncluded($stopSchedulesData->notes, $time->links),
                        $this->generateExceptionsValues($this->getAnnotationsIfIsIncluded($stopSchedulesData->exceptions, $time->links))
                    );
                }
            }
        }

        return ($result);
    }

    private function duplicateCalendarsInterpretion($calendar, array &$calendarsSorted, Block $block)
    {
        if (in_array($calendar->id, $this->calendars)) {
            $calendar = clone $calendar;
            $calendar->id .= '-' . count($this->calendars);
            $calendarsSorted[$calendar->id] = $calendar;
            $block->setContent($calendar->id);
        }
        $this->calendars[] = $calendar->id;

        return ($calendar);
    }

    /**
     * Returns Calendars enhanced with schedules for a stop point and a route
     * Datetimes are parsed and response formatted for template
     * Only calendars added to stopTimetable are kept
     *
     * @param String $externalCoverageId
     * @param Object $stopTimetable
     * @param Object $stopPointInstance
     *
     * @return object
     */
    public function getCalendarsForStopPointAndStopTimetable(
        $externalCoverageId,
        $stopTimetable,
        $stopPointInstance
    )
    {
        $notesComputed = array();
        $calendarsSorted = array();
        // indicates whether to aggregate or dispatch notes
        $layout = $stopTimetable->getLineConfig()->getLayoutConfig();
        $calendarsData = $this->navitia->getStopPointCalendarsData(
            $externalCoverageId,
            $stopTimetable->getExternalRouteId(),
            $stopPointInstance->getExternalId()
        );
        if (isset($calendarsData->calendars)) {
            $calendarsSorted = $this->sortCalendars($calendarsData->calendars);
        }
        // calendar blocks are defined on route/stopTimetable level
        if (count($stopTimetable->getBlocks()) > 0) {
            foreach ($stopTimetable->getBlocks() as $block) {
                if ($block->getType() == 'calendar') {
                    $calendar = $this->findCalendar($block->getContent(), $calendarsSorted);
                    $stopSchedulesData = $this->navitia->getCalendarStopSchedulesByRoute(
                        $externalCoverageId,
                        $stopTimetable->getExternalRouteId(),
                        $stopPointInstance->getExternalId(),
                        $block->getContent()
                    );
                    $calendar = $this->duplicateCalendarsInterpretion($calendar, $calendarsSorted, $block);
                    $calendar = $this->addSchedulesToCalendar(
                        $calendar,
                        $stopSchedulesData->stop_schedules
                    );
                    $calendar->schedules->additional_informations = $this->generateAdditionalInformations($calendar->schedules->additional_informations);
                    $calendar->notes = $this->purgeAnnotationsNotUsed(
                        $stopSchedulesData,
                        $stopTimetable->getLineConfig()->getLayoutConfig()
                    );
                    $notesComputed = $this->computeNotes(
                        $stopTimetable->getLineConfig(),
                        $notesComputed,
                        $calendar,
                        $layout->aggregatesNotes()
                    );
                }
            }
        }

        return array('calendars' => $calendarsSorted, 'notes' => $notesComputed);
    }

    /**
     * Returns Calendars for a route
     * Datetimes are not parsed
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
     *
     * @return object
     */
    public function getCalendarsForRoute($externalCoverageId, $externalRouteId, \DateTime $startDate, \DateTime $endDate)
    {
        $calendarsData = $this->navitia->getRouteCalendars($externalCoverageId, $externalRouteId, $startDate, $endDate);
        $calendarsSorted = array();
        if (isset($calendarsData->calendars) && !empty($calendarsData->calendars)) {
            foreach ($calendarsData->calendars as $calendar) {
                //make it easier for template
                $calendarsSorted[$calendar->id] = $calendar;
            }
        }

        return $calendarsSorted;
    }

    /**
     * Check if calendar is valid during a season (even a minimal amount of time)
     */
    public function isIncluded($calendarId, Season $season)
    {
        $externalCoverageId = $season->getPerimeter()->getExternalCoverageId();
        $calendarsData = $this->navitia->getCalendar($externalCoverageId, $calendarId);
        $calendar = $calendarsData->calendars[0];
        $calendarBeginDate = new \DateTime($calendar->active_periods[0]->begin);
        $calendarEndDate = new \DateTime($calendar->active_periods[0]->end);
        if (($season->getStartDate() < $calendarBeginDate && $calendarBeginDate < $season->getEndDate())  || ($season->getStartDate() < $calendarEndDate && $calendarEndDate < $season->getEndDate())) {
            return true;
        }

        return false;
    }

    /**
     * Returning a calendar for a block
     *
     * @param string $externalCoverageId
     * @param Block $block
     * @param array $parameters
     */
    public function getCalendarForBlock(
        $externalCoverageId,
        Block $block,
        $parameters
    ) {
        $calendar = $this->navitia->getCalendar($externalCoverageId, $block->getContent())->calendars[0];

        if (empty($parameters['hourOffset'])) {
            throw new \Exception('The hour offset have to be set');
        }

        $parameters['startProductionDate'] = $this->navitia->getStartProductionDate(
            $externalCoverageId,
            $parameters['hourOffset']
        );

        return $this->getRouteSchedules(
            $externalCoverageId,
            $block->getExternalRouteId(),
            $calendar,
            $parameters
        );
    }

    /**
     * Returning calendars for a line.
     *
     * @param string $externalCoverageId
     * @param string $externalNetworkId
     * @param string $externalLineId
     * @param array $parameters
     */
    public function getCalendarsForLine(
        $externalCoverageId,
        $externalNetworkId,
        $externalLineId,
        $parameters
    ) {
        $routes = $this->navitia->getLineRoutes($externalCoverageId, $externalNetworkId, $externalLineId);
        $calendars = $this->navitia->getLineCalendars($externalCoverageId, $externalNetworkId, $externalLineId);

        if (empty($parameters['hourOffset'])) {
            throw new \Exception('The hour offset have to be set');
        }

        $parameters['startProductionDate'] = $this->navitia->getStartProductionDate(
            $externalCoverageId,
            $parameters['hourOffset']
        );

        $schedule = array();
        foreach ($routes as $route) {
            $externalRouteId = $route->id;
            $schedule[$externalRouteId]['direction'] = $route->name;

            foreach ($calendars as $calendar) {
                $schedule[$externalRouteId]['calendars'][$calendar->id] = $this->getRouteSchedules(
                    $externalCoverageId,
                    $externalRouteId,
                    $calendar,
                    $parameters
                );
            }
        }

        return $schedule;
    }

    /**
     * Getting route schedules
     *
     * @param string $externalCoverageId
     * @param string $externalRouteId
     * @param mixed &$calendar
     */
    private function getRouteSchedules(
        $externalCoverageId,
        $externalRouteId,
        &$calendar,
        $parameters
    ) {
        try {
            $routeSchedules = $this->navitia->getRouteSchedulesByRouteAndCalendar(
                $externalCoverageId,
                $externalRouteId,
                $calendar->id,
                $parameters['startProductionDate']
            );
        } catch(\Exception $e) {
            return $this->createEmptyLineCalendar($calendar);
        }

        $this->prepareRouteSchedules($routeSchedules, $calendar);

        if (!empty($parameters['stopPoints'])) {
            $this->filterStopPoints($routeSchedules->route_schedules, $parameters['stopPoints']);
        }

        if (count($routeSchedules->route_schedules) == 0) {
            throw new \Exception('No stop points found for the route : '.$externalRouteId);
        }

        $this->buildFullCalendar($routeSchedules, $parameters['hourOffset']);

        $stopTimesToDelete = array();
        $firstTrip = array_shift(array_values($routeSchedules->route_schedules['metadata']))['firstHour'];

        if (!empty($parameters['limits'])) {
            $this->processTimeLimits(
                $parameters['limits'],
                $parameters['hourOffset'],
                $firstTrip
            );
            $this->cutCalendar($routeSchedules->route_schedules, $parameters['limits'], $stopTimesToDelete);
        }

        if (!empty($parameters['frequencies'])) {
            // TODO: Because the frequencies start_time and end_time are time type in database,
            // we can't manage the 23:59+ times and can't add a frequency after midnight
            foreach ($parameters['frequencies'] as $idx => $frequency) {
                $limits = array(
                    'min' => $frequency->getStartTime()->format('His'),
                    'max' => $frequency->getEndTime()->format('His')
                );

                $this->processTimeLimits(
                    $limits,
                    $parameters['hourOffset'],
                    $firstTrip
                );

                $newFrequency = true;
                foreach ($routeSchedules->route_schedules['metadata'] as $columnNumber => $metadata) {
                    if ($metadata['type'] === 'trip') {
                        if ($metadata['firstHour'] >= $limits['min'] &&
                            $metadata['firstHour'] <= $limits['max']
                        ) {
                            $stopTimesToDelete[] = $columnNumber;
                            if ($newFrequency) {
                                $newFrequency = false;
                                $routeSchedules->route_schedules['metadata'][$columnNumber] = array(
                                    "type"      => "frequency",
                                    "colspan"   => $frequency->getColumns(),
                                    "content"   => $frequency->getContent()
                                );
                                $routeSchedules->route_schedules['columns'] += $frequency->getColumns() - 1;
                            } else {
                                unset($routeSchedules->route_schedules['metadata'][$columnNumber]);
                                $routeSchedules->route_schedules['columns']--;
                            }
                        }
                    }
                }
                ksort($routeSchedules->route_schedules['metadata']);
            }
        }

        if (count($stopTimesToDelete) > 0) {
            $this->deleteStopTimes(
                $routeSchedules->route_schedules,
                $stopTimesToDelete
            );
        }

        if (!empty($parameters['checkFrequency'])) {
            $this->transformHoursToFrequencies($routeSchedules->route_schedules);
        }

        unset($routeSchedules->headers, $routeSchedules->exceptions);

        return $routeSchedules;
    }

    /**
     * Preparing empty response if something went wrong
     *
     * @param mixed &$calendar
     */
    private function createEmptyLineCalendar(&$calendar)
    {
        $data = new \stdClass;
        $data->route_schedules = array('columns' => 0);
        $data->notes = array();
        $data->name = $calendar->name;
        $data->id = $calendar->id;

        return $data;
    }

    /**
     * Transforming route_schedules and calendar objects
     * into something more adapted containing useful information
     *
     * @param mixed &$routeSchedules
     * @param mixed &$calendar
     */
    private function prepareRouteSchedules(&$routeSchedules, &$calendar)
    {
        $data = new \stdClass;
        $data->route_schedules = $routeSchedules->route_schedules[0]->table->rows;
        $data->headers = $routeSchedules->route_schedules[0]->table->headers;
        $data->notes = isset($routeSchedules->notes) ? $routeSchedules->notes : array();
        $data->exceptions = isset($routeSchedules->exceptions) ? $routeSchedules->exceptions : array();
        $data->name = $calendar->name;
        $data->id = $calendar->id;

        $routeSchedules = $data;
    }

    /**
     * Filtering selected stop points
     *
     * @param mixed $data
     * @param Collection $filter
     */
    private function filterStopPoints(&$data, Collection $filter)
    {
        foreach ($data as $idx => $stopPoint) {
            $selected = $filter->filter(
                function($stop) use ($stopPoint) {
                    return $stop->getExternalStopPointId() == $stopPoint->stop_point->id;
                }
            );

            if ($selected->isEmpty()) {
                unset($data[$idx]);
            }
        }
    }

    /**
     * Building full calendar.
     *
     * @param mixed &$schedule
     * @param integer $hourOffset
     *
     * Building a full one-line calendar array.
     * The calendar's structure in JSON is :
     *  {
     *      "stops": [
     *          "0": {
     *              "stopName": "",
     *              "stopTimes": []
     *          },
     *      ],
     *      "metadata": {
     *          "0": {
     *              "type": "trip",
     *              "trip": "",
     *              "firstHour": "",
     *              "lastHour": "",
     *              "departureStop": "",
     *              "arrivalStop": "",
     *              "note": ""
     *          }
     *      }
     *  }
     * The metadata describe columns information in the calendar.
     * It's type is just "trip" at the end of the function but it
     * can be replaced later by something else (frequency for example).
     */
    private function buildFullCalendar(&$schedule, $hourOffset)
    {
        $calendar = array(
            'columns' => count($schedule->route_schedules[0]->date_times),
            'stops' => array(),
            'metadata' => array()
        );

        foreach ($schedule->route_schedules as $lineNumber => $stop) {
            $stopTimes = array();
            $previousHour = null;
            $dayOffset = false;
            foreach ($stop->date_times as $columnNumber => $detail) {
                $hour = intVal(substr($detail->date_time, 0, 2));

                if (empty($detail->date_time)) {
                    if (!isset($calendar['metadata'][$columnNumber])) {
                        $calendar['metadata'][$columnNumber] = null;
                    }
                    $stopTimes[] = null;
                } else {
                    // Detecting day / day+1 limit looking at hours and hourOffset
                    if ($previousHour && !$dayOffset && $previousHour > $hour) {
                        if ($previousHour > $hourOffset && $hour < $hourOffset) {
                            $dayOffset = true;
                        }
                    }

                    $date = strtotime($detail->date_time);
                    if ($dayOffset) {
                        $date = strtotime('+1 day', $date);
                    }

                    $stopTimes[] = $date;

                    if (!isset($calendar['metadata'][$columnNumber])) {
                        $trip = null;
                        if (!empty($detail->links)) {
                            $tripInfo = array_filter(
                                $detail->links,
                                function ($link) {
                                    return ($link->type == 'vehicle_journey');
                                }
                            );
                            $trip = array_pop($tripInfo)->value;
                        }

                        $calendar['metadata'][$columnNumber] = array(
                            'type' => 'trip',
                            'firstHour' => $date,
                            'lastHour' => $date,
                            'trip' => $trip,
                            'departureStop' => $stop->stop_point->name,
                            'arrivalStop' => $stop->stop_point->name,
                        );
                    } else {
                        $calendar['metadata'][$columnNumber]['lastHour'] = $date;
                        $calendar['metadata'][$columnNumber]['arrivalStop'] = $stop->stop_point->name;
                    }

                    $previousHour = $hour;
                }
            }

            $calendar['stops'][$lineNumber] = array(
                'stopName' => $stop->stop_point->name,
                'stopExternalId' => $stop->stop_point->id,
                'stopTimes' => $stopTimes,
            );
        }

        $schedule->route_schedules = $calendar;
    }

    /**
     * Processing time limits using a reference date and the first
     * hour of the first trip in calendar
     *
     * @param array &$limits
     * @param integer $hourOffset
     * @param time $firstTrip
     */
    private function processTimeLimits(&$limits, $hourOffset, $firstTrip)
    {
        if (empty($limits['min']) || intVal($limits['min']) < 0) {
            throw new \Exception('The low limit is invalid');
        }

        if (empty($limits['max']) || intVal($limits['max']) < 0) {
            throw new \Exception('The high limit is invalid');
        }

        $referenceDate = new \Datetime(date('Y-m-d\TH:i:s', $firstTrip));

        // The date is D-1 if the first hour is < hourOffset
        if (intval($referenceDate->format('H')) < $hourOffset) {
            $referenceDate->sub(new \DateInterval('P1D'));
        }

        $referenceDate = $referenceDate->format('Y-m-d');

        foreach ($limits as $idx => $time)
        {
            $time = (int)$time;
            if ($time > 240000) {
                $limits[$idx] = strtotime(
                    "+1 day",
                    strtotime($referenceDate." ".$this->formatTime(($time - 240000), 6))
                );
            } else {
                $limits[$idx] = strtotime($referenceDate." ".$this->formatTime($time, 6));
            }
        }
    }

    /**
     * Formatting time
     * @param integer $hour
     * @param integer $length
     * @return string
     *
     * Formating time from int to $length chars string.
     */
    private function formatTime($time, $length)
    {
        return str_repeat('0', ($length - strlen($time))).$time;
    }

    /**
     * Cutting calendar hours using limitations
     *
     * @param mixed &$schedule
     * @param array $limits
     * @param array &$stopTimesToDelete
     */
    private function cutCalendar(&$schedule, $limits, &$stopTimesToDelete)
    {
        foreach ($schedule['metadata'] as $columnNumber => $metadata)
        {
            if ($metadata['firstHour'] < $limits['min']
                || $metadata['firstHour'] > $limits['max']
            ) {
                unset($schedule['metadata'][$columnNumber]);
                $stopTimesToDelete[] = $columnNumber;
                $schedule['columns'] -= 1;
            }
        }
    }

    /**
     * Deleting stopTimes from stops in a calendar array.
     * stopTimesToDelete contains the deleted stopTimes position.
     *
     * @param array $stops
     * @param array $stopTimesToDelete
     */
    private function deleteStopTimes(&$schedule, $stopTimesToDelete)
    {
        $columnsToDelete = 0;
        foreach ($schedule['stops'] as $key => $stop) {
            foreach (array_keys($stop['stopTimes']) as $columnNumber) {
                if (in_array($columnNumber, $stopTimesToDelete)) {
                    unset($stop['stopTimes'][$columnNumber]);
                    $columnsToDelete++;
                }
            }
            $schedule['stops'][$key]['stopTimes'] = $stop['stopTimes'];
        }
    }

    /**
     * Transforming hours into time diff between trips (frequencies)
     *
     * @param mixed &$schedule
     */
    private function transformHoursToFrequencies(&$schedule)
    {
        foreach ($schedule['stops'] as $line => $stop) {
            $previousColumn = null;
            foreach ($stop['stopTimes'] as $column => $stopTime) {
                if ($previousColumn === null) {
                    $previousColumn = $column;
                    continue;
                } else if (
                    $schedule['stops'][$line]['stopTimes'][$previousColumn] !== null &&
                    $stopTime !== null
                ){
                    $schedule['stops'][$line]['stopTimes'][$previousColumn] =
                        $stopTime - $schedule['stops'][$line]['stopTimes'][$previousColumn];
                }
                else {
                    $schedule['stops'][$line]['stopTimes'][$previousColumn] = null;
                }
                $previousColumn = $column;
            }
            array_pop($schedule['stops'][$line]['stopTimes']);
        }
        array_pop($schedule['metadata']);
        $schedule['columns'] -= 1;
    }
}
