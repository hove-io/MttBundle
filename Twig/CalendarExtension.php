<?php

namespace CanalTP\MttBundle\Twig;

class CalendarExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'calendarRange'     => new \Twig_Filter_Method($this, 'calendarRange'),
            'hourIndex'         => new \Twig_Filter_Method($this, 'hourIndex'),
        );
    }

    public function calendarRange($rangeConfig)
    {
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
    
    public function hourIndex($datetime, $hours)
    {
        $searchedHour = date('G', $datetime->getTimestamp());
        foreach($hours as $index => $hour) {
            if ($hour == $searchedHour)
                return $index;
        }
    }

    public function getName()
    {
        return 'calendar_extension';
    }
}
