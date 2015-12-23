<?php

/**
 * Description of BlockManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class BlockManager
{
    private $repository = null;
    private $om = null;
    private $mediaManager = null;
    private $calendarManager = null;

    public function __construct(ObjectManager $om, MediaManager $mediaManager, CalendarManager $calendarManager)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->calendarManager = $calendarManager;
        $this->repo = $om->getRepository('CanalTPMttBundle:Block');
    }

    public function findBlock($blockId)
    {
        return $this->repo->find($blockId);
    }

    /**
     * Return Block
     *
     * @param  Object $block
     * @param  Object $destStopTimetable
     * @param  Object $destStopPoint
     * @return block
     */
    public function copy($block, $destStopTimetable)
    {
        $destSeason = $destStopTimetable->getLineConfig()->getSeason();
        if ($block->isCalendar() AND !$this->calendarManager->isIncluded($block->getContent(), $destSeason)) {
            return false;
        }
        $blockCloned = clone $block;
        $blockCloned->setStopTimetable($destStopTimetable);

        if ($block->isImg()) {
            $this->mediaManager->copy($block, $blockCloned, $destStopTimetable);
        }

        $this->om->persist($blockCloned);

        return $blockCloned;
    }
}
