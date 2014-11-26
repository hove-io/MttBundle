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

    public function getName()
    {
        return 'schedule_extension';
    }
}
