<?php

namespace CanalTP\MttBundle\Twig;

class CalendarExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'calendarRange'     => new \Twig_Filter_Method($this, 'calendarRange'),
            'hourIndex'         => new \Twig_Filter_Method($this, 'hourIndex'),
            'isWithinFrequency' => new \Twig_Filter_Method($this, 'isWithinFrequency'),
        );
    }

    private function getIndex($searchedHour, $hours)
    {
        foreach ($hours as $index => $hour) {
            if ($hour == $searchedHour)
                return $index;
        }
    }

    public function calendarRange($layout)
    {
        $rangeConfig = array('start' => $layout->getCalendarStart(), 'end' => $layout->getCalendarEnd());
        $elements = array();
        $diurnalMax = $rangeConfig['end'] > $rangeConfig['start'] ? $rangeConfig['end'] : 23;
        for ($i = $rangeConfig['start'];$i <= $diurnalMax;$i++) {
            $elements[] = $i;
        }
        if ($diurnalMax != $rangeConfig['end']) {
            for ($i = 0;$i <= $rangeConfig['end'];$i++) {
                $elements[] = $i;
            }
        }

        return $elements;
    }

    public function isWithinFrequency($hour, $frequencies, $hours)
    {
        $hourIndex = $this->getIndex($hour, $hours);
        foreach ($frequencies as $frequency) {
            $startIndex = $this->getIndex(date('G', $frequency->getStartTime()->getTimestamp()), $hours);
            $endIndex = $this->getIndex(date('G', $frequency->getEndTime()->getTimestamp()), $hours);
            if ($hourIndex >= $startIndex && $hourIndex <= $endIndex) {
                return true;
            }
        }

        return false;
    }

    public function hourIndex($datetime, $hours)
    {
        $searchedHour = date('G', $datetime->getTimestamp());

        return $this->getIndex($searchedHour, $hours);
    }

    public function getName()
    {
        return 'calendar_extension';
    }
}
