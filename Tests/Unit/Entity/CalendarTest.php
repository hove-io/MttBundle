<?php

namespace CanalTP\MttBundle\Tests\Unit\Entity;

use CanalTP\MttBundle\Entity\Calendar;
use Symfony\Component\Validator\Validation;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to the validation file
     *
     * @var string
     */
    private static $validatorPath;

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * Initializes validation file path
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$validatorPath = realpath(__DIR__.'/../../../').'/Resources/config/validation.yml';
    }

    /**
     * Initializes $calendar and $validator
     */
    protected function setUp()
    {
        if (version_compare(PHP_VERSION, '7.0.0') === 1 ) {
            $this->markTestSkipped();
        }

        $this->calendar = new Calendar();
        $this->calendar
            ->setTitle('CanalTP')
            ->setStartDate(new \DateTime('2016-07-08'))
            ->setEndDate(new \DateTime('2016-08-08'));

        $this->validator = Validation::createValidatorBuilder()
            ->addYamlMapping(self::$validatorPath)
            ->getValidator();
    }

    /**
     * Tests weekly pattern validity for calendar entity
     *
     * @dataProvider getPatterns
     */
    public function testWeeklyPatternValidity($pattern, $errorsNumber)
    {
        $this->calendar->setWeeklyPattern($pattern);

        $errors = $this->validator->validate($this->calendar);
        $this->assertCount($errorsNumber, $errors);
    }

    /**
     * Data provider with patterns
     *
     * @return array
     */
    public function getPatterns()
    {
        return [
          ['0100000', 0],
          ['1111111', 0],
          ['0000000', 1],
          ['010000', 1],
          ['111111', 1],
          ['000000', 1],
        ];
    }

    /**
     * Tests start date and end date validity
     *
     * @dataProvider getDates
     */
    public function testStartDateAndEndDateValidity(\DateTime $startDate, \DateTime $endDate, $errorsNumber)
    {
        $this->calendar->setStartDate($startDate);
        $this->calendar->setEndDate($endDate);

        $errors = $this->validator->validate($this->calendar);
        $this->assertCount($errorsNumber, $errors);
    }


    /**
     * Data provider with dates
     *
     * @return array
     */
    public function getDates()
    {
        return [
          [new \DateTime('2016-07-08'), new \DateTime('2016-08-08'), 0],
          [new \DateTime('2016-08-07'), new \DateTime('2016-08-08'), 0],
          [new \DateTime('2016-07-08'), new \DateTime('2016-06-08'), 1],
          [new \DateTime('2016-06-08'), new \DateTime('2016-06-08'), 1],
        ];
    }

   /**
     * Tests that title is required and has a correct length
     *
     * @dataProvider getTitles
     */
    public function testTitleValidity($title, $errorsNumber)
    {
        $this->calendar->setTitle($title);

        $errors = $this->validator->validate($this->calendar);
        $this->assertCount($errorsNumber, $errors);
    }

    /**
     * Data provider with dates
     *
     * @return array
     */
    public function getTitles()
    {
        return [
          ['', 1],
          [str_repeat('a', 256), 1],
          [str_repeat('a', 254), 0],
        ];
    }
}
