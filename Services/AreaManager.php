<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\Area;

class AreaManager
{
    private $om = null;
    private $repository = null;
    private $taskManager = null;
    private $perimeterManager = null;

    public function __construct(ObjectManager $om, $perimeterManager, AreaPdfManager $areaPdfManager)
    {
        $this->om = $om;
        $this->areaPdfManager = $areaPdfManager;
        $this->perimeterManager = $perimeterManager;
        $this->repository = $om->getRepository('CanalTPMttBundle:Area');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function getAreaWithPerimeter($perimeter, $areaId)
    {
        $area = $this->find($areaId);

        if ($area == null) {
            $area = new Area();

            $area->setPerimeter($perimeter);
        }

        return ($area);
    }

    public function findByPerimeter($perimeter)
    {
        return $this->repository->findByPerimeter($perimeter);
    }

    public function find($areaId)
    {
        return empty($areaId) ? null : $this->repository->find($areaId);
    }

    public function remove($areaId)
    {
        $area = $this->repository->find($areaId);

        $this->areaPdfManager->removeAreaPdfByArea($area);
        $this->om->remove($area);
        $this->om->flush();
    }

    public function save($area, $user, $externaNetworkId)
    {
        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $user->getCustomer(),
            $externaNetworkId
        );

        $area->setPerimeter($perimeter);
        $this->om->persist($area);
        $this->om->flush();
    }
}
