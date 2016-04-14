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

        $colors =  $exponents  = [];

        foreach ($journey->links as $link) {
            if (!$this->isLinkDecorable($link)) {
                continue;
            }

            $noteIndex = $this->findNoteIndex($link->id, $notes, $calendar);

            if ($notesType == LayoutConfig::NOTES_TYPE_COLOR) {
                $colors[] = $this->getColor($notes, $noteIndex);
            }
            $exponents[] = $this->getExposent($noteIndex);

        }

        $colorizedMinute = $this->colorizeMminute($minute, $colors);
        return $this->addExponents($colorizedMinute, $exponents, $notesType);
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
        if (!is_integer($noteIndex) || !array_key_exists($noteIndex, $notes)) {
            return null;
        }

        $noteObj = $notes[$noteIndex];
        if (!is_object($noteObj) || !property_exists($noteObj, 'color')) {
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
        if (empty($footNote)) {
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

        if (empty($cleanedColors)) {
            return $minute;
        }

        return sprintf('<span style="background-color: %s">%s</span>', current($cleanedColors), $minute);

    }

    /**
     * Adds exponents to the minute string if note type isnot color
     *
     * @param string $minute
     * @param array $exponents
     * @param mixed $notesType
     *
     * @return String
     */
    private function addExponents($minute = '', array $exponents = [], $notesType = null)
    {
        $cleanedExposents = array_filter($exponents);

        $exposantsNb = count($cleanedExposents);

        if ($notesType == LayoutConfig::NOTES_TYPE_COLOR && $exposantsNb < 2) {
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
    private function isLinkDecorable($link)
    {
        if (!is_object($link) || !property_exists($link, 'type')) {
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
        if ($index === false) {
            return '';
        }

        if (!is_object($note) || !property_exists($note, 'color')) {
            return $this->getFootNote($index);
        }

        if ($notesType == LayoutConfig::NOTES_TYPE_EXPONENT) {
            return $this->getFootNote($index);
        }

        return sprintf('<span style="background-color: %s" class="label">&nbsp;</span>', $note->color);
    }

    public function calendarMax($calendar, $min = 12)
    {
        if (!isset($calendar->schedules->date_times) || count($calendar->schedules->date_times) == 0) {
            return max([0, $min]);
        }

        $max = 0;
        foreach ($calendar->schedules->date_times as $hourDateTime) {
            $max = max([$max, count($hourDateTime)]);
        }

        return max([$min, $max]);
    }

    public function getName()
    {
        return 'schedule_extension';
    }
}
