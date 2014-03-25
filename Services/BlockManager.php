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

    public function __construct(ObjectManager $om, MediaManager $mediaManager)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->repo = $om->getRepository('CanalTPMttBundle:Block');
    }
    
    public function getBlock($dom_id, $timetableId, $stop_point = null)
    {
        if (empty($stop_point)) {
            $block = $this->repo->findByTimetableAndDomId($timetableId, $dom_id);
        } else {
            $block = $this->repo->findByTimetableAndStopPointAndDomId($timetableId, $stop_point, $dom_id);
        }
        return $block;
    }
    
    public function findBlock($blockId)
    {
        return $this->repo->find($blockId);
    }
    
    /**
     * Return Block
     *
     * @param  Object $block
     * @param  Object $destTimetable
     * @param  Object $destStopPoint
     * @return block
     */
    public function copy($block, $destTimetable, $destStopPoint = false)
    {
        $blockCloned = clone $block;
        $blockCloned->setTimetable($destTimetable);
        if ($destStopPoint != false) {
            $blockCloned->setStopPoint($destStopPoint);
        }
        if ($block->isImg()) {
            $this->mediaManager->copy($block, $blockCloned, $destTimetable);
        }

        return $blockCloned;
    }
}