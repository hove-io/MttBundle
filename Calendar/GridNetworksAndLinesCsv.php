<?php

namespace CanalTP\MttBundle\Calendar;

use CanalTP\MttBundle\CsvModelInterface;
use CanalTP\MttBundle\Entity\Calendar;

class GridNetworksAndLinesCsv implements CsvModelInterface
{
    private $calendars;
    private $networks;

    /**
     * GridCalendarCsv constructor.
     * @param Calendar[] $calendars
     */
    public function __construct(array $calendars, array $networks)
    {
        $this->calendars = $calendars;
        $this->networks = $networks;
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
            foreach ($this->networks as $network) {
                $rows[] = [
                    $calendar->getId(),
                    $network,
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
