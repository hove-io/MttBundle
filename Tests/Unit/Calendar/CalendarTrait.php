<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\MttBundle\Entity\Calendar;

trait CalendarTrait
{
    private function makeCalendar($id, $title, $starDate, $endDate, $weeklyPattern)
    {
        $calendar = new Calendar();
        $refProperty = new \ReflectionProperty(get_class($calendar), 'id');
        $refProperty->setAccessible(true);
        $refProperty->setValue($calendar, $id);

        $calendar->setTitle($title);
        $calendar->setStartDate(\DateTime::createFromFormat('Y-m-d', $starDate));
        $calendar->setEndDate(\DateTime::createFromFormat('Y-m-d', $endDate));
        $calendar->setWeeklyPattern($weeklyPattern);

        return $calendar;
    }
}
