<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\LayoutConfig;

class ScheduleExtension extends \Twig_Extension
{
    private $ascii_start = 97;

    private function findNoteIndex($noteId, $notes, $calendar)
    {
        foreach ($notes as $index => $note) {
            if ($note->id == $noteId && ($calendar == false || $note->calendarId == $calendar->id)) {
                return $index;
            }
        }

        return false;
    }

    public function getFilters()
    {
        return array(
            'schedule'      => new \Twig_Filter_Method($this, 'scheduleFilter'),
            'footnote'      => new \Twig_Filter_Method($this, 'footnoteFilter'),
            'calendarMax'   => new \Twig_Filter_Method($this, 'calendarMax', array("is_safe" => array("html"))),
            'findCalendar'  => new \Twig_Filter_Method($this, 'findCalendar'),
            'formatHour'    => new \Twig_Filter_Method($this, 'formatHour'),
        );
    }

    public function scheduleFilter($journey, $notes, $notesType = LayoutConfig::NOTES_TYPE_EXPONENT, $calendar = false)
    {
        $value = date('i', $journey->date_time->getTimestamp());
        if (count($journey->links) > 0) {
            foreach ($journey->links as $link) {
                if ($link->type == "notes" || $link->type == "exceptions") {
                    if ($notesType == LayoutConfig::NOTES_TYPE_COLOR) {
                        $value = '<span style="background-color: ' . $notes[$this->findNoteIndex($link->id, $notes, $calendar)]->color . '">' . $value . '</span>';
                    } else {
                        $value .= '<sup>' . $this->footnoteFilter(
                            $this->findNoteIndex($link->id, $notes, $calendar)
                        ) . '</sup>';
                    }
                }
            }
        }

        return $value;
    }

    public function footnoteFilter($index)
    {
        return $index === false ? '' : chr($this->ascii_start + $index);
    }

    public function calendarMax($calendar, $min = 12)
    {
        $max = 0;
        if (isset($calendar->schedules->date_times)) {
            foreach ($calendar->schedules->date_times as $HourDateTime) {
                if (count($HourDateTime) > $max) {
                    $max = count($HourDateTime);
                }
            }
        }

        return $max > $min ? $max : $min;
    }

    /**
     * find calendar by id and routeid
     *
     * @param $array
     * @param string $calendarId calendar id
     * @param string $routeId route id
     *
     * @return array | false
     */
    public function findCalendar($array, $calendarId, $routeId) {

        if (isset($array['routes'][$routeId]['calendars'][$calendarId])) {
                return $array['routes'][$routeId]['calendars'][$calendarId];
        }

        return false;
    }

    public function formatHour($hour) {

        if (is_null($hour)) return '-';
        return substr($hour,0,2) . ':' . substr($hour,2,2);
    }

    public function getName()
    {
        return 'schedule_extension';
    }
}
