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

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Area');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function getSeasonWithExternalNetworkId($externaNetworkId, $areaId)
    {
        $area = $this->find($areaId);

        if ($area == null) {
            $area = new Area();
            $networkRepository = $this->om->getRepository('CanalTPMttBundle:Network');
            $network = $networkRepository->findOneByExternalId($externaNetworkId);

            $area->setNetwork($network);
        }

        return ($area);
    }

    public function findByExternalNetworkId($externaNetworkId)
    {
        $networkRepository = $this->om->getRepository('CanalTPMttBundle:Network');
        $network = $networkRepository->findOneByExternalId($externaNetworkId);

        return ($network->getAreas());
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
        $networkRepository = $this->om->getRepository('CanalTPMttBundle:Network');
        $network = $networkRepository->findOneByExternalId($externaNetworkId);

        $area->setNetwork($network);
        $this->om->persist($area);
        $this->om->flush();
    }
}
