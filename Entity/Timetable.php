<?php

namespace CanalTP\MttBundle\Entity;

use \Doctrine\Common\Collections\Collection;
use \Doctrine\Common\Collections\Criteria;

/**
 * Class Timetable
 */
class Timetable extends AbstractEntity
{
    const STOP_TYPE = 'stop';
    const LINE_TYPE = 'line';
    const STOP_MANAGER = 'canal_tp_mtt.stop_timetable_manager';
    const LINE_MANAGER = 'canal_tp_mtt.line_timetable_manager';

    public static $managers = array(
        self::STOP_TYPE => self::STOP_MANAGER,
        self::LINE_TYPE => self::LINE_MANAGER
    );

    /**
     * @var Collection
     */
    protected $blocks;

    /**
     * @var LineConfig
     */
    protected $lineConfig;

    /**
     * Get lineConfig
     *
     * @return LineConfig
     */
    public function getLineConfig()
    {
        return $this->lineConfig;
    }

    /**
     * Set lineConfig
     *
     * @param LineConfig $lineConfig
     * @return Timetable
     */
    public function setLineConfig(LineConfig $lineConfig)
    {
        $this->lineConfig = $lineConfig;

        return $this;
    }

    /**
     * Get blocks
     *
     * @return Collection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Set blocks
     *
     * @param Collection $blocks
     * @return Timetable
     */
    public function setBlocks(Collection $blocks)
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Get block by domId
     *
     * @param string $domId
     * @return Block|null
     */
    public function getBlockByDomId($domId)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('domId', $domId))
            ->setMaxResults(1)
        ;

        $blocks = $this->blocks->matching($criteria);

        return $blocks->isEmpty() ? null : $blocks->first();
    }

    /**
     * Is locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        return $this->getLineConfig()->isLocked();
    }
}
