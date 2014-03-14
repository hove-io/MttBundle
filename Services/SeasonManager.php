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

    public function getSeasonWithNetworkIdAndSeasonId($networkId, $seasonId)
    {
        return ($this->repository->getSeasonByNetworkIdAndSeasonId($networkId, $seasonId));
    }

    public function save($season)
    {
        $this->om->persist($season);
        $this->om->flush();
    }

    public function findAllByNetworkId($networkId)
    {
        $networkRepository = $this->om->getRepository('CanalTPMttBundle:Network');

        return ($networkRepository->findOneByExternalId($networkId)->getSeasons());
    }
    
    public function getSelected($seasonId, $seasons)
    {
        if ($seasonId == null && count($seasons) > 0) {
            return $seasons[0];
        } else {
            foreach($seasons as $season) {
                if ($seasonId == $season->getId()) {
                    return $season;
                }
            }
        }
    }
}