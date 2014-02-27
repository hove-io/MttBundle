<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

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

    private function findCalendar($calendarId, $calendars)
    {
        foreach ($calendars as $calendar){
            if ($calendar->id == $calendarId) {
                return $calendar;
            }
        }
        throw new Exception(
            $this->translator('services.calendar_manager.calendar_in_block_not_found'), 
            array('%calendarId%' => $calendarId), 
            'exceptions'
        );
    }
    
    public function getCalendars($externalCoverageId, $timetable, $stopPointInstance)
    {
        if (empty($stopPointInstance))
        {
            return array(
                'calendars' => $this->getCalendarsForRoute($externalCoverageId, $timetable->getExternalRouteId()),
                'notes'     => array()
            );
        }
        else
        {
            return $this->getCalendarsForStopPointTimetable($externalCoverageId, $timetable, $stopPointInstance);
        }
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
    public function getCalendarsForStopPointTimetable($externalCoverageId, $timetable, $stopPointInstance)
    {
        $calendarsSorted = array();
        $notesComputed = array();
        // calendar blocks are defined on route/timetable level
        if (count($timetable->getBlocks()) > 0) {
            $calendarsData = $this->navitia->getStopPointCalendarsData(
                $externalCoverageId, 
                $timetable->getExternalRouteId(), 
                $stopPointInstance->getExternalId()
            );
            foreach ($timetable->getBlocks() as $block){
                if ($block->getTypeId() == 'calendar') {
                    $calendar = $this->findCalendar($block->getContent(), $calendarsData->calendars);
                    $stopSchedulesData = $this->navitia->getCalendarStopSchedules(
                        $externalCoverageId,
                        $timetable->getExternalRouteId(),
                        $stopPointInstance->getExternalId(),
                        $block->getContent()
                    );
                    //make it easier for template
                    $calendar->week_pattern = (array) $calendar->week_pattern;
                    $calendar->schedules = $stopSchedulesData->stop_schedules;
                    $calendar->schedules->date_times = $this->prepareDateTimes($calendar->schedules->date_times);
                    $calendarsSorted[$calendar->id] = $calendar;
                    //compute notes for the current timetable
                    $notesComputed = $this->computeNotes($notesComputed, $stopSchedulesData->notes);
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
    public function getCalendarsForRoute($externalCoverageId, $externalRouteId)
    {
        $calendarsData = $this->navitia->getRouteCalendars($externalCoverageId, $externalRouteId);
        $calendarsSorted = array();
        foreach ($calendarsData->calendars as $calendar) {
            //make it easier for template
            $calendarsSorted[$calendar->id] = $calendar;
        }

        return $calendarsSorted;
    }
}
