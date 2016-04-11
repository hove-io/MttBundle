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
        $minute = date('i', $journey->date_time->getTimestamp());

        if (count($journey->links) < 1) {
            return $minute;
        }

        $colors     = []; $exposents  = [];

        foreach ($journey->links as $link) {
            if (!$this->linkIsDecorable($link)) {
                continue;
            }

            $noteIndex = $this->findNoteIndex($link->id, $notes, $calendar);

            if ($notesType == LayoutConfig::NOTES_TYPE_COLOR) {
                $colors[]   = $this->getColor($notes, $noteIndex);
            }
            $exposents[]= $this->getExposent($noteIndex);

        }

        $colorizedMinute = $this->colorizeMminute($minute, $colors);
        return $this->addExposants($colorizedMinute, $exposents, $notesType);
    }

    /**
     * Retrieves color for note index
     *
     * @param array $notes
     * @param Integer $noteIndex
     *
     * @return string | Null
     */
    private function getColor(array $notes, $noteIndex)
    {
        if(!is_integer($noteIndex) || !array_key_exists($noteIndex, $notes)) {
            return null;
        }

        $noteObj = $notes[$noteIndex];
        if(!is_object($noteObj) || !property_exists($noteObj, 'color')) {
            return null;
        }

        return $noteObj->color;
    }

    /**
     * Retrieves exposent by note index
     *
     * @param Integer $noteIndex
     * @return Sting | Null
     */
    private function getExposent($noteIndex)
    {
        $footNote = $this->getFootNote($noteIndex);
        if(empty($footNote)) {
           return null;
        }

        return $footNote;
    }

    /**
     * Adds background color to the minute string
     *
     * @param string $minute
     * @param array $colors
     * @return String
     */
    private function colorizeMminute($minute = '', array $colors = [])
    {
        $cleanedColors = array_filter($colors);

        if(empty($cleanedColors)) {
            return $minute;
        }

        return sprintf('<span style="background-color: %s">%s</span>', current($cleanedColors), $minute);

    }

    /**
     * Adds exposents to the minute string if note type isnot color
     *
     * @param string $minute
     * @param array $exposents
     * @param type $notesType
     *
     * @return String
     */
    private function addExposants($minute = '', array $exposents = [], $notesType)
    {
        $cleanedExposents = array_filter($exposents);

        $exposantsNb = count($cleanedExposents);

        if($notesType == LayoutConfig::NOTES_TYPE_COLOR && $exposantsNb < 2) {
            return $minute;
        }

        array_walk($cleanedExposents, function(&$value) {
            $value = sprintf('<sup>%s</sup>', $value);
        });

        return $minute . join('', $cleanedExposents);
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

    private function getFootNote($index)
    {
        return $index === false ? '' : chr($this->ascii_start + $index);
    }

    public function footnoteFilter($index, $note, $notesType = LayoutConfig::NOTES_TYPE_EXPONENT)
    {

        if($index === false) {
            return '';
        }

        if(!is_object($note) || !property_exists($note, 'color')) {
            return $this->getFootNote($index);
        }

        if($notesType == LayoutConfig::NOTES_TYPE_EXPONENT) {
            return $this->getFootNote($index);
        }

        return sprintf('<span style="background-color: %s" class="label"> </span> ',$note->color);
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
