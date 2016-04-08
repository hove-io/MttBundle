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

        if (count($journey->links) < 1) {
            return $value;
        }

        foreach ($journey->links as $link) {
            if ($this->linkIsDecorable($link)) {
                $noteIndex = $this->findNoteIndex($link->id, $notes, $calendar);
                $this->decorateMinute($notesType, $notes, $noteIndex, $value);
            }
        }

        return $value;
    }

    /**
     * Decorates minute string with html elements
     *
     * (Adds superclase or/and background color)
     *
     * @param string  $notesType
     * @param array   $notes
     * @param integer $noteIndex
     * @param string  $minute
     *
     * @return string
     */
    private function decorateMinute($notesType, $notes, $noteIndex, &$minute)
    {
        if ($notesType == LayoutConfig::NOTES_TYPE_COLOR) {
            $minute = $this->decorateMinuteWithColor($notes, $noteIndex, $minute);
        } else {
            $minute.= $this->addFootNote($noteIndex);
        }

        return $minute;
    }

    /**
     * Tests that $link is decorable
     *
     * (type property should be notes or exceptions)
     *
     * @param object $link
     *
     * @return boolean
     */
    private function linkIsDecorable($link)
    {
        if(!is_object($link) || !property_exists($link, 'type')) {
            return false;
        }

        return $link->type == 'notes' || $link->type == 'exceptions';
    }

    /**
     * Adds superscript element to $value if $index is not false
     *
     * @param integer $noteIndex
     *
     * @return string
     */
    private function addFootNote($noteIndex)
    {
        $footNote = $this->footnoteFilter($noteIndex);
        if(empty($footNote)) {
           return '';
        }

        return sprintf('<sup>%s</sup>', $footNote);
    }

    /**
     * Adds background color to the $value if color could be found in $notes
     *
     * @param array $notes
     * @param integer $noteIndex
     * @param String $value
     *
     * @return String
     */
    private function decorateMinuteWithColor($notes, $noteIndex, $value)
    {
        if(!is_integer($noteIndex)) {
            return $value;
        }

        if(!array_key_exists($noteIndex, $notes)) {
            return $value;
        }

        $noteObj = $notes[$noteIndex];
        if(!is_object($noteObj) || !property_exists($noteObj, 'color')) {
            return $value;
        }

        return sprintf('<span style="background-color: %s">%s</span>', $noteObj->color, $value);
    }

    public function footnoteFilter($index)
    {
        return $index === false ? '' : chr($this->ascii_start + $index);
    }

    public function calendarMax($calendar, $min = 12)
    {
        if (!isset($calendar->schedules->date_times)) {
            return max([0, $min]);
        }

        $max = 0;
        foreach ($calendar->schedules->date_times as $HourDateTime) {
            $dateTimesNb = count($HourDateTime);
            if ($dateTimesNb > $max) {
                $max = $dateTimesNb;
            }
        }

        return max([$min, $max]);
    }

    public function getName()
    {
        return 'schedule_extension';
    }
}
