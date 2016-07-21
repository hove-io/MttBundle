<?php

namespace CanalTP\MttBundle\Calendar;

use CanalTP\MttBundle\CsvModelInterface;
use CanalTP\MttBundle\Entity\Calendar;

class GridNetworksAndLinesCsv implements CsvModelInterface
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
        return ['grid_calendar_id', 'network_id', 'line_code'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        $rows = [];
        foreach ($this->calendars as $calendar) {
            foreach ($calendar->getCustomer()->getPerimeters() as $perimeter) {
                $rows[] = [
                    $calendar->getId(),
                    $perimeter->getExternalNetworkId(),
                    ''
                ];
            }
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return 'grid_rel_calendar_to_network_and_line.txt';
    }
}
