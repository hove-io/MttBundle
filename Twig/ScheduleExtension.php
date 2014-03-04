<?php

namespace CanalTP\MttBundle\Twig;

class ScheduleExtension extends \Twig_Extension
{
    private $ascii_start = 97;

    private function findNoteIndex($noteId, $notes)
    {
        foreach ($notes as $index => $note) {
            if ($note->id == $noteId) {
                return $index;
            }
        }
    }

    public function getFilters()
    {
        return array(
            'schedule'      => new \Twig_Filter_Method($this, 'scheduleFilter'),
            'footnote'      => new \Twig_Filter_Method($this, 'footnoteFilter'),
            'calendarMax'   => new \Twig_Filter_Method($this, 'calendarMax'),
        );
    }

    public function scheduleFilter($journey, $notes, $mode = 'letter', $colors = false)
    {
        $value = date('i', $journey->date_time->getTimestamp());
        if (count($journey->links) > 0) {
            foreach ($journey->links as $link) {
                if ($link->type == "note") {
                    $value .= $this->footnoteFilter($this->findNoteIndex($link->id, $notes), $mode, $colors);
                }
            }
        }

        return $value;
    }

    public function footnoteFilter($index, $mode = 'letter', $colors = false)
    {
        return chr($this->ascii_start + $index);
    }

    public function calendarMax($calendar, $min = 11)
    {
        $max = 0;
        foreach($calendar->schedules->date_times as $HourDateTime){
            if (count($HourDateTime) > $max) {
                $max = count($HourDateTime);
            }
        }
        return $max > $min ? $max : $min;
    }

    public function getName()
    {
        return 'schedule_extension';
    }
}
