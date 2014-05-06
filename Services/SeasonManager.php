<?php

/**
 * Description of SeasonManager
 *
 * @author rabikhalil
 */

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class SeasonManager
{
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Season');
    }

    public function getSeasonWithNetworkIdAndSeasonId($externalNetworkId, $seasonId)
    {
        return ($this->repository->getSeasonByNetworkIdAndSeasonId($externalNetworkId, $seasonId));
    }

    public function save($season)
    {
        $this->om->persist($season);
        $this->om->flush();
    }

    public function publish($seasonId)
    {
        $season = $this->find($seasonId);
        $season->setPublished(true);
        $this->save($season);
    }

    public function unpublish($seasonId)
    {
        $season = $this->find($seasonId);
        $season->setPublished(false);
        $this->save($season);
    }

    public function find($seasonId)
    {
        return $this->repository->find($seasonId);
    }

    public function remove($season)
    {
        $this->om->remove($season);
        $this->om->flush();
    }

    public function findSeasonForDateTime(\DateTime $dateTime)
    {
        return $this->repository->findSeasonForDateTime($dateTime);
    }

    public function findAllByNetworkId($externalNetworkId)
    {
        $networkRepository = $this->om->getRepository('CanalTPMttBundle:Network');

        return ($networkRepository->findOneByExternalId($externalNetworkId)->getSeasons());
    }

    public function getSelected($seasonId, $seasons)
    {
        if ($seasonId == null && count($seasons) > 0) {
            return $seasons[0];
        } else {
            foreach ($seasons as $season) {
                if ($seasonId == $season->getId()) {
                    return $season;
                }
            }
        }
    }
}
