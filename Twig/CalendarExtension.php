<?php

namespace CanalTP\MttBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

class CalendarExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
          'calendarRange'     => new \Twig_Filter_Method($this, 'calendarRange'),
          'hourIndex'         => new \Twig_Filter_Method($this, 'hourIndex'),
          'isWithinFrequency' => new \Twig_Filter_Method($this, 'isWithinFrequency'),
          'toWeekDays'        => new \Twig_Filter_Method($this, 'toWeekDays'),
        ];
    }

    /**
     * returns index of the given value in array
     */
    private function getIndex($searchedHour, $hours)
    {
        foreach ($hours as $index => $hour) {
            if ($hour == $searchedHour) {
                return $index;
            }
        }
    }

    /**
     * Based on Layout configuration, returns an array used to render a calendar
     * also used to determine the index of a hour value (ex: when validating forms)
     *
     * @param $layout Layout entity object
     */
    public function calendarRange($layout)
    {
        $rangeConfig = [
          'start' => $layout->getCalendarStart(),
          'end' => $layout->getCalendarEnd()
        ];
        $elements = [];
        $diurnalMax = $rangeConfig['end'] > $rangeConfig['start'] ? $rangeConfig['end'] : 23;
        for ($i = $rangeConfig['start']; $i <= $diurnalMax; $i++) {
            $elements[] = $i;
        }
        if ($diurnalMax != $rangeConfig['end']) {
            for ($i = 0; $i <= $rangeConfig['end']; $i++) {
                $elements[] = $i;
            }
        }

        return $elements;
    }

    /**
     * Check if an hour is under a frequency frame and should not be displayed.
     *
     * @param $hour String hour value in string
     * @param $frequencies Array of frequency entities
     * @param $hours array as returned by this->calendarRange
     */
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

    /**
     * Returns the index values array for a given Datetime
     *
     * @param $datetime DateTime object
     * @param $hours Array
     */
    public function hourIndex($datetime, $hours)
    {
        $searchedHour = date('G', $datetime->getTimestamp());

        return $this->getIndex($searchedHour, $hours);
    }

    /**
     * Converts weekly pattern to week days string
     *
     * @param type $pattern
     *
     * @return string List of weekdays separated  by comma
     */
    public function toWeekDays($pattern)
    {
        $weekDays = [
          $this->translator->trans('calendar.weekdays.monday', [], 'default'),
          $this->translator->trans('calendar.weekdays.tuesday', [], 'default'),
          $this->translator->trans('calendar.weekdays.wednesday', [], 'default'),
          $this->translator->trans('calendar.weekdays.thursday', [], 'default'),
          $this->translator->trans('calendar.weekdays.friday', [], 'default'),
          $this->translator->trans('calendar.weekdays.saturday', [], 'default'),
          $this->translator->trans('calendar.weekdays.sunday', [], 'default'),
        ];

        $days = str_split($pattern);
        $formatedDays = [];

        foreach (str_split($pattern) as $key => $day) {
            if (array_key_exists($day, $weekDays) && intval($day) === 1) {
                $formatedDays[] = $weekDays[$key];
            }
        }

        return join(', ', $formatedDays);
    }

    public function getName()
    {
        return 'calendar_extension';
    }
}
