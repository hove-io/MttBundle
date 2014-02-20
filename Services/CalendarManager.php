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
            $hour = date('H', $parsedDateTime->date_time->getTimestamp());
            if (!isset($sortedDateTimes[$hour])) {
                $sortedDateTimes[$hour] = array();
            }
            $sortedDateTimes[$hour][] = $parsedDateTime;
        }

        return $sortedDateTimes;
    }
    /**
     * Returns Calendars for a stop point and a route
     * Datetimes are parsed and response formatted for template
     *
     * @param String $externalCoverageId
     * @param String $externalRouteId
     * @param String $externalStopPointId
     *
     * @return object
     */
    public function getCalendarsAndSchedules($externalCoverageId, $externalRouteId, $externalStopPointId)
    {
        $calendarsData = $this->navitia->getStopPointCalendarsData($externalCoverageId, $externalRouteId, $externalStopPointId);

        foreach ($calendarsData->calendars as &$calendar) {
            //make it easier for template
            $calendar->week_pattern = (array) $calendar->week_pattern;
            $calendar->schedules = $this->navitia->getCalendarStopSchedules($externalCoverageId, $externalRouteId, $externalStopPointId, $calendar->id);
            $calendar->schedules->date_times = $this->prepareDateTimes($calendar->schedules->date_times);
        }

        return $calendarsData;
    }
}
