<?php

namespace CanalTP\MttBundle\Calendar;

use CanalTP\MttBundle\CsvModelInterface;
use CanalTP\MttBundle\Entity\Calendar;

class GridCalendarsCsv implements CsvModelInterface
{
    private $calendars;

    /**
     * GridCalendarCsv constructor.
     * @param Calendar[] $calendars
     */
    public function __construct(array $calendars)
    {
        $this->calendars = $calendars;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return [
            'grid_calendar_id',
            'name',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        $rows = [];
        foreach ($this->calendars as $calendar) {
            $rows[] = [
                $calendar->getId(),
                $calendar->getTitle(),
                (int) $calendar->isCirculateTheDay(0),
                (int) $calendar->isCirculateTheDay(1),
                (int) $calendar->isCirculateTheDay(2),
                (int) $calendar->isCirculateTheDay(3),
                (int) $calendar->isCirculateTheDay(4),
                (int) $calendar->isCirculateTheDay(5),
                (int) $calendar->isCirculateTheDay(6),
            ];
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return 'grid_calendars.txt';
    }
}
