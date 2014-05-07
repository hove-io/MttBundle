<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AmqpTask
 */
class AmqpTask extends AbstractEntity
{
    const SEASON_PDF_GENERATION_TYPE = 1;
    const DISTRIBUTION_LIST_PDF_GENERATION_TYPE = 2;
    
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
    private $completed = false;

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
    private $network;


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
     * @param string $typeId
     * @return AmqpTask
     */
    public function setTypeId($typeId)
    {
        if (!in_array($typeId, array(self::SEASON_PDF_GENERATION_TYPE, self::DISTRIBUTION_LIST_PDF_GENERATION_TYPE))) {
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
     * @param integer $objectId
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
     * @param boolean $completed
     * @return AmqpTask
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean 
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set jobsPublished
     *
     * @param boolean $jobsPublished
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
     * @return boolean 
     */
    public function getJobsPublished()
    {
        return $this->jobsPublished;
    }

    /**
     * Set completedAt
     *
     * @param \DateTime $completedAt
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
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Set Object
     *
     * @return Season
     */
    public function setNetwork($network)
    {
        $this->network = $network;

        return ($this);
    }
}
