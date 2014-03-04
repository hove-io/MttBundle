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
            // TODO: period format not defined by the RO Team yet
            // if (substr($datetime->date_time, 0 , 1) == "P")
                // $datetime->date_time = new \DateInterval($datetime->date_time);
            // else
                $datetime->date_time = new \DateTime($datetime->date_time);
        }

        return $datetimes;
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
            $sortedDateTimes[$hour][] = $parsedDateTime;
        }
        return $sortedDateTimes;
    }

    /**
     * gather notes and ensure these notes are unique. (based on Navitia ID)
     */
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
     * find a calendar or throws an exception if a calendar is not found
     */
    private function findCalendar($calendarId, $calendars)
    {
        if (isset($calendars[$calendarId])) {
            return $calendars[$calendarId];
        } else {
            throw new Exception(
                $this->translator('services.calendar_manager.calendar_in_block_not_found'), 
                array('%calendarId%' => $calendarId), 
                'exceptions'
            );
        }
    }
    
    /**
     * Index calendars by Id and cast week_patterns into array for templates
     */
    private function sortCalendars($calendars)
    {
        $calendarsSorted = array();
        foreach ($calendars as $calendar){
            $calendarsSorted[$calendar->id] = $calendar;
            $calendarsSorted[$calendar->id]->week_pattern = (array) $calendarsSorted[$calendar->id]->week_pattern;
        }
        return $calendarsSorted;
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
    
    public function getCalendars($externalCoverageId, $timetable, $stopPointInstance = false)
    {
        if (empty($stopPointInstance))
        {
            return array(
                'calendars' => $this->getCalendarsForRoute(
                    $externalCoverageId, 
                    $timetable->getExternalRouteId()
                ),
                'notes'     => array()
            );
        }
        else
        {
            return $this->getCalendarsForStopPointAndTimetable($externalCoverageId, $timetable, $stopPointInstance);
        }
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
        $notesComputed = array();
        $calendarsData = $this->navitia->getStopPointCalendarsData(
            $externalCoverageId, 
            $externalRouteId,
            $externalStopPointId
        );
        $calendarsSorted = $this->sortCalendars($calendarsData->calendars);
        foreach ($calendarsSorted as $calendar) {
            $stopSchedulesData = $this->navitia->getCalendarStopSchedules(
                $externalCoverageId,
                $externalRouteId,
                $externalStopPointId,
                $calendar->id
            );
            $calendar = $this->addSchedulesToCalendar($calendar, $stopSchedulesData->stop_schedules);
            $calendar->notes = $stopSchedulesData->notes;
            $calendarsSorted[$calendar->id] = $calendar;
            //compute notes for the current timetable
            $notesComputed = $this->computeNotes($notesComputed, $stopSchedulesData->notes);
        }
        return array('calendars' => $calendarsSorted, 'notes' => $notesComputed);
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
    public function getCalendarsForStopPointAndTimetable($externalCoverageId, $timetable, $stopPointInstance)
    {
        $notesComputed = array();
        $calendarsFiltered = array();
        $calendarsData = $this->navitia->getStopPointCalendarsData(
            $externalCoverageId, 
            $timetable->getExternalRouteId(), 
            $stopPointInstance->getExternalId()
        );
        $calendarsSorted = $this->sortCalendars($calendarsData->calendars);
        // calendar blocks are defined on route/timetable level
        if (count($timetable->getBlocks()) > 0) {
            foreach ($timetable->getBlocks() as $block){
                if ($block->getTypeId() == 'calendar') {
                    $calendar = $this->findCalendar($block->getContent(), $calendarsSorted);
                    $stopSchedulesData = $this->navitia->getCalendarStopSchedules(
                        $externalCoverageId,
                        $timetable->getExternalRouteId(),
                        $stopPointInstance->getExternalId(),
                        $block->getContent()
                    );
                    $calendar = $this->addSchedulesToCalendar($calendar, $stopSchedulesData->stop_schedules);
                    $calendarsFiltered[$calendar->id] = $calendar;
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
