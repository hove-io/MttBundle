<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class CalendarManager
{
    private $navitia = null;
    private $translator = null;
    private $computedNotesId = array();

    public function __construct(Navitia $navitia, Translator $translator)
    {
        $this->navitia = $navitia;
        $this->translator = $translator;
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
    private function computeNotes($notesToReturn, $newCalendar, $season, $aggregation)
    {
        foreach ($newCalendar->notes as $note) {
            if (($note->type == 'notes' || $this->isExceptionInsideSeason($note, $season)) &&
                ($aggregation == false || !in_array($note->id, $this->computedNotesId))) {
                $note->calendarId = $newCalendar->id;
                $this->computedNotesId[] = $note->id;
                $notesToReturn[] = $note;
            }
        }

        return $notesToReturn;
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
            $exceptions[] = $exception;
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

    public function getCalendars($externalCoverageId, $timetable, $stopPointInstance)
    {
        return $this->getCalendarsForStopPointAndTimetable($externalCoverageId, $timetable, $stopPointInstance);
    }

    /**
     * Generate value propriety of exceptions to display in view
     */
    private function generateAdditionalInformations($additionalInformationsId)
    {
        $additionalInformations = null;

        if (!empty($additionalInformationsId)) {
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
     * @param Object $timetable
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
            $calendar->active_periods = $this->parsePeriods($calendar->active_periods);
            $calendarsSorted[$calendar->id] = $calendar;
        }

        return $calendarsSorted;
    }

    /**
     * Returns Calendars enhanced with schedules for a stop point and a route
     * Datetimes are parsed and response formatted for template
     * Only calendars added to timetable are kept
     *
     * @param String $externalCoverageId
     * @param Object $timetable
     * @param Object $stopPointInstance
     *
     * @return object
     */
    public function getCalendarsForStopPointAndTimetable(
        $externalCoverageId,
        $timetable,
        $stopPointInstance
    )
    {
        $notesComputed = array();
        $calendarsFiltered = array();
        $calendarsSorted = array();
        // indicates whether to aggregate or dispatch notes
        $layout = $timetable->getLineConfig()->getLayoutConfig();
        $calendarsData = $this->navitia->getStopPointCalendarsData(
            $externalCoverageId,
            $timetable->getExternalRouteId(),
            $stopPointInstance->getExternalId()
        );
        if (isset($calendarsData->calendars)) {
            $calendarsSorted = $this->sortCalendars($calendarsData->calendars);
        }
        // calendar blocks are defined on route/timetable level
        if (count($timetable->getBlocks()) > 0) {
            foreach ($timetable->getBlocks() as $block) {
                if ($block->getTypeId() == 'calendar') {
                    $calendar = $this->findCalendar($block->getContent(), $calendarsSorted);
                    $stopSchedulesData = $this->navitia->getCalendarStopSchedulesByRoute(
                        $externalCoverageId,
                        $timetable->getExternalRouteId(),
                        $stopPointInstance->getExternalId(),
                        $block->getContent()
                    );
                    $calendar = $this->addSchedulesToCalendar(
                        $calendar,
                        $stopSchedulesData->stop_schedules
                    );
                    $calendar->schedules->additional_informations = $this->generateAdditionalInformations($calendar->schedules->additional_informations);
                    $calendar->notes = array_merge(
                        $stopSchedulesData->notes,
                        $this->generateExceptionsValues(
                            $stopSchedulesData->exceptions
                        )
                    );
                    $notesComputed = $this->computeNotes(
                        $notesComputed,
                        $calendar,
                        $timetable->getLineConfig()->getSeason(),
                        $layout->aggregatesNotes()
                    );
                    $calendarsFiltered[$calendar->id] = $calendar;
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
}
