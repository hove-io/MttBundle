<?php

/**
 * Description of BlockManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\Area;

class AreaManager
{
    private $repository = null;
    private $om = null;
    private $perimeterManager = null;

    public function __construct(ObjectManager $om, $perimeterManager)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Area');
        $this->perimeterManager = $perimeterManager;
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function getAreaWithExternalNetworkId($externaNetworkId, $areaId)
    {
        $area = $this->find($areaId);

        if ($area == null) {
            $area = new Area();
            $network = $this->perimeterManager->findOneByExternalNetworkId($externaNetworkId);

            $area->setPerimeter($network);
        }

        return ($area);
    }

    public function findByExternalNetworkId($externaNetworkId)
    {
        $network = $this->perimeterManager->findOneByExternalNetworkId($externaNetworkId);

        return $this->repository->findByPerimeter($network);
    }

    public function find($areaId)
    {
        return empty($areaId) ? null : $this->repository->find($areaId);
    }

    public function remove($areaId)
    {
        $area = $this->repository->find($areaId);

        $this->om->remove($area);
        $this->om->flush();
    }

    public function save($area, $externaNetworkId)
    {
        $network = $this->perimeterManager->findOneByExternalNetworkId($externaNetworkId);

        $area->setPerimeter($network);
        $this->om->persist($area);
        $this->om->flush();
    }
}
