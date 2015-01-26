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

    /**
     * Get block
     *
     * @param $dom_id
     * @param $objectId
     * @param $objectType
     * @param null $stop_point
     * @return Block
     */
    public function getBlock($dom_id, $objectId, $objectType, $stop_point = null)
    {
        if (empty($stop_point)) {
           /* if ($objectType == \CanalTP\MttBundle\Entity\Timetable::OBJECT_TYPE) {
                $block = $this->repo->findByTimetableIdAndDomId($objectId, $dom_id);
            } else if ($objectType == \CanalTP\MttBundle\Entity\LineTimecard::OBJECT_TYPE) {
                $block = $this->repo->findByLineTimecardIdAndDomId($objectId, $dom_id);
            }*/

             $block = $this->repo->findByObjectIdAndDomId($objectId, $objectType, $dom_id);
        } else {
            $block = $this->repo->findByTimetableAndStopPointAndDomId($objectId, $stop_point, $dom_id);
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
        if ($block->isCalendar() == false) {
            $blockCloned = clone $block;
            $blockCloned->setTimetable($destTimetable);
            if ($destStopPoint != false) {
                $blockCloned->setStopPoint($destStopPoint);
            }
            if ($block->isImg()) {
                $this->mediaManager->copy($block, $blockCloned, $destTimetable);
            }
            $this->om->persist($blockCloned);
        }

        return isset($blockCloned) ? $blockCloned : false;
    }
}
