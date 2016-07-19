<?php

namespace CanalTP\MttBundle\Entity;

use CanalTP\SamCoreBundle\Entity\CustomerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Calendar
 */
class Calendar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var string
     */
    private $weeklyPattern;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Calendar
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return Calendar
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return Calendar
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeeklyPattern()
    {
        return $this->weeklyPattern;
    }

    /**
     * @param string $weeklyPattern
     *
     * @return Calendar
     */
    public function setWeeklyPattern($weeklyPattern)
    {
        $this->weeklyPattern = $weeklyPattern;

        return $this;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return Calendar
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Validates that start date is less than end date
     *
     * @return boolean
     */
    public function isDatesValid()
    {
        if ($this->startDate >= $this->endDate) {
            return false;
        }

        return ($this->endDate->diff($this->startDate)->days > 0);
    }

    /**
     * @param int $numericDayOfTheWeek Numeric day of the week (0 to 6)
     *
     * @return bool
     */
    public function isCirculateTheDay($numericDayOfTheWeek)
    {
        if (!isset($this->weeklyPattern[$numericDayOfTheWeek])) {
            throw new \OutOfBoundsException(sprintf('%d is not a numeric day of the week. It should be contained in 0 to 6'));
        }

        return (bool) $this->weeklyPattern[$numericDayOfTheWeek];
    }
}
