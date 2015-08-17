<?php

namespace CanalTP\MttBundle\Entity;

/**
 * AbstractEntity
 */
abstract class AbstractEntity
{
    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $updated
     */
    protected $updated;

    /**
     * Get creation date
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated date
     *
     * @param \Datetime $date The updated date
     */
    public function setUpdated(\Datetime $date)
    {
        $this->updated = $date;
    }

    /**
     * Get updated date
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function __clone()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }
}
