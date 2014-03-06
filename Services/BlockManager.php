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

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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
}