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

        $this->om->remove($area);
        $this->om->flush();
    }

    public function save($area, $user, $externaNetworkId)
    {
        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $user,
            $externaNetworkId
        );

        $area->setPerimeter($perimeter);
        $this->om->persist($area);
        $this->om->flush();
    }
}
