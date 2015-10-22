<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Class LineTimetable
 */
class LineTimetable extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var LineConfig
     */
    private $lineConfig;

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
     * Set lineConfig
     *
     * @param LineConfig $lineConfig
     * @return LineTimetable
     */
    public function setLineConfig(LineConfig $lineConfig = null)
    {
        $this->lineConfig = $lineConfig;

        return $this;
    }

    /**
     * Get lineConfig
     *
     * @return LineConfig
     */
    public function getLineConfig()
    {
        return $this->lineConfig;
    }
}
