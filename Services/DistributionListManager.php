<?php

/**
 * Service of distribution list
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class DistributionListManager
{
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:DistributionList');
    }

    public function findByTimetable($timetable)
    {
        return (
            $this->repository->findOneBy(array(
                'network' => $timetable->getLineConfig()->getSeason()->getNetwork(),
                'externalRouteId' => $timetable->getExternalRouteId()
                )
            )
        );
    }
}
