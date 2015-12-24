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
            'cutCalendar'   => new \Twig_Filter_method($this, 'cutCalendar'),
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
     * Finding a calendar
     *
     * @param $array
     * @param string $calendarId
     * @param string $externalRouteId
     *
     * @return array | null
     */
    public function findCalendar($array, $calendarId, $externalRouteId) {
        if (isset($array['routes'][$externalRouteId]['calendars'][$calendarId])) {
            return $array['routes'][$externalRouteId]['calendars'][$calendarId];
        }

        return null;
    }

    /**
     * Cutting one-line calendar into multiple tables according to
     * a limited columns per table.
     *
     * @param mixed $calendar
     * @param integer $columnsLimit
     */
    public function cutCalendar($calendar, $columnsLimit)
    {
        $metaCalendar = array();

        $tablesNumber = ceil($calendar['columns']/$columnsLimit);
        if ($tablesNumber > 1) {
            for ($tableNumber = 0; $tableNumber < $tablesNumber; $tableNumber++) {
                $dataLength = 0;
                $index = 0;
                $stopTimesToCut = array();
                while ($dataLength < $columnsLimit && $index < count($calendar['metadata'])) {
                    $cut = false;
                    $metadatas = array_slice($calendar['metadata'], $index, 1, true);
                    foreach ($metadatas as $columnNumber => $metadata) {
                        if ($metadata['type'] === 'frequency') {
                            if ($dataLength + $metadata['colspan'] > $columnsLimit) {
                                $cut = true;
                            }
                            $dataLength += $metadata['colspan'];
                        } else {
                            $dataLength++;
                            $stopTimesToCut[] = $columnNumber;
                        }

                        if (!$cut) {
                            $metaCalendar[$tableNumber]['metadata'][$columnNumber] = $metadata;
                            $index++;
                        }
                    }
                }

                $calendar['metadata'] = array_slice($calendar['metadata'], $index, null, true);
                foreach ($calendar['stops'] as $stop) {
                    $stopTimes = array();
                    foreach ($stop['stopTimes'] as $columnNumber => $stopTime) {
                        if (in_array($columnNumber, $stopTimesToCut)) {
                            $stopTimes[$columnNumber] = $stopTime;
                        }
                    }

                    $metaCalendar[$tableNumber]['stops'][] = array(
                        'stopName' => $stop['stopName'],
                        'stopTimes' => $stopTimes
                    );
                }
            }
        } else {
            $metaCalendar[0] = $calendar;
        }

        return $metaCalendar;
    }


    public function getName()
    {
        return 'schedule_extension';
    }
}
