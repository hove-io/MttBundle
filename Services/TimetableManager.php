<?php

/**
 * Description of RouteManager
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Services\Navitia;
use CanalTP\MethBundle\Entity\Timetable;

class TimetableManager
{
    private $timetable = null;
    private $line = null;
    private $navitia = null;
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om, Navitia $navitia, LineManager $lineManager)
    {
        $this->timetable = null;
        $this->om = $om;
        $this->navitia = $navitia;
        $this->lineManager = $lineManager;
        $this->repository = $this->om->getRepository('CanalTPMethBundle:Timetable');
    }
    
    /*
     * @function find twig path for parent line
     */
    private function initAdditionalData($externalRouteId, $externalCoverageId)
    {
        $data = $this->navitia->getRouteData($externalRouteId, $externalCoverageId);
        $line = $this->lineManager->getLineByExternalId($data->line->id);
        $this->timetable->setLine($line);
        $this->timetable->setTitle($data->name);
    }
    
    /*
     * @function if timetable has an id, get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        if ($this->timetable->getId() && count($this->timetable->getBlocks()) > 0)
        {
            $blocks = array();

            foreach ($this->timetable->getBlocks() as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            if (count($blocks) > 0)
                $this->timetable->setBlocks($blocks);
        }
    }
    
    /**
     * Return timetable Object with navitia data added
     *
     * @param  Integer $externalId
     * @return line
     */
    public function getTimetable($externalRouteId, $externalCoverageId)
    {
        $this->timetable = $this->repository->getTimetableByRouteExternalId($externalRouteId);
        $this->initAdditionalData($externalRouteId, $externalCoverageId);
        $this->initBlocks();

       return $this->timetable;
    }
}