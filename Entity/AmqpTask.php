<?php

namespace CanalTP\MttBundle\Entity;

/**
 * AmqpTask
 */
class AmqpTask extends AbstractEntity
{
    const SEASON_PDF_GENERATION_TYPE = 1;
    const DISTRIBUTION_LIST_PDF_GENERATION_TYPE = 2;
    const AREA_PDF_GENERATION_TYPE = 3;

    const CANCELED_STATUS = 0;
    const LAUNCHED_STATUS = 1;
    const COMPLETED_STATUS = 2;
    const ERROR_STATUS = 3;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $typeId;

    /**
     * @var integer
     */
    private $objectId;

    /**
     * @var boolean
     */
    private $status = self::LAUNCHED_STATUS;

    /**
     * @var boolean
     */
    private $jobsPublished;

    /**
     * @var \DateTime
     */
    private $completedAt;

    /**
     * @var Object
     */
    private $perimeter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $amqpAcks;

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
     * Set typeId
     *
     * @param  string   $typeId
     * @return AmqpTask
     */
    public function setTypeId($typeId)
    {
        if (!in_array($typeId, array(self::SEASON_PDF_GENERATION_TYPE, self::AREA_PDF_GENERATION_TYPE))) {
            throw new \InvalidArgumentException("Invalid typeId");
        }
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set objectId
     *
     * @param  integer  $objectId
     * @return AmqpTask
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set completed
     *
     * @param  boolean  $completed
     * @return AmqpTask
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set jobsPublished
     *
     * @param  integer  $jobsPublished
     * @return AmqpTask
     */
    public function setJobsPublished($jobsPublished)
    {
        $this->jobsPublished = $jobsPublished;

        return $this;
    }

    /**
     * Get jobsPublished
     *
     * @return integer
     */
    public function getJobsPublished()
    {
        return $this->jobsPublished;
    }

    /**
     * Set completedAt
     *
     * @param  \DateTime $completedAt
     * @return AmqpTask
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get completedAt
     *
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * Get Object
     *
     * @return Network
     */
    public function getPerimeter()
    {
        return $this->perimeter;
    }

    /**
     * Set Object
     *
     * @return Season
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;

        return ($this);
    }

    /**
     * Get Object
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAmqpAcks()
    {
        return $this->amqpAcks;
    }

    /**
     * Set Object
     *
     * @return amqp task
     */
    public function setAmqpAcks($amqpAcks)
    {
        $this->amqpAcks = $amqpAcks;

        return ($this);
    }

    /**
     * Get Options
     *
     * @return \Array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set Object
     *
     * @return amqp task
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return ($this);
    }

    public function isCompleted()
    {
        return $this->status == self::COMPLETED_STATUS;
    }

    public function isUnderProgress()
    {
        return $this->status == self::LAUNCHED_STATUS;
    }

    public function isCanceled()
    {
        return $this->status == self::CANCELED_STATUS;
    }

    public function complete()
    {
        if ($this->isUnderProgress()) {
            $this->status = self::COMPLETED_STATUS;
        }

        return $this;
    }

    public function cancel()
    {
        $this->status = self::CANCELED_STATUS;

        return $this;
    }

    public function fail()
    {
        $this->status = self::ERROR_STATUS;

        return $this;
    }
}
