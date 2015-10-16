<?php
/**
 * Description of StopTimetableManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\StopTimetable;

class StopTimetableManager
{
    private $stopTimetable = null;
    private $line = null;
    private $navitia = null;
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om, Navitia $navitia, LineManager $lineManager)
    {
        $this->stopTimetable = null;
        $this->om = $om;
        $this->navitia = $navitia;
        $this->lineManager = $lineManager;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:StopTimetable');
    }

    /*
     * @function find twig path for parent line
     */
    private function initAdditionalData($externalRouteId, $externalCoverageId)
    {
        $data = $this->navitia->getRouteData($externalRouteId, $externalCoverageId);
        $embedded_type = $data->direction->embedded_type;

        $this->stopTimetable->setTitle($data->direction->$embedded_type->name);
        $this->stopTimetable->setDirectionCity(null);
    }

    /*
     * get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        $stopTimetableBlocks = $this->repository->findBlocksByStopTimetableIdOnly($this->stopTimetable->getId());

        if (count($stopTimetableBlocks) > 0) {
            $blocks = array();

            foreach ($stopTimetableBlocks as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            if (count($blocks) > 0) {
                $this->stopTimetable->setBlocks($blocks);
            }
        }
    }

    /**
     * Return stopTimetable Object with navitia data added
     *
     * @param  Integer   $id PK in bdd
     * @return stopTimetable
     */
    public function getStopTimetableById($stopTimetableId, $externalCoverageId)
    {
        $this->stopTimetable = $this->repository->find($stopTimetableId);
        $this->initAdditionalData($this->stopTimetable->getExternalRouteId(), $externalCoverageId);
        $this->initBlocks();

        return $this->stopTimetable;
    }

    /**
     * Return stopTimetable Object with navitia data added
     *
     * @param  Integer   $externalId
     * @return stopTimetable
     */
    public function getStopTimetable($externalRouteId, $externalCoverageId, $lineConfig)
    {
        $this->stopTimetable = $this->repository->getStopTimetableByRouteExternalId($externalRouteId, $lineConfig);
        $this->initAdditionalData($externalRouteId, $externalCoverageId);
        $this->initBlocks();

        return $this->stopTimetable;
    }

    /**
     * Return stopTimetable Object without navitia data added
     *
     * @param  Integer   $externalId
     * @return stopTimetable
     */
    public function findStopTimetableByExternalRouteIdAndLineConfig($externalRouteId, $lineConfig)
    {
        return $this->repository->findOneBy(array(
            'externalRouteId' => $externalRouteId,
            'line_config' => $lineConfig->getId(),
        ));
    }

    /**
     * Return stopTimetable Object without navitia data added
     *
     * @param  Integer   $externalId
     * @return stopTimetable
     */
    public function findStopTimetableByExternalRouteIdAndLineConfigId($externalRouteId, $lineConfigId)
    {
        return $this->repository->findOneBy(array(
            'externalRouteId' => $externalRouteId,
            'line_config' => $lineConfigId
        ));
    }

    /**
     * Return stopTimetable Object
     *
     * @param  Integer   $externalId
     * @return stopTimetable
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Return stopTimetable
     *
     * @param  Object    $stopTimetable
     * @param  Object    $destLineConfig
     * @return stopTimetable
     */
    public function copy($stopTimetable, $destLineConfig)
    {
        $stopTimetableCloned = clone $stopTimetable;
        $stopTimetableCloned->setLineConfig($destLineConfig);

        $this->om->persist($stopTimetableCloned);

        return $stopTimetableCloned;
    }
}
