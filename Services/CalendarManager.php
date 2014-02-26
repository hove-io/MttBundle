<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

class CalendarManager
{
    private $navitia = null;
    private $computedNotesId = array();

    public function __construct(Navitia $navitia)
    {
        $this->navitia = $navitia;
    }

    private function parseDateTimes($datetimes)
    {
        foreach ($datetimes as &$datetime) {
            // TODO: period format not defined by the RO Team
            // if (substr($datetime->date_time, 0 , 1) == "P")
                // $datetime->date_time = new \DateInterval($datetime->date_time);
            // else
                $datetime->date_time = new \DateTime($datetime->date_time);
        }

        return $datetimes;
    }

    private function prepareDateTimes($datetimes)
    {
        $parsedDateTimes = $this->parseDateTimes($datetimes);
        $sortedDateTimes = array();
        foreach ($parsedDateTimes as $parsedDateTime) {
            $hour = date('G', $parsedDateTime->date_time->getTimestamp());
            if (!isset($sortedDateTimes[$hour])) {
                $sortedDateTimes[$hour] = array();
            }
            $sortedDateTimes[$hour][] = $parsedDateTime;
        }

        return $sortedDateTimes;
    }

    private function computeNotes($notes, $notesToAdd)
    {
        foreach ($notesToAdd as $note) {
            if (!in_array($note->id, $this->computedNotesId)) {
                $this->computedNotesId[] = $note->id;
                $notes[] = $note;
            }
        }

        return $notes;
    }

    /**
     * Returns Calendars enhanced with schedules for a stop point and a route
     * Datetimes are parsed and response formatted for template
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
     * @param String $externalStopPointId
     *
     * @return object
     */
    public function getCalendarsForStopPoint($externalCoverageId, $externalRouteId, $externalStopPointId)
    {
        $calendarsData = $this->navitia->getStopPointCalendarsData($externalCoverageId, $externalRouteId, $externalStopPointId);
        $calendarsSorted = array();
        $notesComputed = array();

        foreach ($calendarsData->calendars as $calendar) {
            //make it easier for template
            $calendar->week_pattern = (array) $calendar->week_pattern;
            $stopSchedulesData = $this->navitia->getCalendarStopSchedules($externalCoverageId, $externalRouteId, $externalStopPointId, $calendar->id);
            $calendar->schedules = $stopSchedulesData->stop_schedules;
            $calendar->schedules->date_times = $this->prepareDateTimes($calendar->schedules->date_times);
            $calendarsSorted[$calendar->id] = $calendar;
            // notes
            $calendar->notes = $stopSchedulesData->notes;
            // compute notes for the timetable
            $notesComputed = $this->computeNotes($notesComputed, $stopSchedulesData->notes);
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
    public function getCalendarsForRoute($externalCoverageId, $externalRouteId)
    {
        $calendarsData = $this->navitia->getRouteCalendars($externalCoverageId, $externalRouteId);
        $calendarsSorted = array();
        foreach ($calendarsData->calendars as $calendar) {
            //make it easier for template
            $calendarsSorted[$calendar->id] = $calendar;
        }

        return array('calendars' => $calendarsSorted, 'notes' => $calendarsData->notes);
    }
}
